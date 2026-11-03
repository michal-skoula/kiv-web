<?php

use Core\Facades\App;
use Core\Routing\Controllers\CoverageRedirectController;
use Core\Routing\Controllers\DebugController;
use Core\Routing\Controllers\PrettyDebugController;
use Core\Routing\Objects\Route;

if(! App::inProduction()) {
    Route::get('/debug/coverage', CoverageRedirectController::class);

    Route::fallback(DebugController::class);
}

Route::get('/users/{id%i}', PrettyDebugController::class)
    ->name('users.show.id');

Route::get('/users/{slug%s}', PrettyDebugController::class)
    ->name('users.show.slug');

Route::get('/users/{id%i}/{performances%i[]}/{?name}', PrettyDebugController::class)
    ->name('users.show.performance');


