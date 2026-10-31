<?php

namespace PidAnalyzer\Core\Migrations\Concerns;

use PDO;
use PDOException;
use PidAnalyzer\Core\Exceptions\MigrationException;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Enums\Direction;

trait InteractsWithMigrationsTable
{
    public static function migrationTableExists(): bool
    {
        $statement = App::db()->prepare("select name from sqlite_schema where type = 'table' and name = 'migrations'");
        $statement->execute();

        return count($statement->fetchAll()) === 1;
    }

    /**
     * @param string $sqlExtra Additional SQL conditions that go after `from migrations`.
     * @return array
     */
    private static function getExecutedMigrations(string $sqlExtra = ''): array
    {
        $statement = App::db()->prepare("SELECT migration from migrations {$sqlExtra}");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private static function getCurrentBatchNumber(): int
    {
        $statement = App::db()->prepare("select max(batch) from migrations");
        $statement->execute();

        /** @var int|null $batchNo Null when the table is empty. */
        $batchNo = $statement->fetchAll(PDO::FETCH_COLUMN)[0];

        return (int)$batchNo;
    }

    /**
     * Runs a given SQL query with an accompanying update to the migrations table.
     *
     * @param string $statement SQL statement.
     * @param int $batch The current batch number for UP, and the lower bound of what to roll back for DOWN.
     * @param Direction $direction
     *
     * @return void
     *
     * @throws MigrationException
     */
    private static function executeAsMigration(string $statement, int $batch, Direction $direction): void
    {
        $db = App::db();
        try {
            $db->beginTransaction();
            $db->exec($statement);
            $db->exec(match($direction) {
                Direction::UP => "insert into migrations (migration, batch) values ('{$statement}', '{$batch}')",
                Direction::DOWN => "delete from migrations where migration = '{$statement}' and batch = '{$batch}'"
            });
            $db->commit();
        }
        catch (PDOException $e) {
            $db->rollBack();
            $verb = match($direction) {
                Direction::DOWN => "roll back",
                Direction::UP => "execute",
            };
            throw MigrationException::from($e, "Failed to {$verb} migration: {$e->getMessage()}");
        }

    }
}