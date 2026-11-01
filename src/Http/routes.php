<?php

use App\Http\Controllers\AboutController;
use Core\Routing\Controllers\DebugController;
use Core\Routing\Objects\Route;

Route::get('/about', AboutController::class);

Route::fallback(DebugController::class);