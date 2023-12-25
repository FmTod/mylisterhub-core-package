<?php

namespace MyListerHub\Core\Providers;

use BenSampo\Enum\Enum;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use FmTod\LaravelTabulator\Factories\ColumnFactory;
use FmTod\LaravelTabulator\Helpers\Action;
use FmTod\LaravelTabulator\Helpers\Column;
use FmTod\Money\Money;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Builder as Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use JsonSerializable;
use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use MyListerHub\Core\Currency\Exchangers\SmartRatesExchanger;
use MyListerHub\Core\Eloquent\SerializableBuilder;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Traversable;
use UnitEnum;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     *
     * @throws \ReflectionException
     */
    public function boot(): void
    {
        $this->registerQueryMacros();
        $this->registerEloquentMacros();
        $this->registerCollectionMacros();
        $this->registerRequestMacros();
        $this->registerResponseMacros();
        $this->registerEnumMacros();
        $this->registerStringMacros();
        $this->registerArrayMacros();
        $this->registerCarbonMacros();
        $this->registerTabulatorMacros();
        $this->registerMoneyMacros();
    }

    /**
     * Register macros related to query/builder object.
     *
     * @noinspection PhpParamsInspection
     */
    private function registerQueryMacros(): void
    {
        Builder::macro(
            'whereNullOrValue',
            /**
             * Add a where or whereNull clause to the query.
             *
             * @return \MyListerHub\Core\Providers\MacroServiceProvider|\Illuminate\Database\Query\Builder|\Illuminate\Support\HigherOrderTapProxy|mixed
             */
            function (array|string $column, ?string $value = null) {
                /* @var Builder $this */

                if (! is_array($column)) {
                    return $this->where(fn ($query) => $query->whereNull($column)->orWhere($column, $value));
                }

                return tap($this, static function ($query) use ($column) {
                    foreach ($column as $columnName => $columnValue) {
                        $query->whereNullOrValue($columnName, $columnValue);
                    }
                });
            }
        );

        Builder::macro(
            'whereDateBetween',
            /**
             * Scope a query to only include the last n days records.
             */
            function (string $column, DateTimeInterface|string $from, DateTimeInterface|string $to): Builder {
                /** @var Builder $this */
                return $this->whereDate($column, '>=', $from)->whereDate($column, '<=', $to);
            }
        );

        Builder::macro(
            'orWhereDateBetween',
            /**
             * Scope a query to only include the last n days records.
             */
            function (string $column, DateTimeInterface|string $from, DateTimeInterface|string $to): Builder {
                /** @var Builder $this */
                return $this->orWhere(
                    fn (Builder $query) => $query
                        ->whereDate($column, '>=', $from)
                        ->whereDate($column, '<=', $to)
                );
            }
        );

        Builder::macro(
            'resetOffset',
            /**
             * Reset offset component in the builder instance.
             *
             * @return \Illuminate\Database\Query\Builder
             */
            function () {
                /** @var Builder $this */
                $this->offset = null;

                return $this;
            }
        );

        Builder::macro(
            'resetLimit',
            /**
             * Reset limit & offset component in the builder instance.
             *
             * @return \Illuminate\Database\Query\Builder
             */
            function () {
                /** @var Builder $this */
                return $this->limit(null)->resetOffset();
            }
        );

        Builder::macro(
            'resetJoins',
            /**
             * Reset joins component in the builder instance.
             *
             * @return \Illuminate\Database\Query\Builder
             */
            function () {
                /** @var Builder $this */
                $this->joins = [];
                $this->setBindings([], 'join');

                return $this;
            }
        );

        Builder::macro(
            'fromCentralConnection',
            /**
             * Reset joins component in the builder instance.
             *
             * @return \Illuminate\Database\Query\Builder
             */
            function (?string $table = null, ?string $as = null) {
                if (is_null($table)) {
                    $table = $this->from;
                }

                $connection = config('tenancy.database.central_connection', 'central');
                $database = DB::connection($connection)->getDatabaseName();

                $this->from("$database.$table", $as);

                return $this;
            }
        );
    }

    /**
     * Register macros related to eloquent/model object.
     */
    public function registerEloquentMacros(): void
    {
        Eloquent::macro(
            'getAppendNames',
            /**
             * Get available attributes to append to the model.
             *
             * @throws \Psr\SimpleCache\InvalidArgumentException
             */
            function (): array {
                /** @var Eloquent $this */
                $model = $this->newModelInstance();

                if (Cache::has($cacheKey = $model->getMorphClass().'-appends')
                    && is_array($appends = Cache::get($cacheKey))) {
                    return $appends;
                }

                /** @var Model $class */
                $class = get_class($model);

                $appends = collect([
                    ...$class::getAttributeMarkedMutatorMethods($model),
                    ...$class::getMutatorMethods($class),
                ])->map(fn ($match) => lcfirst($class::$snakeAttributes ? Str::snake($match) : $match))->all();

                Cache::set($cacheKey, $appends, now()->addDay());

                return $appends;
            }
        );

        Eloquent::macro(
            'getRelationNames',
            /**
             * Get available relationships to include in the model.
             *
             * @param  class-string|array<class-string>  $relationType
             * @return array<string>
             *
             * @throws \Psr\SimpleCache\InvalidArgumentException
             * @throws \ReflectionException
             */
            function (string|array $relationType = Relation::class): array {
                /** @var Eloquent $this */
                $model = $this->newModelInstance();

                if (Cache::has($cacheKey = $model->getMorphClass().'-relations')
                    && is_array($relations = Cache::get($cacheKey))) {
                    return $relations;
                }

                $relations = collect((new ReflectionClass($model))->getMethods())
                    ->filter(function (ReflectionMethod $method) use ($relationType) {
                        $returnType = $method->getReturnType();

                        return $returnType instanceof ReflectionNamedType && collect($relationType)->contains(
                            static fn (string $type) => $returnType->getName() === $type
                                || is_subclass_of($returnType->getName(), $type)
                        );
                    })
                    ->map(fn (ReflectionMethod $method) => $method->name)
                    ->values()
                    ->all();

                Cache::set($cacheKey, $relations, now()->addDay());

                return $relations;
            }
        );

        Eloquent::macro(
            'getAttributeNames',
            /**
             * Get the model's attribute names.
             */
            function (bool $includeAppends = false, bool $includeRelationships = false): array {
                /** @var Eloquent $this */
                $model = $this->newModelInstance();

                /** @var Model $class */
                $class = get_class($model);

                if (Cache::has($cacheKey = $model->getMorphClass().'-attributes')) {
                    $attributes = Cache::get($cacheKey);
                } else {
                    $attributes = Schema::getColumnListing($model->getTable());

                    Cache::put($cacheKey, $attributes, now()->addDay());
                }

                if ($includeAppends) {
                    $attributes = [...$attributes, ...$class::getAppendNames()];
                }

                if ($includeRelationships) {
                    $attributes = [...$attributes, ...$class::getRelationNames()];
                }

                return $attributes;
            }
        );

        Eloquent::macro(
            'toSqlWithBindings',
            /**
             * Get SQL query with bindings.
             */
            function (): string {
                /** @var Builder $this */
                $query = str_replace('?', '%s', $this->toSql());

                $bindings = collect($this->getBindings())
                    ->map(fn ($binding) => is_numeric($binding) ? $binding : "'$binding'")
                    ->toArray();

                return vsprintf($query, $bindings);
            }
        );

        Eloquent::macro(
            'toSerializable',
            /**
             * Get Serializable instance of the eloquent query builder.
             */
            function (): SerializableBuilder {
                /** @var Eloquent $this */
                return SerializableBuilder::of($this);
            }
        );

        Eloquent::macro(
            'resetOffset',
            /**
             * Reset offset component in the builder instance.
             */
            function (): Eloquent {
                /** @var Eloquent $this */
                $this->getQuery()->resetOffset();

                return $this;
            }
        );

        Eloquent::macro(
            'resetLimit',
            /**
             * Reset limit and offset components in the builder instance.
             */
            function (): Eloquent {
                /** @var Eloquent $this */
                $this->getQuery()->resetLimit();

                return $this;
            }
        );

        Eloquent::macro(
            'resetJoins',
            /**
             * Reset limit and offset components in the builder instance.
             */
            function (): Eloquent {
                /** @var Eloquent $this */
                $this->getQuery()->resetJoins();

                return $this;
            }
        );

        Eloquent::macro(
            'resetEagerLoads',
            /**
             * Reset eager loads in the builder instance.
             *
             * @return \Illuminate\Database\Eloquent\Builder
             */
            fn (): Eloquent => $this->setEagerLoads([])
        );

        Eloquent::macro(
            'tableExists',
            /**
             * Return whether the table for the eloquent instance exists.
             */
            function (): bool {
                /** @var Eloquent $this */
                $connection = $this->getModel()->getConnectionName();
                $table = $this->getModel()->getTable();

                return DB::connection($connection)->getSchemaBuilder()->hasTable($table);
            }
        );

        Eloquent::macro(
            'treePaginate',
            /**
             * Paginate the given query.
             *
             * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
             *
             * @throws \InvalidArgumentException
             */
            function (string $childRelation = 'children', ?int $perPage = null, array $columns = ['*'], string $pageName = 'page', ?int $page = null) {
                $page = $page ?: Paginator::resolveCurrentPage($pageName);

                $perPage = $perPage ?: $this->model->getPerPage();

                $results = ($total = $this->toBase()->getCountForPagination())
                    ? $this->forPage($page, $perPage)->get($columns)->map(function (Model $model) use ($childRelation) {
                        if (empty($model->{$childRelation})) {
                            $model->unsetRelation($childRelation);
                        }

                        return $model;
                    })
                    : $this->model->newCollection();

                return $this->paginator($results, $total, $perPage, $page, [
                    'path' => Paginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]);
            }
        );
    }

    /**
     * Register macros related to collection object.
     *
     * @noinspection PhpParamsInspection
     */
    private function registerCollectionMacros(): void
    {
        Collection::macro(
            'toAssoc',
            /**
             * Convert the collection to an associative array.
             *
             * @return \Illuminate\Support\Collection
             *
             * @deprecated Replaced by the newer `mapWithKey` method.
             */
            function () {
                /* @var Collection $this */
                return $this->reduce(function ($assoc, $keyValuePair) {
                    [$key, $value] = $keyValuePair;
                    $assoc[$key] = $value;

                    return $assoc;
                }, new static);
            }
        );

        Collection::macro(
            'mapToAssoc',
            /**
             * Convert the collection to an associative array.
             *
             * @return \Illuminate\Support\Collection
             *
             * @deprecated Replaced by the newer `mapWithKey` method.
             */
            function ($callback) {
                /* @var Collection $this */
                return $this->map($callback)->toAssoc();
            }
        );

        Collection::macro(
            'containsAny',
            /**
             * Determine if any of the values exist in the collection.
             *
             * @return bool
             */
            function ($values) {
                if ($this->isEmpty()) {
                    return false;
                }

                $values = is_array($values) ? $values : func_get_args();

                foreach ($values as $value) {
                    if ($this->contains($value)) {
                        return true;
                    }
                }

                return false;
            }
        );
    }

    /**
     * Register macros related to response factory.
     */
    private function registerRequestMacros(): void
    {
        Request::macro('truthy', function ($key) {
            return $this->has($key)
                && $this->get($key)
                && strtolower($this->get($key)) !== 'false';
        });

        Request::macro('remove', function (string $key) {
            $this->getInputSource()?->remove($key);

            return $this;
        });

        Request::macro('clear', function () {
            foreach ($this->all() as $key => $value) {
                $this->remove($key);
            }

            return $this;
        });

        Request::macro('wrap', function (string $key) {
            $data = $this->all();

            $this->clear();
            $this->merge([$key => $data]);

            return $this;
        });

        Request::macro('unwrap', function (string $key, bool $clear = true) {
            $data = $this->input($key, []);

            $clear ? $this->clear() : $this->remove($key);

            $this->merge($data);

            return $this;
        });
    }

    /**
     * Register macros related to response factory.
     */
    private function registerResponseMacros(): void
    {
        ResponseFactory::macro(
            'queryBuilder',
            /**
             * Response factory for QueryBuilder compatible resources.
             *
             * @return \Illuminate\Http\JsonResponse
             */
            function (mixed $value, bool $paginate = false) {
                /** @var Response $this */
                $limit = request()?->input('limit');

                if ($paginate || request()?->has('page')) {
                    $response = $value->paginate();

                    return $this->json($response);
                }

                $response = when($limit, $value, static fn ($q) => $q->limit($limit))->get();

                return $this->data($response);
            }
        );

        ResponseFactory::macro(
            'data',
            /**
             * Response factory for data wrapped responses.
             *
             * @return \Illuminate\Http\JsonResponse
             */
            function (mixed $data, ?string $message = null, string $status = 'success', int $code = 200) {
                return response()->json(array_filter([
                    'status' => $status,
                    'message' => $message,
                    'data' => $data,
                ]), $code);
            }
        );

        ResponseFactory::macro(
            'message',
            /**
             * Response factory for just returning message.
             *
             * @return \Illuminate\Http\JsonResponse
             */
            function (string $message, string $status = 'success', int $code = 200) {
                return response()->json(array_filter([
                    'status' => $status,
                    'message' => $message,
                ]), $code);
            }
        );

        ResponseFactory::macro(
            'failure',
            /**
             * Response factory for returning a failure.
             *
             * @return \Illuminate\Http\JsonResponse
             */
            function (string $message, int $code = 422) {
                return response()->message($message, 'failure', $code);
            }
        );
    }

    /**
     * Register macros related to enums.
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    private function registerEnumMacros(): void
    {
        Enum::macro(
            'asInstanceArray',
            /**
             * Get array of enums with all their properties.
             *
             * @return array
             */
            function () {
                return array_values(array_map(static fn (Enum $enum) => [
                    'key' => $enum->key,
                    'value' => $enum->value,
                    'description' => $enum->description,
                ], self::getInstances()));
            }
        );

        Enum::macro(
            'asCollection',
            /**
             * Get collection of an enum instances.
             *
             * @return Collection
             */
            fn (): Collection => collect(self::getInstances())
        );

        Enum::macro(
            'map',
            /**
             * Get map enum instances to array.
             *
             * @param  callable  $callback
             * @return array
             */
            fn (callable $callback): array => self::asCollection()->map($callback)->values()->toArray()
        );
    }

    /**
     * Register macros related to the Str class.
     *
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpParamsInspection
     */
    private function registerStringMacros(): void
    {
        Str::macro(
            'camelToTitle',
            /**
             * Convert camel cased string to title case.
             *
             * @param  string  $value
             * @return string
             */
            fn ($value) => Str::title(implode(' ', preg_split('/(?<!^)(?=[A-Z])/', $value)))
        );

        Str::macro(
            'snakeToTitle',
            /**
             * Convert snake cased string to title case.
             *
             * @param  string  $value
             * @return string
             */
            fn ($value) => Str::title(str_replace('_', ' ', $value))
        );

        Str::macro(
            'list',
            /**
             * Returns English string representation of a list of words.
             *
             * @return string
             */
            function (mixed $words, bool $lowerCase = false, string $separator = ', ', string $concatCharacter = ' and ') {
                if ($words instanceof Arrayable) {
                    $words = $words->toArray();
                }

                if (! is_array($words)) {
                    $words = (array) $words;
                }

                if (count($words) <= 2) {
                    $concatenated = implode($concatCharacter, $words);

                    return $lowerCase
                        ? self::lower($concatenated)
                        : $concatenated;
                }

                $lastWord = array_pop($words);
                $lastWordSeparator = str_replace('  ', ' ', $separator.$concatCharacter);
                $concatenated = implode($separator, $words).$lastWordSeparator.$lastWord;

                return $lowerCase
                    ? self::lower($concatenated)
                    : $concatenated;
            }
        );

        Str::macro(
            'default',
            /**
             * Provide default value if the provided one is blank.
             *
             * @param  string  $value
             * @param  string|null  $default
             * @return string
             */
            fn ($value, $default = null) => filled($value) ? $value : $default
        );

        Str::macro(
            'variableName',
            /**
             * Convert given name to snake cased and formatted variable.
             *
             * @return string
             */
            fn ($name) => (string) Str::of($name)
                ->lower()
                ->replaceMatches('/[^\w_]/', '_')
                ->replaceMatches('/__+/', '_')
                ->replaceMatches('/_$/', '')
        );

        Stringable::macro(
            'variableName',
            /**
             * Convert stringable instance to snake cased and formatted variable.
             *
             * @return \Illuminate\Support\Stringable
             */
            fn () => new static(Str::variableName($this->value))
        );

        Stringable::macro(
            'toString',
            /**
             * Convert stringable instance to string.
             *
             * @return string
             */
            fn () => (string) $this
        );
    }

    /**
     * Register macros related to the Str class.
     *
     *
     * @noinspection PhpIncompatibleReturnTypeInspection
     * @noinspection PhpParamsInspection
     * @noinspection PhpUndefinedMethodInspection
     */
    private function registerArrayMacros(): void
    {
        Arr::macro(
            'in',
            /**
             * Check if an array contains all array values from another array.
             *
             * @return bool
             */
            function ($actual, $expected) {
                if (is_array($expected) && is_array($actual)) {
                    foreach ($expected as $key => $value) {
                        if (! isset($actual[$key]) || ! static::in($value, $actual[$key])) {
                            return false;
                        }
                    }

                    return true;
                }

                if (is_array($expected) || is_array($actual)) {
                    return false;
                }

                return (string) $expected == (string) $actual;
            }
        );

        Arr::macro(
            'fromArrayable',
            /**
             * Results array of items from Collection or Arrayable.
             *
             * @return array
             */
            function (mixed $items) {
                if (is_array($items)) {
                    return $items;
                }

                if ($items instanceof Enumerable) {
                    return $items->all();
                }

                if ($items instanceof Arrayable) {
                    return $items->toArray();
                }

                if ($items instanceof Jsonable) {
                    return json_decode($items->toJson(), true);
                }

                if ($items instanceof JsonSerializable) {
                    return (array) $items->jsonSerialize();
                }

                if ($items instanceof Traversable) {
                    return iterator_to_array($items);
                }

                if ($items instanceof UnitEnum) {
                    return [$items];
                }

                return (array) $items;
            }
        );

        Arr::macro(
            'isAssociative',
            /**
             * Check if array is associative.
             *
             * @param  bool  $strict
             * <p>If <i>false</i> then this function will match any array that doesn't contain integer keys.</p>
             * <p>If <i>true</i> then this function match only arrays with sequence of integers starting from zero (range from 0 to elements_number - 1) as keys.</p>
             */
            function (array $array, bool $strict = false): bool {
                if (empty($array)) {
                    return false;
                }

                if ($strict) {
                    return ! array_is_list($array);
                }

                foreach (array_keys($array) as $key) {
                    if (! is_int($key)) {
                        return true;
                    }
                }

                return false;
            }
        );
    }

    /**
     * Register macros related to the Carbon\Carbon class.
     *
     *
     * @throws \ReflectionException
     */
    private function registerCarbonMacros(): void
    {
        Carbon::macro(
            name: 'isBetweenTime',
            /**
             * Determines if the instance is between two others without taking the date into account.
             *
             * @param  \Carbon\Carbon|\DateTimeInterface|mixed  $time1
             * @param  mixed  $time2
             * @param  bool  $equal
             * @return bool
             *
             * @example
             * ```
             * Carbon::parse('8:50')->isBetween('8:00', '9:00'); // true
             * Carbon::parse('10:00')->isBetween('7:00', '9:00'); // false
             * Carbon::parse('9:00')->isBetween('9:00', '10:00'); // true
             * Carbon::parse('9:00')->isBetween('9:00', '10:00', false); // false
             * ```
             */
            macro: function (mixed $time1, mixed $time2, bool $equal = true) {
                /** @var Carbon $this */
                $time1 = $this->resolveCarbon($time1);
                $time2 = $this->resolveCarbon($time2);

                if ($time1->greaterThan($time2)) {
                    [$time1, $time2] = [$time2, $time1];
                }

                if ($equal) {
                    return $this->format('Gis.u') >= $time1->format('Gis.u')
                        && $this->format('Gis.u') <= $time2->format('Gis.u');
                }

                return $this->format('Gis.u') > $time1->format('Gis.u')
                    && $this->format('Gis.u') < $time2->format('Gis.u');
            }
        );

        CarbonPeriod::mixin(new class
        {
            public function nth()
            {
                return function (int $key) {
                    /** @var CarbonPeriod $this */
                    $copy = $this->copy();

                    $copy->skip($key);

                    return $copy->current();
                };
            }
        });
    }

    /**
     * Register macros related to the Tabulator class.
     */
    private function registerTabulatorMacros(): void
    {
        ColumnFactory::macro(
            name: 'dateTime',
            macro: fn (): ColumnFactory => (new ColumnFactory)
                ->formatter('datetime')
                ->formatterParams([
                    'inputFormat' => 'iso',
                    'outputFormat' => 'yyyy-MM-dd h:mm a',
                    'invalidPlaceholder' => true,
                    'timezone' => 'system',
                ]),
        );

        ColumnFactory::macro(
            name: 'date',
            macro: fn (): ColumnFactory => (new ColumnFactory)
                ->formatter('datetime')
                ->formatterParams([
                    'inputFormat' => 'iso',
                    'outputFormat' => 'yyyy-MM-dd',
                    'invalidPlaceholder' => true,
                    'timezone' => 'system',
                ]),
        );

        ColumnFactory::macro(
            name: 'rowSelection',
            macro: fn (): ColumnFactory => (new ColumnFactory)
                ->titleFormatter('rowSelection')
                ->formatter('rowSelection')
                ->hozAlign('center')
                ->headerSort(false)
                ->cssClass('row-selection'),
        );

        ColumnFactory::macro(
            name: 'actions',
            macro: fn (): ColumnFactory => (new ColumnFactory(config('tabulator.action'))),
        );

        Column::macro(
            name: 'dateTime',
            macro: fn ($field): Column => (new ColumnFactory)->dateTime()->make($field),
        );

        Column::macro(
            name: 'date',
            macro: fn ($field): Column => (new ColumnFactory)->date()->make($field),
        );

        Column::macro(
            name: 'rowSelection',
            macro: fn (): Column => (new ColumnFactory)->rowSelection()->make([]),
        );

        Column::macro(
            name: 'actions',
            macro: fn (array $actions): Column => (new ColumnFactory)->actions()
                ->make(config('tabulator.action.field', 'actions'))
                ->formatterParams(['actions' => $actions]),
        );

        Action::macro(
            name: 'edit',
            macro: fn (string $route): Action|false => Action::visit('<i class="fas fa-pen"></i>', $route),
        );
    }

    /**
     * Register macros related to the Money class.
     */
    private function registerMoneyMacros(): void
    {
        Money::macro(
            name: 'convertToCurrency',
            macro: function (Currency|string $currency, ?Carbon $date = null): Money|\Money\Money {
                if (is_string($currency)) {
                    $currency = new Currency($currency);
                }

                if ($this->getCurrency()->equals($currency)) {
                    return $this;
                }

                $exchanger = new SmartRatesExchanger($date);
                $currencies = new ISOCurrencies();
                $converter = new Converter($currencies, $exchanger);

                return $converter->convert($this->getMoney(), $currency);
            },
        );
    }
}
