<?php

namespace MyListerHub\Core\Actions;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemAdapter;
use MyListerHub\Core\Concerns\Actions\AsAction;

class TenantStorageLocalContext
{
    use AsAction;

    /**
     * Execute the given callback with the file in the local disk.
     *
     * The file will be moved to the local disk and deleted from the local disk after the callback is executed.
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function handle(string $path, callable $callback, ?string $visibility = null, FilesystemAdapter|string $disk = 'local'): mixed
    {
        return StorageLocalContext::run(Storage::tenant($visibility), $path, $callback, $disk);
    }
}
