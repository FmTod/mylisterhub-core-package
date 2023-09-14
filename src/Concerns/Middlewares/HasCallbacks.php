<?php

namespace MyListerHub\Core\Concerns\Middlewares;

use Closure;

trait HasCallbacks
{
    protected ?Closure $onThen = null;

    protected ?Closure $onAfter = null;

    protected ?Closure $onCatch = null;

    protected ?Closure $onFinally = null;

    public function then(callable $callback): static
    {
        $this->onThen = $callback;

        return $this;
    }

    public function after(callable $callback): static
    {
        $this->onAfter = $callback;

        return $this;
    }

    public function catch(callable $callback): static
    {
        $this->onCatch = $callback;

        return $this;
    }

    public function finally(callable $callback): static
    {
        $this->onFinally = $callback;

        return $this;
    }

    protected function thenCallback(...$args): void
    {
        if ($this->onThen) {
            ($this->onThen)(...$args);
        }
    }

    protected function afterCallback(...$args): void
    {
        if ($this->onAfter) {
            ($this->onAfter)(...$args);
        }
    }

    protected function catchCallback(...$args): void
    {
        if ($this->onCatch) {
            ($this->onCatch)(...$args);
        }
    }

    protected function finallyCallback(...$args): void
    {
        if ($this->onFinally) {
            ($this->onFinally)(...$args);
        }
    }
}
