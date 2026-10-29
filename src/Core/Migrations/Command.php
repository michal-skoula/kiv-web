<?php

namespace PidAnalyzer\Core\Migrations;

use PidAnalyzer\Core\Exceptions\NotImplementedException;
use PidAnalyzer\Core\Migrations\Commands\CreateMigrationFiles;
use PidAnalyzer\Core\Migrations\Commands\PrintHelpMenu;
use PidAnalyzer\Core\Migrations\Commands\PrintMigrationStatus;
use PidAnalyzer\Core\Migrations\Commands\RollBackMigrations;
use PidAnalyzer\Core\Migrations\Commands\RunMigrations;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;

/**
 * Registry for all Migrator tools, their handlers and metadata.
 */
enum Command: string
{
    case HELP = 'help';
    case STATUS = 'status';
    case MAKE = 'make';
    case MIGRATE = 'migrate';
    case ROLLBACK = 'rollback';
    case FRESH = 'fresh';

    /**
     * @return class-string<CommandExecutor>
     * @throws NotImplementedException
     */
    public function handler(): string
    {
        return match ($this) {
            self::HELP      => PrintHelpMenu::class,
            self::STATUS    => PrintMigrationStatus::class,
            self::MAKE      => CreateMigrationFiles::class,
            self::MIGRATE   => RunMigrations::class,
            self::ROLLBACK  => RollBackMigrations::class,
            self::FRESH     => throw new NotImplementedException(),
        };
    }

    public function parameters(): string
    {
        return match($this) {
            self::HELP      => '',
            self::STATUS    => '',
            self::MAKE      => '<name:string>',
            self::MIGRATE   => '<force:bool>',
            self::ROLLBACK  => '<steps:int> <force:bool>',
            self::FRESH     => '<force:bool>',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::HELP      => 'Shows this page.',
            self::STATUS    => 'Check which migrations have and haven\'t run.',
            self::MAKE      => 'Creates the migration .sql files.',
            self::MIGRATE   => 'Runs migrations which are yet to run.',
            self::ROLLBACK  => 'Rollbacks the migrations N steps.',
            self::FRESH     => 'Re-generates the database.',
        };
    }
}
