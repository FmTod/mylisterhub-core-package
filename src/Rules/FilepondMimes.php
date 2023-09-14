<?php

namespace MyListerHub\Core\Rules;

use RahulHaque\Filepond\Facades\Filepond;

class FilepondMimes extends StorageFileMimes
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string ...$mimes)
    {
        parent::__construct(config('filepond.temp_disk'), ...$mimes);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $filepond = Filepond::field($value);

        /** @var \RahulHaque\Filepond\Models\Filepond|null $model */
        $model = $filepond->getModel();

        return parent::passes($attribute, $model->filepath);
    }
}
