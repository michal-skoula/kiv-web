<?php

use App\Http\Controllers\AboutController;
use Core\Routing\Controllers\DebugController;
use Core\Routing\Controllers\PrettyDebugController;
use Core\Routing\Objects\Route;

Route::get('/about', AboutController::class);
Route::get('/users/{id%i}', PrettyDebugController::class);
Route::get('/friends/{...friends}', DebugController::class);


Route::fallback(PrettyDebugController::class);