<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class ProcessTransaction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $depositData;
    /**
     * Create a new job instance.
     */
    public function __construct($depositData)
    {
        $this->depositData = $depositData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $insertRes = DB::table('transactions')
            ->insert([
                'user_id' => $this->depositData['user_id'],
                'type' => $this->depositData['type'],
                'amount' => $this->depositData['amount'],
                'balance_after' => $this->depositData['balance_after'],
                'created_at' => $this->depositData['created_at']
            ]);

        // 刪除redis明細
        if ($insertRes) {
            $key = "transactions:user:{$this->depositData['user_id']}";
            $transactions = Redis::lrange($key, 0, -1);

            foreach ($transactions as $item) {
                $itemData = json_decode($item, true);

                if ($itemData['tx_id'] === $this->depositData['tx_id']) {
                    Redis::lrem($key, 1, $item);
                    break;
                }
            }
        }
    }
}
