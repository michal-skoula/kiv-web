<?php

use Core\Facades\App;
use Core\Routing\Controllers\CoverageRedirectController;
use Core\Routing\Controllers\PrettyDebugController;
use Core\Routing\Objects\Route;

if(App::inProduction()) {
    Route::get('/debug/coverage', CoverageRedirectController::class);
}

Route::get('/users/{id%i}', PrettyDebugController::class)
    ->name('users.show');

Route::get('/users/{id%i}/{performances%[i]}/{?name}', PrettyDebugController::class)
    ->name('users.show.performance');


