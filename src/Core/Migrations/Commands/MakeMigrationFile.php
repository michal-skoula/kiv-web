<?php

namespace PidAnalyzer\Core\Migrations\Commands;

use DateTimeImmutable;
use PidAnalyzer\Core\Console\Attributes\PreventInProduction;
use PidAnalyzer\Core\Console\Concerns\InteractsWithCliArguments;
use PidAnalyzer\Core\Console\Enums\ArgType;
use PidAnalyzer\Core\Console\Enums\Status;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Concerns\InteractsWithMigrationFiles;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;

#[PreventInProduction(canBeForced: false)]
final class MakeMigrationFile implements CommandExecutor
{
    use InteractsWithMigrationFiles;
    use InteractsWithCliArguments;

    private const string TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s';

    public static function execute(array &$argv): Status
    {
        $timestamp = new DateTimeImmutable()->format(self::TIMESTAMP_FORMAT);

        $name = self::getArg($argv, 'name', ArgType::KEY_VALUE);
        if(! $name) {
            Status::ARG_ERROR->exit("ERROR: Missing required property `name`.");
        }

        $migration = "$timestamp {$name}.sql";
        $migrationFile = self::pathToMigrations() . $migration;

        if(file_exists($migrationFile)) {
            Status::FAILURE->exit("ERROR: {$migration} already exists.");
        }

        file_put_contents($migrationFile, self::migrationFileBoilerplate($migration));

        if(! file_exists($migrationFile)) {
            Status::FAILURE->exit("ERROR: Failed to create migration files. Check permissions.");
        }

        App::log()->info("Created migration file: $migration");

        return Status::SUCCESS;
    }

    private static function migrationFileBoilerplate(string $migration): string
    {
        return sprintf(self::MIGRATION_STUB,
            $migration,
            self::UP_START_MARKER,
            self::UP_STOP_MARKER,
            self::DOWN_START_MARKER,
            self::DOWN_END_MARKER
        );
    }

    private const string MIGRATION_STUB = <<<STUB
        -- Migration name: %s
        
        %s
        
        
        
        %s
        
        %s
        
        
        
        %s
        STUB;
}