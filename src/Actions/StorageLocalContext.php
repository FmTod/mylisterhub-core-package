<?php

namespace MyListerHub\Core\Actions;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemAdapter;
use MyListerHub\Core\Concerns\Actions\AsAction;

class StorageLocalContext
{
    use AsAction;

    /**
     * Execute the given callback with the file in the local disk.
     *
     * The file will be moved to the local disk and deleted from the local disk after the callback is executed.
     *
     * @throws \League\Flysystem\FilesystemException
     */
    public function handle(FilesystemAdapter|string $disk, string $path, callable $callback, FilesystemAdapter|string $localDisk = 'local'): mixed
    {
        // Get the remote storage disk.
        $remoteStorage = is_string($disk) ? Storage::disk($disk) : $disk;

        // Get the local storage disk.
        $localStorage = is_string($localDisk) ? Storage::disk($localDisk) : $localDisk;

        // Get the file contents from the remote disk.
        $contents = $remoteStorage->get($path);

        // Get the remote disk prefix if any and prepend it to the filepath.
        $prefix = $remoteStorage->getConfig()['prefix'] ?? null;
        $path = $prefix ? "$prefix/$path" : $path;

        // Put the file contents in the local disk.
        $localStorage->put($path, $contents);

        // Get the path of the file in the local disk.
        $pathname = $localStorage->path($path);

        // Execute the callback with the file path.
        $result = $callback($pathname);

        // Delete the file from the local disk.
        $localStorage->delete($path);

        // Delete the local directory if it is empty.
        if (count($localStorage->allFiles(dirname($path))) === 0) {
            $localStorage->deleteDirectory(dirname($path));
        }

        // Return the result of the callback.
        return $result;
    }
}
