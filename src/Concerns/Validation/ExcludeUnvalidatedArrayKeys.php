<?php

namespace MyListerHub\Core\Concerns\Validation;

use Illuminate\Contracts\Validation\Validator;

trait ExcludeUnvalidatedArrayKeys
{
    protected bool $excludeUnvalidatedArrayKeys = true;

    /**
     * Get the validator instance for the request.
     */
    protected function getValidatorInstance(): Validator
    {
        return tap(
            parent::getValidatorInstance(),
            fn (Validator $validator) => $validator->excludeUnvalidatedArrayKeys = $this->excludeUnvalidatedArrayKeys
        );
    }
}
