<?php

require_once __DIR__ . '/vendor/autoload.php';

use PidAnalyzer\Core\Application;
use PidAnalyzer\Core\Console\Actions\PreventCommandIfItCantRun;
use PidAnalyzer\Core\Console\Enums\Status;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Commands\PrintHelpMenu;
use PidAnalyzer\Core\Migrations\Enums\Command;

App::initialize(new Application(
    rootDir: __DIR__,
    customArgs: [
        'LOG_LEVEL' => 'info'
    ])
);

// 2nd argument is the commands mode, which is required.
if(! isset($argv[1])) {
    Status::FAILURE->exit("ERROR: Missing `mode` parameter. " . PrintHelpMenu::HELP_MSG);
}

try {
    $command = Command::from($argv[1]);
} catch (ValueError $e) {
    Status::FAILURE->exit("ERROR: Unknown mode <{$argv[1]}>. " . PrintHelpMenu::HELP_MSG);
}

PreventCommandIfItCantRun::handle($command, $argv);

$command->handler()::execute($argv);