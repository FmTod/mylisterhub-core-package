<?php

namespace MyListerHub\Core\Concerns\Ledgers;

use Altek\Accountant\Contracts\Cipher;
use Altek\Accountant\Exceptions\AccountantException;
use Altek\Accountant\Recordable as RecordableBase;
use Altek\Accountant\Resolve;
use Illuminate\Support\Facades\Config;

trait Recordable
{
    use RecordableBase;

    public function gather(string $event): array
    {
        if (! $this->isRecordingEnabled()) {
            throw new AccountantException('Recording is not enabled');
        }

        if (! $this->isEventRecordable($event)) {
            throw new AccountantException(sprintf('Invalid event: "%s"', $event));
        }

        // Gather the modified properties
        $modified = collect($this->getDirty())->keys()
            ->when(isset($this->recordableAttributes) || isset($this->recordableData))
            ->filter(fn ($key) => in_array($key, array_merge(
                $this->recordableAttributes ?? [],
                $this->recordableData ?? [],
            )))
            ->toArray();

        // Gather the Recordable properties
        $properties = collect($this->getAttributes())
            ->when(isset($this->recordableData))
            ->filter(fn ($value, $key) => in_array($key, $this->recordableData))
            ->toArray();

        // Cipher property values
        foreach ($this->getCiphers() as $property => $implementation) {
            if (! array_key_exists($property, $properties)) {
                throw new AccountantException(sprintf('Invalid property: "%s"', $property));
            }

            if (! is_subclass_of($implementation, Cipher::class)) {
                throw new AccountantException(sprintf('Invalid Cipher implementation: "%s"', $implementation));
            }

            $properties[$property] = call_user_func([$implementation, 'cipher'], $properties[$property]);
        }

        $user = Resolve::user();

        $userPrefix = Config::get('accountant.user.prefix');

        return [
            $userPrefix.'_id' => $user ? $user->getIdentifier() : null,
            $userPrefix.'_type' => $user ? $user->getMorphClass() : null,
            'context' => Resolve::context(),
            'event' => $event,
            'recordable_id' => $this->getIdentifier(),
            'recordable_type' => $this->getMorphClass(),
            'properties' => $properties,
            'modified' => $modified,
            'extra' => $this->supplyExtra($event, $properties, $user),
            'url' => Resolve::url(),
            'ip_address' => Resolve::ipAddress(),
            'user_agent' => Resolve::userAgent(),
        ];
    }
}
