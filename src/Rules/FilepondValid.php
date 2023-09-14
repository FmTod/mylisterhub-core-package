<?php

namespace MyListerHub\Core\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use RahulHaque\Filepond\Facades\Filepond;

class FilepondValid implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __invoke($attribute, $value, $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute field must be a string');
        }

        $filepond = Filepond::field($value);

        /** @var \RahulHaque\Filepond\Models\Filepond|null $model */
        $model = $filepond->getModel();

        if (! $model) {
            $fail('Could not find the model for the given :attribute field');
        }

        $exists = new StorageFileExists($filepond->getTempDisk());

        if (! $exists->passes($attribute, $model->filepath)) {
            $fail($exists->message());
        }
    }
}
