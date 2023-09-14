<?php

namespace MyListerHub\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Macellan\Zip\Zip;

class LogClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear {--z|zip=true : Whether the deleted logs should be stored in a zip file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove every log files in the log directory';

    /**
     * A filesystem instance.
     */
    private Filesystem $disk;

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $disk)
    {
        parent::__construct();

        $this->disk = $disk;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        $files = $this->getLogFiles();

        if ($this->option('zip')) {
            $this->zip($files);
        }

        $deleted = $this->delete($files);

        if (! $deleted) {
            $this->info('There was no log file to delete in the log folder');
        } elseif ($deleted === 1) {
            $this->info('1 log file has been deleted');
        } else {
            $this->info($deleted.' log files have been deleted');
        }
    }

    /**
     * Get a collection of log files sorted by their last modification date.
     */
    private function getLogFiles(): Collection
    {
        $logPath = storage_path('logs');

        return collect($this->disk->allFiles($logPath))
            ->filter(fn ($file) => Str::endsWith($file, '.log'))
            ->sortBy('mtime');
    }

    /**
     * Delete the given files.
     */
    private function delete(Collection $files): int
    {
        return $files->each(fn ($file) => $this->disk->delete($file))->count();
    }

    /**
     * Zip the given files.
     *
     *
     * @throws \Exception
     */
    private function zip(Collection $files): void
    {
        $path = storage_path('logs');
        $date = now()->format('mdYHis');
        $fileName = "$path/logs_$date.zip";

        $zip = Zip::create($fileName, true);
        $zip->add($files->toArray());
        $zip->close();
    }
}
