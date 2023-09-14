<?php

namespace MyListerHub\Core\TypeScript;

use Based\TypeScript\Definitions\TypeScriptProperty;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class ModelGenerator extends AbstractGenerator
{
    protected Model $model;

    /** @var Collection<Column> */
    protected Collection $columns;

    /**
     * @throws \ReflectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function getDefinition(): ?string
    {
        $this->model = $this->reflection->newInstance();

        $this->columns = collect(
            $this->model->getConnection()
                ->getDoctrineSchemaManager()
                ->listTableColumns($this->model->getConnection()->getTablePrefix().$this->model->getTable())
        );

        return collect($this->getProperties())
            ->merge($this->getAccessors())
            ->merge($this->getAttributes())
            ->merge($this->getRelations())
            ->merge($this->getRelationsCount())
            ->map(fn (TypeScriptProperty $property) => (string) $property)
            ->ray()
            ->filter(fn (string $part) => ! empty($part))
            ->join(PHP_EOL.$this->getIndentation(1));
    }

    protected function getMethods(): Collection
    {
        return collect($this->reflection->getMethods())
            ->reject(fn (ReflectionMethod $method) => $method->isStatic())
            ->reject(fn (ReflectionMethod $method) => $method->getNumberOfParameters());
    }

    protected function getProperties(): Collection
    {
        return $this->columns
            ->mapWithKeys(fn (Column $column) => [$column->getName() => $column])
            ->map(fn (Column $column) => new TypeScriptProperty(
                name: $column->getName(),
                types: $this->getPropertyType($column->getType()->getName()),
                nullable: ! $column->getNotnull()
            ));
    }

    protected function getAccessors(): Collection
    {
        return $this->getMethods()
            ->filter(
                fn (ReflectionMethod $method) => Str::startsWith($method->getName(), 'get')
                    && Str::endsWith($method->getName(), 'Attribute')
            )
            ->mapWithKeys(function (ReflectionMethod $method) {
                $property = (string) Str::of($method->getName())
                    ->between('get', 'Attribute')
                    ->snake();

                return [$property => $method];
            })
            ->map(fn (ReflectionMethod $method, string $property) => new TypeScriptProperty(
                name: $property,
                types: TypeScriptType::fromMethod($method),
                optional: $this->columns->doesntContain(fn (Column $column) => $column->getName() === $property),
                readonly: $this->isPropertyReadOnly($property)
            ));
    }

    protected function getAttributes(): Collection
    {
        return $this->getMethods()
            ->filter(fn (ReflectionMethod $method) => $this->model->hasAttributeGetMutator($method->getName()))
            ->mapWithKeys(fn (ReflectionMethod $method) => [Str::snake($method->getName()) => $method->invoke($this->model)])
            ->map(fn (Attribute $attribute, string $property) => new TypeScriptProperty(
                name: $property,
                types: TypeScriptType::fromAttribute($attribute),
                optional: $this->columns->doesntContain(fn (Column $column) => $column->getName() === $property),
                readonly: $this->isPropertyReadOnly($property),
            ));
    }

    protected function getRelations(): Collection
    {
        return $this->getMethods()
            ->filter($this->isRelationMethod(...))
            ->mapWithKeys(fn (ReflectionMethod $method) => [Str::snake($method->getName()) => $method])
            ->map(fn (ReflectionMethod $method) => new TypeScriptProperty(
                name: Str::snake($method->getName()),
                types: $this->getRelationType($method),
                optional: true,
                nullable: true
            ));
    }

    protected function getRelationsCount(): Collection
    {
        return $this->getMethods()
            ->filter($this->isRelationMethod(...))
            ->filter($this->isManyRelation(...))
            ->mapWithKeys(fn (ReflectionMethod $method) => [Str::snake($method->getName()).'_count' => $method])
            ->map(fn (ReflectionMethod $method) => new TypeScriptProperty(
                name: Str::snake($method->getName()).'_count',
                types: TypeScriptType::NUMBER,
                optional: true,
                nullable: true
            ));
    }

    protected function getPropertyType(string $type): string|array
    {
        return match ($type) {
            Types::ASCII_STRING,
            Types::GUID,
            Types::DATETIMETZ_IMMUTABLE,
            Types::DATETIMETZ_MUTABLE,
            Types::DATETIME_IMMUTABLE,
            Types::DATETIME_MUTABLE,
            Types::DATEINTERVAL,
            Types::DATE_IMMUTABLE,
            Types::DATE_MUTABLE,
            Types::BLOB,
            Types::BINARY,
            Types::STRING,
            Types::TEXT => TypeScriptType::STRING,

            Types::BIGINT,
            Types::INTEGER,
            Types::FLOAT,
            Types::DECIMAL,
            Types::SMALLINT,
            Types::TIME_MUTABLE,
            Types::TIME_IMMUTABLE => TypeScriptType::NUMBER,

            Types::JSON,
            Types::SIMPLE_ARRAY => [TypeScriptType::array(), TypeScriptType::ANY],

            Types::BOOLEAN => TypeScriptType::BOOLEAN,

            default => TypeScriptType::ANY,
        };
    }

    protected function isPropertyReadOnly(string $property): bool
    {
        return ! $this->columns->some(fn (Column $column) => $column->getName() === $property)
            && ! $this->model->hasSetMutator($property)
            && ! $this->model->hasAttributeSetMutator($property);
    }

    protected function isRelationMethod(ReflectionMethod $method): bool
    {
        if (! config('typescript.include_traits', true)
            || collect($this->reflection->getTraits())
                ->some(fn (ReflectionClass $trait) => $trait->hasMethod($method->name))) {
            return false;
        }

        try {
            return $method->invoke($this->model) instanceof Relation;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @throws \ReflectionException
     */
    protected function getRelationType(ReflectionMethod $method): string
    {
        $relationReturn = $method->invoke($this->model);
        $related = config('typescript.namespace', true)
            ? str_replace('\\', '.', get_class($relationReturn->getRelated()))
            : class_basename($relationReturn->getRelated());

        if (! Str::startsWith(get_class($relationReturn->getRelated()), 'App\\')) {
            return TypeScriptType::ANY;
        }

        if ($this->isManyRelation($method)) {
            return TypeScriptType::array($related);
        }

        if ($this->isOneRelation($method)) {
            return $related;
        }

        return TypeScriptType::ANY;
    }

    /**
     * @throws \ReflectionException
     */
    protected function isOneRelation(ReflectionMethod $method): bool
    {
        $relationType = $method->getReturnType()?->getName() ?? get_class($method->invoke($this->model));

        return in_array($relationType, [
            HasOne::class,
            BelongsTo::class,
            MorphOne::class,
            HasOneThrough::class,
        ]);
    }

    /**
     * @throws \ReflectionException
     */
    protected function isManyRelation(ReflectionMethod $method): bool
    {
        $relationType = $method->getReturnType()?->getName() ?? get_class($method->invoke($this->model));

        return in_array($relationType, [
            HasMany::class,
            BelongsToMany::class,
            HasManyThrough::class,
            MorphMany::class,
            MorphToMany::class,
        ]);
    }
}
