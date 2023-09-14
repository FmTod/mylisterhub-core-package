<?php

namespace MyListerHub\Core\Concerns\API;

use Illuminate\Support\Facades\App;
use MyListerHub\Core\Http\Request;

trait HasRequest
{
    protected function getRequest(): string
    {
        return $this->request ?? Request::class;
    }

    public function initializeHasRequest(): void
    {
        $this->bindRequestClass();
    }

    protected function bindRequestClass(): void
    {
        App::bind(Request::class, $this->getRequest());
    }

    protected function shouldValidateRequest(): bool
    {
        if (isset($this->validateRequest)) {
            return $this->validateRequest;
        }

        return isset($this->request) && $this->request !== Request::class;
    }
}
