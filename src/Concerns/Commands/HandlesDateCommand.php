<?php

namespace MyListerHub\Core\Concerns\Commands;

use Carbon\Carbon;

trait HandlesDateCommand
{
    public function getDate($date, string $message = null): Carbon
    {
        if (! $message) {
            $message = 'Please enter a date';
        }

        if (! $date) {
            $date = $this->ask($message, now()->format('Y-m-d'));
        }

        return Carbon::parse($date);
    }

    public function getDateArgument(string $argument = 'date', string $message = null): Carbon
    {
        return $this->getDate($this->argument($argument), $message);
    }

    public function getDateOption(string $option = 'date', string $message = null): Carbon
    {
        return $this->getDate($this->option($option), $message);
    }
}
