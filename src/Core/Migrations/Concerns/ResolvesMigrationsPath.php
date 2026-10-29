<?php

namespace PidAnalyzer\Core\Migrations\Concerns;

use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\MigratorData;

trait ResolvesMigrationsPath
{
    /**
     * Full path to the migrations directory.
     */
    public static function pathToMigrations(): string
    {
        $normalizedPath =  '/' . trim(MigratorData::MIGRATIONS_PATH, '/') . '/';
        return App::instance()->rootDir . $normalizedPath;
    }
}