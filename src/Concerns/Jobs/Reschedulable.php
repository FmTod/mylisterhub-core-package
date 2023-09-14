<?php

namespace MyListerHub\Core\Concerns\Jobs;

use Illuminate\Bus\Batchable;

trait Reschedulable
{
    public function reschedule(int $delay = 0, ...$arguments): void
    {
        $job = (new self(...$arguments))->delay($delay);
        $uses = array_flip(class_uses_recursive(static::class));

        $this->delete();

        if (isset($uses[Batchable::class]) && $this->batch()) {
            $this->batch()->add([$job]);
        } else {
            dispatch($job)->onConnection($this->connection)->onQueue($this->queue);
        }
    }
}
