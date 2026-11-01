<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Application;
use Core\Console\Actions\PreventCommandIfItCantRun;
use Core\Console\Enums\Status;
use Core\Migrations\Commands\PrintHelpMenu;
use Core\Migrations\Enums\Command;

Application::bootstrap(
    rootDir: __DIR__,
    customEnv: [
        'LOG_LEVEL' => 'info'
    ]
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

$executor = $command->executor();
PreventCommandIfItCantRun::handle($executor, $argv);

$executor::execute($argv);