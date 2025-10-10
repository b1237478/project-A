<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WithdrawController;


Route::post('/withdraw', [WithdrawController::class, 'store']);
