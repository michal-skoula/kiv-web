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
     * @throws MigrationException
     */
    private static function getMigrationSql(string $migration, Direction $direction): string
    {
        $file = App::paths()->getMigrationFile($migration);
        if(! file_exists($file)) {
            throw new MigrationException("File `$migration` not found.");
        }

        [$start, $stop] = match($direction) {
            Direction::UP => [self::UP_START_MARKER, self::UP_STOP_MARKER],
            Direction::DOWN => [self::DOWN_START_MARKER, self::DOWN_END_MARKER],
        };

        $full = file_get_contents($file);
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
        $dir = App::paths()->getMigrationsDir();
        if(! file_exists($dir)) {
            throw new MigrationException("Migrations folder `$dir` not found.");
        }
        // works like `ls`, so it even includes the `.` and `..` from `ls -la`
        $files = scandir($dir);

        // Filter out .gitkeep, ., .. and other random junk
        return array_filter($files, fn($file) => str_ends_with($file, '.sql'));
    }
}