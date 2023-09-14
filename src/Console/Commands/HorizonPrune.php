<?php

namespace MyListerHub\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class HorizonPrune extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horizon:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush all of the failed queue jobs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->call('queue:flush');

        $arrayFailed = Redis::connection('horizon')->keys('failed:*');
        $arrayFailedJobs = Redis::connection('horizon')->keys('failed_jobs');
        $arrayToRemove = array_merge($arrayFailed, $arrayFailedJobs);

        $arrayMap = array_map(static fn ($k) => str_replace(config('horizon.prefix'), '', $k), $arrayToRemove);

        Redis::connection('horizon')->del($arrayMap);

        $this->line('Failed jobs flushed!');

        return Command::SUCCESS;
    }
}
