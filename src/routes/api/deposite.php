<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DepositeController;


Route::post('/deposite', [DepositeController::class, 'store']);
