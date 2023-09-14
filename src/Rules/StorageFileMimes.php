<?php

namespace MyListerHub\Core\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mime\MimeTypes;

class StorageFileMimes implements Rule
{
    protected array $mimes = [];

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(public string $disk, string ...$mimes)
    {
        $this->mimes = $mimes;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        if (in_array('jpg', $this->mimes, true) || in_array('jpeg', $this->mimes, true)) {
            $this->mimes = array_unique(array_merge($this->mimes, ['jpg', 'jpeg']));
        }

        $mimeType = Storage::disk($this->disk)->mimeType($value);
        $extension = MimeTypes::getDefault()->getExtensions($mimeType)[0] ?? null;

        return in_array($extension, $this->mimes, true);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return trans('validation.mimes', ['values' => implode(',', $this->mimes)]);
    }
}
