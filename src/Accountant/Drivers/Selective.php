<?php

declare(strict_types=1);

namespace MyListerHub\Core\Accountant\Drivers;

use Altek\Accountant\Contracts\Ledger;
use Altek\Accountant\Contracts\Notary;
use Altek\Accountant\Contracts\Recordable;
use Altek\Accountant\Drivers\Database;
use Altek\Accountant\Exceptions\AccountantException;
use Illuminate\Support\Facades\Config;

class Selective extends Database
{
    /**
     * Create a Ledger from a Recordable model.
     *
     * @throws \Altek\Accountant\Exceptions\AccountantException
     */
    public function record(
        Recordable $model,
        string $event,
        string $pivotRelation = null,
        array $pivotProperties = []
    ): Ledger {
        $notary = Config::get('accountant.notary');

        if (! is_subclass_of($notary, Notary::class)) {
            throw new AccountantException(sprintf('Invalid Notary implementation: "%s"', $notary));
        }

        $implementation = Config::get('accountant.ledger.implementation');

        if (! is_subclass_of($implementation, Ledger::class)) {
            throw new AccountantException(sprintf('Invalid Ledger implementation: "%s"', $implementation));
        }

        $ledger = new $implementation();

        $ignore = collect($model->getDirty())->keys()
            ->when(isset($model->recordableAttributes) || isset($model->recordableData))
            ->filter(fn ($key) => in_array($key, array_merge(
                $model->recordableAttributes ?? [],
                $model->recordableData ?? [],
            )))
            ->isNotEmpty();

        if ($ignore) {
            return $ledger;
        }

        // Set the Ledger properties
        foreach ($model->gather($event) as $property => $value) {
            $ledger->setAttribute($property, $value);
        }

        if ($ledger->usesTimestamps()) {
            $ledger->setCreatedAt($ledger->freshTimestamp())
                ->setUpdatedAt($ledger->freshTimestamp());
        }

        $ledger->setAttribute('pivot', $pivotRelation ? [
            'relation' => $pivotRelation,
            'properties' => $pivotProperties,
        ] : []);

        // Sign and store the record
        $ledger->setAttribute('signature', call_user_func([$notary, 'sign'], $ledger->attributesToArray()))
            ->save();

        return $ledger;
    }
}
