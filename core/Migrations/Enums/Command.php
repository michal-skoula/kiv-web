<?php

namespace Core\Migrations\Enums;

use Core\Console\Concerns\InteractsWithCliArguments;
use Core\Console\Contracts\CommandExecutor;
use Core\Migrations\Commands\MakeMigrationFile;
use Core\Migrations\Commands\PrintHelpMenu;
use Core\Migrations\Commands\PrintMigrationStatus;
use Core\Migrations\Commands\RerunMigrationsFresh;
use Core\Migrations\Commands\RollBackMigrations;
use Core\Migrations\Commands\RunMigrations;

/**
 * Registry for all Migrator tools, their handlers and metadata.
 */
enum Command: string
{
    use InteractsWithCliArguments;

    case HELP = 'help';
    case STATUS = 'status';
    case MAKE = 'make';
    case MIGRATE = 'migrate';
    case ROLLBACK = 'rollback';
    case FRESH = 'fresh';

    /**
     * @return class-string<CommandExecutor>
     */
    public function handler(): string
    {
        return match ($this) {
            self::HELP      => PrintHelpMenu::class,
            self::STATUS    => PrintMigrationStatus::class,
            self::MAKE      => MakeMigrationFile::class,
            self::MIGRATE   => RunMigrations::class,
            self::ROLLBACK  => RollBackMigrations::class,
            self::FRESH     => RerunMigrationsFresh::class,
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
