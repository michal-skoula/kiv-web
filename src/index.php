<?php

namespace App;

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Application;
use Core\Facades\App;

$applicationInstance = new Application(
    rootDir: __DIR__ . '/../',
    routes: 'src/Http/routes.php',
    customArgs: []
);


App::initialize($applicationInstance);

App::instance()->handleHttpRequests();
