<?php

namespace PidAnalyzer\Core\Migrations\Commands;

use DateTimeImmutable;
use PidAnalyzer\Core\Cli\CliArgType;
use PidAnalyzer\Core\Cli\Status;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Concerns\InteractsWithCliArguments;
use PidAnalyzer\Core\Migrations\Concerns\RequiresForceToRunInProd;
use PidAnalyzer\Core\Migrations\Concerns\ResolvesMigrationsPath;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;
use PidAnalyzer\Core\Migrations\Contracts\PreventInProduction;

#[PreventInProduction(canBeForced: false)]
final class CreateMigrationFiles implements CommandExecutor
{
    use ResolvesMigrationsPath;
    use RequiresForceToRunInProd;
    use InteractsWithCliArguments;

    private const string TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s';

    public const string UP_ICON = '↗';

    public const string DOWN_ICON = '↘';

    public static function execute(array $argv): Status
    {
        $timestamp = new DateTimeImmutable()->format('Y-m-d\TH:i:s');
        $name = self::getArg($argv, 'name', CliArgType::KEY_VALUE);
        if(! $name) {
            Status::ARG_ERROR->exit("ERROR: Missing required property `name`.");
        }

        // Note: Unicode and spaces in filenames, what could go wrong? It looks cool though :D
        $up = $timestamp.' '.self::UP_ICON.' '."{$name}.sql";
        $down = $timestamp.' '.self::DOWN_ICON.' '."{$name}.sql";
        $upPath = self::pathToMigrations() . $up;
        $downPath = self::pathToMigrations() . $down;

        if(file_exists($up)) {
            Status::FAILURE->exit("ERROR: {$up} already exists.");
        }

        if(file_exists($down)) {
            Status::FAILURE->exit("ERROR: {$down} already exists.");
        }

        file_put_contents($upPath, "-- Migration: {$up} \n");
        file_put_contents($downPath, "-- Migration: {$down} \n");

        if(! file_exists($upPath) || ! file_exists($downPath)) {
            // This will warn on missing files, expected.
            unlink($upPath);
            unlink($downPath);
            Status::FAILURE->exit("ERROR: Failed to create migration files.");
        }

        App::log()->info('Created the following migration files:', [$up, $down]);

        return Status::SUCCESS;
    }
}