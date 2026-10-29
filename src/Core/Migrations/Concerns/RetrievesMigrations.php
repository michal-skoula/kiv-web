<?php

namespace PidAnalyzer\Core\Migrations\Concerns;

use PDO;
use PidAnalyzer\Core\Exceptions\MigrationException;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Commands\CreateMigrationFiles;

trait RetrievesMigrations
{
    use ResolvesMigrationsPath;

    private static array $cacheExecuted;
    private static array $cacheAll;


    private static function getWaitingMigrations(): array
    {
        return array_diff(static::getAllMigrations(), static::getExecutedMigrations());
    }

    private static function getExecutedMigrations(): array
    {
        if(isset(self::$cacheExecuted)) {
            return self::$cacheExecuted;
        }

        $statement = App::db()->prepare('SELECT migration from migrations');
        $statement->execute();
        return self::$cacheExecuted = $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    private static function getAllMigrations(): array
    {
        if(isset(self::$cacheAll)) {
            return self::$cacheAll;
        }
        $migrations = scandir(self::pathToMigrations());

        if($migrations === false) {
            throw new MigrationException("Unable to read migrations directory. Check permissions.");
        }

        return self::$cacheAll = array_filter($migrations, fn($m)
            => str_contains($m, CreateMigrationFiles::UP_ICON)
        );
    }
}