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

    public $transactionData;
    /**
     * Create a new job instance.
     */
    public function __construct($transactionData)
    {
        $this->transactionData = $transactionData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $insertRes = DB::table('transactions')
            ->insert([
                'user_id' => $this->transactionData['user_id'],
                'type' => $this->transactionData['type'],
                'amount' => $this->transactionData['amount'],
                'balance_after' => $this->transactionData['balance_after'],
                'created_at' => $this->transactionData['created_at']
            ]);

        // 刪除redis明細
        if ($insertRes) {
            $key = "transactions:user:{$this->transactionData['user_id']}";
            $transactions = Redis::lrange($key, 0, -1);

            foreach ($transactions as $item) {
                $itemData = json_decode($item, true);

                if ($itemData['tx_id'] === $this->transactionData['tx_id']) {
                    Redis::lrem($key, 1, $item);
                    break;
                }
            }
        }
    }
}
