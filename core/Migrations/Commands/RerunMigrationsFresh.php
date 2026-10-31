<?php

namespace Core\Migrations\Commands;

use Core\Console\Attributes\PreventInProduction;
use Core\Console\Contracts\CommandExecutor;
use Core\Console\Enums\Status;
use Core\Exceptions\MigrationException;
use Core\Facades\App;
use Core\Migrations\Actions\CreateMigrationsTable;
use Core\Migrations\Concerns\InteractsWithMigrationFiles;
use Core\Migrations\Concerns\InteractsWithMigrationsTable;
use Core\Migrations\Enums\Direction;

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
            self::executeAsMigration($migration, $statement, 0, Direction::DOWN);
            App::log()->info("Rolling back {$migration}");
        }

        $all = self::getAllMigrationFiles();

        foreach ($all as $migration) {
            $statement = self::getMigrationSql($migration, Direction::UP);
            self::executeAsMigration($migration, $statement, 1, Direction::UP);

            App::log()->info("Running migration {$migration}");
        }

        return Status::SUCCESS;
    }
}