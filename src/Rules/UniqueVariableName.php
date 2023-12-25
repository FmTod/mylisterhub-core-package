<?php

namespace MyListerHub\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UniqueVariableName implements Rule
{
    /**
     * The table to run the query against.
     */
    protected string $table;

    /**
     * The column to check on.
     */
    protected ?string $column;

    /**
     * The ID that should be ignored.
     */
    protected mixed $ignore;

    /**
     * The name of the ID column.
     */
    protected string $idColumn = 'id';

    /**
     * Create a new rule instance.
     */
    public function __construct(string $table, mixed $ignore = null, ?string $column = null, ?string $idColumn = null)
    {
        $this->column = $column;

        $this->table = $this->resolveTableName($table);

        if (! is_null($ignore)) {
            $this->ignore($ignore, $idColumn);
        }
    }

    /**
     * Resolves the name of the table from the given string.
     */
    public function resolveTableName(string $table): string
    {
        if (! class_exists($table) || ! Str::contains($table, '\\')) {
            return $table;
        }

        if (! is_subclass_of($table, Model::class)) {
            return $table;
        }
        $model = new $table;

        if (Str::contains($model->getTable(), '.')) {
            return $table;
        }

        return implode('.', array_map(
            callback: static fn (string $part) => trim($part, '.'),
            array: array_filter([$model->getConnectionName(), $model->getTable()])
        ));
    }

    /**
     * Ignore the given ID during the unique check.
     *
     * @return $this
     */
    public function ignore(mixed $id, ?string $idColumn = null): static
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id, $idColumn);
        }

        $this->ignore = $id;
        $this->idColumn = $idColumn ?? 'id';

        return $this;
    }

    /**
     * Ignore the given model during the unique check.
     *
     * @return $this
     */
    public function ignoreModel(Model $model, ?string $idColumn = null): static
    {
        $this->idColumn = $idColumn ?? $model->getKeyName();
        $this->ignore = $model->{$this->idColumn};

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $column = $this->column ?? $attribute;

        return DB::table($this->table)
            ->whereRaw("REGEXP_REPLACE(REGEXP_REPLACE(REGEXP_REPLACE(LOWER(`$column`), '[^\\\\w_]', '_'), '__+', '_'), '_$', '') = ?", Str::variableName($value))
            ->when(isset($this->ignore), fn (Builder $query) => $query->where($this->idColumn, '!=', $this->ignore))
            ->doesntExist();
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The variable representation of :attribute must be unique.';
    }
}
