<?php

namespace MyListerHub\Core\Concerns\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait SecureUpdatable
 * Allows to secure update without override the existing fields in a model
 * This depends on the $fillable and $guarded attributes too.
 */
trait Completable
{
    /**
     * Updates without overriding the existing fields in a model.
     *
     * @return Model|$this
     */
    public static function completeOrCreate($attributes, array $values = []): Model|static
    {
        return tap(self::firstOrNew($attributes), static function ($instance) use ($values) {
            $instance->complete($values);
        });
    }

    /**
     * Updates without overriding the existing fields in a model.
     */
    public function complete($values): bool
    {
        /** @var Model $this */
        $blankValues = collect($values)
            ->filter(fn ($fieldValue, $fieldName) => blank($this->getAttribute($fieldName)))
            ->toArray();

        return $this->fill($blankValues)->save();
    }
}
