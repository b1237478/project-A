<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\OperateTransactionService;
use App\Models\Account;

class DepositeController extends Controller
{
    private $operateTransactionService;

    public function __construct( 
        OperateTransactionService $operateTransactionService,
    )
    {
        $this->operateTransactionService = $operateTransactionService;
    }

    /**
     * 新增存款
     *
     * 請求欄位：
     *  integer user_id
     *  numeric amount 金額
     * 
     * @param \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric'
        ]);

        $account = Account::where('user_id', $request->user_id)->first();

        if (!$account) {
            throw new \RuntimeException('Account not exist!');
        }

        // 寫明細進redis
        $operateRes = $this->operateTransactionService->transactionIntoRedis();
        //$redisKey = "deposits:user:{$request->user_id}";

        // job queue寫DB

        if (!$operateRes) {
            throw new \RuntimeException('Error,please try again');
        }
        
        $amount = $request->amount;
        $balance = $account->balance + $amount;
        $version = $account->version;

        DB::transaction(function () use ($account, $balance, $version) {
            $update = Account::where('id', $account->id)
                ->where('version', $version)
                ->update(['balance' => $balance, 'version' => $version + 1]);

            if (!$update) {
                throw new \RuntimeException('Please wait a moment');
            }
        });

        return [
            'result' => 'ok',
            'ret' => [
                'id' => $account->id,
                'user_id' => $account->user_id,
                'balance' => $balance
            ]
        ];
    }
}
