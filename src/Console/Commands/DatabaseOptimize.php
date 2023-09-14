<?php

namespace MyListerHub\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;

class DatabaseOptimize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:optimize
                        {--database=default : Default database is set in the config. Database that needs to be optimized.}
                        {--table=* : Defaulting to all tables in the default database.}';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Table optimizer for database';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Optimize table/s of the database';

    /**
     * Progress bar instance.
     */
    protected ProgressBar $progress;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting Optimization.');

        $tables = $this->getTables();

        $this->progress = $this->output->createProgressBar($tables->count());

        $tables->each(fn ($table) => $this->optimize($table));

        $this->info(PHP_EOL.'Optimization Completed');
    }

    /**
     * Get database which need optimization.
     */
    protected function getDatabase(): string
    {
        $database = $this->option('database');
        if ($database === 'default') {
            return DB::getDatabaseName();
        }

        // Check if the database exists
        if (is_string($database) && $this->existsDatabase($database)) {
            return $database;
        }

        throw new RuntimeException("The database $database doesn't exists.");
    }

    /**
     * Check if the database exists.
     */
    protected function existsDatabase(string $databaseName): bool
    {
        return DB::table('INFORMATION_SCHEMA.SCHEMATA')
            ->select('SCHEMA_NAME')
            ->where('SCHEMA_NAME', $databaseName)
            ->count();
    }

    /**
     * Get all the tables that need to the optimized.
     */
    protected function getTables(): Collection
    {
        $tableList = collect($this->option('table'));

        if ($tableList->isEmpty()) {
            return DB::table('INFORMATION_SCHEMA.TABLES')
                ->where('TABLE_SCHEMA', $this->getDatabase())
                ->pluck('TABLE_NAME');
        }

        // Check if the table exists
        if ($this->existsTables($tableList)) {
            return $tableList;
        }

        throw new RuntimeException("One or more tables provided doesn't exists.");
    }

    /**
     * Check if the table exists.
     */
    protected function existsTables(Collection $tables): bool
    {
        return DB::table('INFORMATION_SCHEMA.TABLES')
            ->where('TABLE_SCHEMA', $this->getDatabase())
            ->whereIn('TABLE_NAME', $tables)
            ->count() === $tables->count();
    }

    /**
     * Optimize the table.
     */
    protected function optimize(string $table): void
    {
        $result = DB::select("OPTIMIZE TABLE `$table`");
        $messages = collect($result)->pluck('Msg_text');

        if ($messages->contains('OK')) {
            $this->progress->advance();
        }
    }
}
