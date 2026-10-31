<?php

namespace App;

use Core\Application;
use Core\Exceptions\ApplicationException;
use Core\Facades\App;

require_once __DIR__ . '/../vendor/autoload.php';

$applicationInstance = new Application(
    rootDir: __DIR__ . '/../',
    customArgs: []
);

try {
    App::initialize($applicationInstance);
} catch (ApplicationException $e) {
    echo "ERROR: Failed to initialize application: {$e->getMessage()}" . PHP_EOL;
}
