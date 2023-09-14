<?php

namespace MyListerHub\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class StorageFileExists implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        public string $disk
    ){
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return Storage::disk($this->disk)->fileExists($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return "The file for the given :attribute field does not exist in the $this->disk disk";
    }
}
