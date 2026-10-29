<?php

namespace PidAnalyzer\Core\Migrations\Concerns;

use PidAnalyzer\Core\Facades\App;

trait ChecksMigrationTableExists
{
    public static function migrationTableExists(): bool
    {
        $statement = App::db()->prepare("select name from sqlite_schema where type = 'table' and name = 'migrations'");
        $statement->execute();

        return count($statement->fetchAll()) === 1;
    }
}