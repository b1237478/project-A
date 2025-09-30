<?php

namespace App\Services;

use COM;
use Illuminate\Support\Facades\Redis;

Class OperationLogService
{
    /**
     * 推送Note訊息
     * 
     * @param string $action create、update、delete
     * @param string $table 目標table
     * @param array $changeData 更動的資料
     */
    public function recordLog($action, $table, $changeData)
    {
        // 寫進redis之後給job WriteOperationLogs寫進DB
        Redis::rpush('operation_logs', json_encode([
            'action' => $action,
            'table' => $table,
            'changes' => $changeData,
            'operator' => 1
        ]));
    }
}
