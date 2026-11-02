<?php

use Core\Facades\App;
use Core\Routing\Controllers as CoreControllers;
use Core\Routing\Objects\Route;

if(App::isProduction()) {
    Route::get('/debug/coverage', CoreControllers\CoverageRedirectController::class);

//    Route::fallback(CoreControllers\PrettyDebugController::class);
}


