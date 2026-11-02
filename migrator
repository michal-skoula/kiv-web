<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\Application\Application;
use Core\Application\DataStores\PathsStorage;
use Core\Console\Actions\PreventCommandIfItCantRun;
use Core\Console\Enums\Status;
use Core\Exceptions\ApplicationException;
use Core\Migrations\Commands\PrintHelpMenu;
use Core\Migrations\Enums\Command;

try {
    Application::bootstrap(
        paths: new PathsStorage(appRoot: __DIR__)
            ->setMigrationsPath('/migrations'),
        customEnv: [
            'LOG_LEVEL' => 'info'
        ]
    );
} catch (ApplicationException $e) {
    Status::FAILURE->exit("Failed to start migrator: {$e->getMessage()}");
}


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