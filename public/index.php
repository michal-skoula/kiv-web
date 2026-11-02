<?php

namespace App;

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Application\Application;
use Core\Application\DataStores\PathsStorage;
use Core\Facades\App;

Application::bootstrap(
    paths: new PathsStorage(__DIR__ . '/../')
        ->setRoutesPath('src/Http/routes.php')
    ,
    customEnv: []
);

App::instance()->handleHttpRequests();