<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SlotGameController;





Route::post('/auth', [SlotGameController::class, 'auth']);
Route::get('/game-permissions', [SlotGameController::class, 'gamePermissions'])->middleware('auth:sanctum');
Route::get('/start-game', [SlotGameController::class, 'startGame'])->middleware('auth:sanctum');


Route::get('/leaders-borad', [SlotGameController::class, 'leadersBorad'])->middleware('auth:sanctum');

