<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class WriteOperationLogs implements ShouldQueue
{
    use Queueable;

    const REDIS_KEY = 'operation_logs';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $logs = [];

        for ($i = 0; $i < 100; $i++) {
            $opLog = Redis::lpop(self::REDIS_KEY);

            if (!$opLog) break;

            $logs[] = json_decode($opLog, true);
        }

        if (!empty($logs)) {
            DB::table('operation_logs')->insert(array_map(fn($log) => [
                'action' => $log['action'],
                'table' => $log['table'],
                'changes' => json_encode($log['changes']),
                'operator' => $log['operator'],
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s')
            ], $logs));
        }
    }
}
