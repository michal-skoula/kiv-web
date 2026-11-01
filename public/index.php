<?php

namespace App;

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Application;
use Core\Facades\App;

Application::bootstrap(
    rootDir: __DIR__ . '/../',
    routes: 'src/Http/routes.php',
    customEnv: []
);

App::instance()->handleHttpRequests();
