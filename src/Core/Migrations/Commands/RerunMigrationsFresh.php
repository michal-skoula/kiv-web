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
final class RerunMigrationsFresh implements CommandExecutor
{
    use InteractsWithMigrationsTable;
    use InteractsWithMigrationFiles;

    /**
     * @throws MigrationException
     */
    public static function execute(array &$argv): Status
    {
        if(! self::migrationTableExists()) {
            CreateMigrationsTable::handle();
        }

        $executed = self::getExecutedMigrations("where batch > 0 order by batch desc");

        foreach ($executed as $migration) {
            $statement = self::getMigrationSql($migration, Direction::DOWN);
            self::executeAsMigration($statement, 0, Direction::DOWN);
            App::log()->info("Rolling back {$migration}");
        }

        $all = self::getAllMigrationFiles();

        foreach ($all as $migration) {
            $statement = self::getMigrationSql($migration, Direction::UP);
            self::executeAsMigration($statement, 1, Direction::UP);

            App::log()->info("Running migration {$migration}");
        }

        return Status::SUCCESS;
    }
}