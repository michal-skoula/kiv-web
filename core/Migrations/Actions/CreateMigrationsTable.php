<?php

namespace Core\Migrations\Actions;

use PDOException;
use Core\Exceptions\MigrationException;
use Core\Facades\App;

final class CreateMigrationsTable
{
    const string TABLE_SCHEMA = <<<SQL
    create table migrations (
        id integer primary key autoincrement,
        migration varchar(255) not null, 
        batch integer not null,
        ran_at timestamp not null default current_timestamp
    );
    SQL;

    /**
     * @throws MigrationException
     */
    public static function handle(): void
    {
        try {
            $statement = App::db()->prepare(self::TABLE_SCHEMA);
            $statement->execute();
        } catch (PDOException $e) {
            App::log()->error("Failed to create migrations table: {$e->getMessage()}");
            throw MigrationException::from($e);
        }
    }
}