<?php

namespace App\Services;

use COM;
use Illuminate\Support\Facades\Redis;

Class OperateTransactionService
{
    /**
     * 紀錄交易資料
     * 
     * @param string $key redis key
     * @param string $type (deposit, withdraw)
     * @param integer $amount 交易金額
     * @param integer $balance 餘額
     * @param string $txId 識別碼
     * @param string $createdAt 新增時間
     * 
     */
    public function recordTransaction($key, $type, $amount, $balance, $txId, $createdAt)
    {
        Redis::rpush($key, json_encode([
            'tx_id' => $txId,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $balance,
            'created_at' => $createdAt
        ]));
    }
}
