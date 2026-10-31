<?php

namespace Core\Migrations\Commands;

use Core\Console\Contracts\CommandExecutor;
use Core\Console\Enums\Status;
use Core\Exceptions\MigrationException;
use Core\Facades\App;
use Core\Migrations\Concerns\InteractsWithMigrationFiles;
use Core\Migrations\Concerns\InteractsWithMigrationsTable;

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