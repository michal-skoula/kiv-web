<?php

namespace PidAnalyzer\Core\Migrations\Commands;

use PidAnalyzer\Core\Console\Enums\Status;
use PidAnalyzer\Core\Exceptions\MigrationException;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Concerns\InteractsWithMigrationFiles;
use PidAnalyzer\Core\Migrations\Concerns\InteractsWithMigrationsTable;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;

final class PrintMigrationStatus implements CommandExecutor
{
    use InteractsWithMigrationFiles;
    use InteractsWithMigrationsTable;

    /**
     * @throws MigrationException
     */
    public static function execute(array &$argv): Status
    {
        $executed =  self::getExecutedMigrations();
        $waiting = array_diff(self::getAllMigrationFiles(), $executed);

        App::log()->info("Waiting to be migrated:", $waiting ?: ['None']);
        App::log()->info("Already migrated:", $executed ?: ['None']);

        return Status::SUCCESS;
    }
}