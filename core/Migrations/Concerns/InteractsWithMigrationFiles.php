<?php

namespace Core\Migrations\Concerns;

use Core\Exceptions\MigrationException;
use Core\Facades\App;
use Core\Helpers\Facades\Str;
use Core\Migrations\Enums\Direction;

trait InteractsWithMigrationFiles
{
    public const string UP_START_MARKER = '-- START UP MIGRATION BLOCK --';
    public const string UP_STOP_MARKER = '-- END UP MIGRATION BLOCK --';
    public const string DOWN_START_MARKER = '-- START DOWN MIGRATION BLOCK --';
    public const string DOWN_END_MARKER = '-- END DOWN MIGRATION BLOCK --';

    /**
     * Full path to the migrations directory **with a trailing slash**.
     * @throws MigrationException
     */
    private static function pathToMigrations(): string
    {
        return App::paths()->migrationsDir()
            ?? throw new MigrationException("Migrations folder path not set or not found.");
    }

    /**
     * @throws MigrationException
     */
    private static function getMigrationSql(string $migration, Direction $direction): string
    {
        $file = self::pathToMigrations() . "/$migration";
        $full = file_get_contents($file);

        [$start, $stop] = match($direction) {
            Direction::UP => [self::UP_START_MARKER, self::UP_STOP_MARKER],
            Direction::DOWN => [self::DOWN_START_MARKER, self::DOWN_END_MARKER],
        };

        $sql = Str::between($full, $start, $stop) ?? '';
        return trim($sql);
    }

    /**
     * Reads all migrations from the migrations directory.
     *
     * @return array array of file names.
     *
     * @throws MigrationException
     */
    private static function getAllMigrationFiles(): array
    {
        // works like `ls`, so it even includes the `.` and `..` from `ls -la`
        $files = scandir(self::pathToMigrations());
        if($files === false) {
            throw new MigrationException("Unable to read migrations directory. Check permissions?");
        }

        // Filter out .gitkeep and other random junk
        return array_filter($files, fn($file) => str_ends_with($file, '.sql'));
    }
}