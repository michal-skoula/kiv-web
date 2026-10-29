<?php

require_once __DIR__ . '/vendor/autoload.php';

use PidAnalyzer\Core\Application;
use PidAnalyzer\Core\Cli\CliArgReader;
use PidAnalyzer\Core\Cli\CliArgType;
use PidAnalyzer\Core\Cli\Status;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Command;
use PidAnalyzer\Core\Migrations\Commands\PrintHelpMenu;
use PidAnalyzer\Core\Migrations\MigratorData;

App::initialize(new Application(
    rootDir: __DIR__,
    customArgs: [
        'LOG_LEVEL' => 'info'
    ])
);
CliArgReader::loadArgv($argv);

/**
 * Terminates the script in production environments.
 *
 * If the `--force` flag is passed, this check is bypassed.
 *
 * @param bool $forceCheckDisabled Prevent --force from being applied.
 */
function preventInProd(bool $forceCheckDisabled = false): void
{
    // Ignores the second value on purpose, as boolean flags don't pass a value.
    [$forced, $_] = CliArgReader::read('force', CliArgType::BOOLEAN);

    if (App::env() !== 'production') {
        return;
    }

    if(! $forceCheckDisabled && $forced) {
        return;
    }

    Status::FAILURE->exit("ERROR: Operation not allowed in production.");
}

// Checks that the migrator isn't being run in an unintended way
if(! isset($argv[0]) || $argv[0] !== MigratorData::SCRIPT_NAME) {
    Status::FAILURE->exit("ERROR: Not running via the CLI tool. Check that the migrator tool was not renamed.");
}

// 2nd argument is the commands mode, which is required.
if(! isset($argv[1])) {
    Status::FAILURE->exit("ERROR: Missing `mode` parameter. " . PrintHelpMenu::HELP_MSG);
}

//$migrator = new Migrator($argv);

try {
    $command = Command::from($argv[1]);
} catch (ValueError $e) {
    Status::FAILURE->exit("ERROR: Unknown mode <{$argv[1]}>. " . PrintHelpMenu::HELP_MSG);
}

// 1. check if the class string has the PreventInProduction attribute
// 2. check whether it should prevent outright or check for --force
// 3. execute
$command->handler()::execute($argv);

// Mode switcher


switch($argv[1]) {
    case '-h': case '--help': case 'help':
        $migrator->help()->exit();
    case 'status':
        $migrator->status()->exit();

    case 'make':
        preventInProd(forceCheckDisabled: true);
        [$exists, $name] = CliArgReader::read('name');
        if(! $exists) {
            Status::ARG_ERROR->exit("ERROR: Missing required argument `--name`.");
        }
        $migrator->create($name)->exit();

    case 'migrate':
        preventInProd();
        $migrator->migrate()->exit();

    case 'rollback':
        preventInProd();
        [$exists, $steps] = CliArgReader::read('steps');
        if(! $exists) {
            $steps = MigratorData::DEFAULT_ROLLBACK_STEP;
        }
        if(! is_numeric($steps) || $steps <= 0) {
            Status::ARG_ERROR->exit("ERROR: `--steps` must be a positive integer.");
        }
        $migrator->rollback($steps)->exit();

    case 'fresh':
        preventInProd();
        $migrator->fresh()->exit();

    default:
        Status::ARG_ERROR->exit("ERROR: Unknown mode <{$argv[1]}>. " . PrintHelpMenu::HELP_MSG);
}