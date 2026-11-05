<?php

use App\Models\Post;
use Core\Facades\App;
use Core\Routing\Controllers\Debug\CoverageRedirectController;
use Core\Routing\Controllers\Debug\DebugController;
use Core\Routing\Controllers\Debug\PrettyDebugController;
use Core\Routing\Objects\Request;
use Core\Routing\Objects\Route;

if(! App::inProduction()) {
    Route::get('/debug/coverage', CoverageRedirectController::class);

    Route::fallback(PrettyDebugController::class);
}

Route::get('/users/{id%i}', PrettyDebugController::class)
    ->name('users.show.id');

Route::get('/users/{slug%s}', PrettyDebugController::class)
    ->name('users.show.slug');

Route::get('/users/{id%i}/{performances%i[]}/{?name}', PrettyDebugController::class)
    ->name('users.show.performance');

Route::get('/posts/{post%m[]}', PrettyDebugController::class);


