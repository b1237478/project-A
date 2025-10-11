<?php

namespace App\Services;

use COM;
use Illuminate\Support\Facades\Redis;
use App\Models\Account;

Class CheckBalanceService
{
    /**
     * 檢查餘額
     * 
     * @param string $userId
     * @param integer $amount 取款金額
     * 
     * @return integer|boolean 
     */
    public function checkAccountBalance($userId, $amount)
    {
        $balance = Redis::get("account:balance:{$userId}");

        if ($balance === null) {
            $account = Account::where('user_id', $userId)->first();
            $balance = $account->balance;

            Redis::set("account:balance:{$userId}", $balance);
        }

        $newBalance = $balance - $amount;

        return $newBalance;   
    }
}
