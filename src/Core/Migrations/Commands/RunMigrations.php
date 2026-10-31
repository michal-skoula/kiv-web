<?php

namespace PidAnalyzer\Core\Migrations\Commands;

use PidAnalyzer\Core\Console\Attributes\PreventInProduction;
use PidAnalyzer\Core\Console\Enums\Status;
use PidAnalyzer\Core\Exceptions\MigrationException;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Actions\CreateMigrationsTable;
use PidAnalyzer\Core\Migrations\Concerns\InteractsWithMigrationFiles;
use PidAnalyzer\Core\Migrations\Concerns\InteractsWithMigrationsTable;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;
use PidAnalyzer\Core\Migrations\Enums\Direction;

#[PreventInProduction(canBeForced: true)]
final class RunMigrations implements CommandExecutor
{
    use InteractsWithMigrationFiles;
    use InteractsWithMigrationsTable;

    /**
     * @throws MigrationException
     */
    public static function execute(array &$argv): Status
    {
        if(! self::migrationTableExists()) {
            CreateMigrationsTable::handle();
        }

        try {
            $waiting = array_diff(self::getAllMigrationFiles(), self::getExecutedMigrations());
        }
        catch (\Exception $e) {
            throw MigrationException::from($e, "Failed to get migrations: {$e->getMessage()}");
        }

        if(count($waiting) === 0) {
            App::log()->info("No new migrations to execute.");
            return Status::SUCCESS;
        }

        $batchNo = self::getCurrentBatchNumber() + 1;

        foreach($waiting as $migration)
        {
            $statement = self::getMigrationSql($migration, Direction::UP);
            self::executeAsMigration($statement, $batchNo, Direction::UP);
            App::log()->info("Running migration {$migration}");
        }

        return Status::SUCCESS;
    }
}