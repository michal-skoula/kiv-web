<?php

namespace PidAnalyzer\Core\Migrations\Commands;

use PidAnalyzer\Core\Console\Attributes\PreventInProduction;
use PidAnalyzer\Core\Console\Concerns\InteractsWithCliArguments;
use PidAnalyzer\Core\Console\Enums\ArgType;
use PidAnalyzer\Core\Console\Enums\Status;
use PidAnalyzer\Core\Exceptions\MigrationException;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Concerns\InteractsWithMigrationFiles;
use PidAnalyzer\Core\Migrations\Concerns\InteractsWithMigrationsTable;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;
use PidAnalyzer\Core\Migrations\Enums\Direction;

#[PreventInProduction(canBeForced: true)]
final class RollBackMigrations implements CommandExecutor
{
    use InteractsWithCliArguments;
    use InteractsWithMigrationsTable;
    use InteractsWithMigrationFiles;

    /**
     * @var int How many steps rollback does if unspecified.
     */
    public const int DEFAULT_ROLLBACK_STEP = 1;

    /**
     * @throws MigrationException
     */
    public static function execute(array &$argv): Status
    {
        if(! self::migrationTableExists()) {
            throw new MigrationException('Nothing to roll back, migration table does not exist');
        }

        $steps = self::getArg($argv, 'steps', ArgType::KEY_VALUE);
        if(! $steps) {
            $default = self::DEFAULT_ROLLBACK_STEP;
            App::log()->info('No --steps passed, using default rollback step of ' . $default);
            $steps = $default;
        }

        $steps = (int)$steps;
        $currentBatchNo = self::getCurrentBatchNumber();
        $rollBackBatchNo = $currentBatchNo - $steps;

        // 0 means no migrations have run yet.
        if ($rollBackBatchNo < 0) {
            throw new MigrationException("Cannot go {$steps} steps back, current batch is number $currentBatchNo");
        }

        // IMPORTANT! Must be ordered descending, otherwise reverse
        // order won't be maintained and stuff will probably break.
        $migrations = self::getExecutedMigrations("where batch > {$rollBackBatchNo} order by batch desc");

        foreach ($migrations as $migration) {
            $statement = self::getMigrationSql($migration, Direction::DOWN);
            self::executeAsMigration($statement, 0, Direction::DOWN);

            App::log()->info("Rolling back migration {$migration}");
        }

        return status::SUCCESS;
    }
}