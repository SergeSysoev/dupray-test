<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\GameController;

Route::get('/', [GameController::class, 'index']);

Route::get('/games', [GameController::class, 'fetchGames']);
Route::get('/getGames', [GameController::class, 'fetchGames']);
