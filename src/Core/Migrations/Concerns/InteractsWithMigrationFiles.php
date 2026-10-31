<?php

namespace PidAnalyzer\Core\Migrations\Concerns;

use PidAnalyzer\Core\Exceptions\MigrationException;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Enums\Direction;

trait InteractsWithMigrationFiles
{
    public const string UP_START_MARKER = '-- START UP MIGRATION BLOCK --';
    public const string UP_STOP_MARKER = '-- END UP MIGRATION BLOCK --';
    public const string DOWN_START_MARKER = '-- START DOWN MIGRATION BLOCK --';
    public const string DOWN_END_MARKER = '-- END DOWN MIGRATION BLOCK --';

    /**
     * @var string  Path from app root to the migrations directory.
     */
    public const string MIGRATIONS_PATH = '/migrations';

    /**
     * Full path to the migrations directory **with a trailing slash**.
     */
    private static function pathToMigrations(): string
    {
        $normalizedPath =  '/' . trim(self::MIGRATIONS_PATH, '/') . '/';
        return App::instance()->rootDir . $normalizedPath;
    }


    private static function getMigrationSql(string $migration, Direction $direction): string
    {
        $file = self::pathToMigrations() . $migration;
        $full = file_get_contents($file);

        [$start, $stop] = match($direction) {
            Direction::UP => [self::UP_START_MARKER, self::UP_STOP_MARKER],
            Direction::DOWN => [self::DOWN_START_MARKER, self::DOWN_END_MARKER],
        };

        $sql = self::getStringBetween($full, $start, $stop);
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

    /**
     * Uses RegEx to find a string between two other strings.
     */
    private static function getStringBetween(string $full, string $start, string $end): string
    {
        // `preg_quote()` is for escaping the special characters like `--`.
        // `(.*?)` is match everything
        // `/s` is to handle newlines properly
        $pattern = '/' . preg_quote($start, '/') . '(.*?)' . preg_quote($end, '/') . '/s';

        if (preg_match($pattern, $full, $match) == 1) {
            return $match[1];
        }

        return '';
    }
}