<?php

namespace PidAnalyzer\Core\Migrations\Commands;

use PDO;
use PDOException;
use PidAnalyzer\Core\Cli\Status;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Actions\CreateMigrationsTable;
use PidAnalyzer\Core\Migrations\Concerns\ChecksMigrationTableExists;
use PidAnalyzer\Core\Migrations\Concerns\RetrievesMigrations;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;
use PidAnalyzer\Core\Migrations\Contracts\PreventInProduction;

#[PreventInProduction(canBeForced: true)]
final class RunMigrations implements CommandExecutor
{
    use RetrievesMigrations;
    use ChecksMigrationTableExists;

    public static function execute(array $argv): Status
    {
        if(! self::migrationTableExists()) {
            CreateMigrationsTable::handle();
        }

        try {
            $waiting = self::getWaitingMigrations();
        }
        catch (\Exception $e) {
            App::log()->error("Failed to get migrations: {$e->getMessage()}");
            return Status::FAILURE;
        }

        if(count($waiting) === 0) {
            App::log()->info("No new migrations to execute.");
            return Status::SUCCESS;
        }

        $batchNo = self::getNextBatchNumber();

        foreach($waiting as $migration)
        {
            $sql = file_get_contents(self::pathToMigrations() . $migration);
            if(! $sql) {
                App::log()->error("Unable to read migration '{$migration}'");
                return Status::FAILURE;
            }

            try {
                App::db()->beginTransaction();
                self::executeMigrationSql($sql);
                self::writeMigrationToTable($migration, $batchNo);
                App::db()->commit();
            }
            catch (PDOException $e) {
                App::db()->rollBack();
                throw $e;
            }

            App::log()->info("Running migration {$migration}");
        }

        return Status::SUCCESS;
    }

    private static function executeMigrationSql(string $sql): void
    {
        App::db()->exec($sql);
    }

    private static function writeMigrationToTable(string $migration, int $batchNo): void
    {
        App::db()->exec("insert into migrations (migration, batch) values ('$migration', '$batchNo')");
    }

    private static function getNextBatchNumber(): int
    {
        $statement = App::db()->prepare("select max(batch) from migrations");
        $statement->execute();

        /** @var int|null $lastBatchNo */
        $lastBatchNo = $statement->fetchAll(PDO::FETCH_COLUMN)[0];

        // null is int cast to 0, which makes this work even when no migrations have run.
        return (int)$lastBatchNo + 1;
    }


}