<?php

namespace PidAnalyzer;

use PidAnalyzer\Core\Application;
use PidAnalyzer\Core\Exceptions\ApplicationException;
use PidAnalyzer\Core\Facades\App;

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
