<?php

namespace MyListerHub\Core\Concerns\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasActionMessage
{
    public function actionMessage(Builder $query, string $action, bool $queued = false, bool $lockable = false, string $subject = null): string
    {
        $count = $query->clone()->when($lockable, fn (Builder $query) => $query->where('is_locked', false))->count();
        $lockedCount = $lockable ? $query->clone()->where('is_locked', true)->count() : 0;

        if (! $subject) {
            $subject = substr(strrchr(get_class($query->getModel()), '\\'), 1);
        }

        if ($queued) {
            return sprintf(
                '%s %s will be %s shortly%s.',
                $count,
                Str::of($subject)->lower()->plural($count)->toString(),
                $action,
                $lockedCount > 0 ? " ($lockedCount locked)" : '',
            );
        }

        return sprintf(
            '%s %s %s successfully%s.',
            $count,
            Str::of($subject)->lower()->plural($count)->toString(),
            $action,
            $lockedCount > 0 ? " ($lockedCount locked)" : '',
        );
    }
}
