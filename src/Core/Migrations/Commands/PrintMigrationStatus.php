<?php

namespace PidAnalyzer\Core\Migrations\Commands;

use PidAnalyzer\Core\Cli\Status;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Concerns\RetrievesMigrations;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;

class PrintMigrationStatus implements CommandExecutor
{
    use RetrievesMigrations;

    public static function execute(array $argv): Status
    {
        App::log()->info("Waiting to be migrated:", self::getWaitingMigrations() ?: ['None']);
        App::log()->info("Already migrated:", self::getExecutedMigrations() ?: ['None']);

        return Status::SUCCESS;
    }
}