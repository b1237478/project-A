<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;


Route::get('/notes', [NoteController::class, 'index']);
Route::post('/notes', [NoteController::class, 'store']);
Route::put('/notes/{id}', [NoteController::class, 'update']);
Route::delete('/notes/{id}', [NoteController::class, 'destory']);

//Route::middleware(['auth:api'])->group(function () {});
