<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Services\OperateTransactionService;
use App\Services\CheckBalanceService;
use App\Jobs\ProcessTransaction;
use App\Models\Account;

class WithdrawController extends Controller
{
    private $operateTransactionService;
    private $checkBalanceService;

    public function __construct( 
        OperateTransactionService $operateTransactionService,
        CheckBalanceService $checkBalanceService
    )
    {
        $this->operateTransactionService = $operateTransactionService;
        $this->checkBalanceService = $checkBalanceService;
    }

    /**
     * 新增取款
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

        $userId = $request->user_id;
        $account = Account::where('user_id', $userId)->first();

        if (!$account) {
            throw new \RuntimeException('Account not exist!');
        }

        $amount = $request->amount;

        // 計算餘額
        $balance = $this->checkBalanceService->checkAccountBalance($userId, $amount);

        if ($balance < 0) {
            throw new \RuntimeException('Balance not enough!');
        }

        $txId = (string) Str::uuid(); // 產生唯一識別碼
        $createdAt = now()->format('Y-m-d H:i:s');
        $key = "transactions:user:{$userId}";

        // 寫明細進redis
        $this->operateTransactionService->recordTransaction(
            $key,
            'withdraw',
            $amount,
            $balance,
            $txId,
            $createdAt
        );

        $data = [
            'tx_id' => $txId,
            'user_id' => $userId,
            'amount' => $amount,
            'type' => 'withdraw',
            'balance_after' => $balance,
            'created_at' => $createdAt
        ];

        ProcessTransaction::dispatch($data);// job queue寫DB

        $version = $account->version;

        DB::transaction(function () use ($account, $balance, $version, $userId) {
            $update = Account::where('id', $account->id)
                ->where('version', $version)
                ->update(['balance' => $balance, 'version' => $version + 1]);

            if (!$update) {
                throw new \RuntimeException('Please wait a moment');
            }

            Redis::set("account:balance:{$userId}", $balance);
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
