<?php

namespace MyListerHub\Core\Actions;

use MyListerHub\Core\Concerns\Actions\AsAction;
use RahulHaque\Filepond\Models\Filepond;

class FilepondLocalContext
{
    use AsAction;

    /**
     * Execute the given callback with the file in the local disk.
     *
     * The file will be moved to the local disk and deleted from the local disk after the callback is executed.
     */
    public function handle(Filepond $file, callable $callback, string $disk = 'local'): mixed
    {
        return StorageLocalContext::run(config('filepond.temp_disk'), $file->filepath, $callback, $disk);
    }
}
