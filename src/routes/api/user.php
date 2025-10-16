<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;


Route::post('/users/register', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);
