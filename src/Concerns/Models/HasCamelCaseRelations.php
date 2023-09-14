<?php

namespace MyListerHub\Core\Concerns\Models;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use LogicException;

trait HasCamelCaseRelations
{
    /**
     * Determine if the given relation is loaded.
     *
     * @param  string  $key
     */
    public function relationLoaded($key): bool
    {
        return array_key_exists($key, $this->relations) || array_key_exists(Str::camel($key), $this->relations);
    }

    /**
     * Determine if the given key is a relationship method on the model.
     *
     * @param  string  $key
     */
    public function isRelation($key): bool
    {
        return method_exists($this, $key) ||
            method_exists($this, Str::camel($key)) ||
            (static::$relationResolvers[get_class($this)][$key] ?? null) ||
            (static::$relationResolvers[get_class($this)][Str::camel($key)] ?? null);
    }

    /**
     * Get a relationship value from a method.
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromMethod($method)
    {
        $relation = method_exists($this, Str::camel($method)) ? $this->{Str::camel($method)}() : $this->$method();

        if (! $relation instanceof Relation) {
            if (is_null($relation)) {
                throw new LogicException(sprintf(
                    '%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?',
                    static::class,
                    $method
                ));
            }

            throw new LogicException(sprintf(
                '%s::%s must return a relationship instance.',
                static::class,
                $method
            ));
        }

        return tap($relation->getResults(), function ($results) use ($method) {
            $this->setRelation($method, $results);
        });
    }

    /**
     * Get a relationship.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[$key] ?? $this->relations[Str::camel($key)];
        }

        if (! $this->isRelation($key)) {
            return;
        }

        if ($this->preventsLazyLoading) {
            $this->handleLazyLoadingViolation($key);
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        return $this->getRelationshipFromMethod($key);
    }
}
