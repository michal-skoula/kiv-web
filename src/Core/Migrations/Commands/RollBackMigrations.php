<?php

namespace PidAnalyzer\Core\Migrations\Commands;

use PidAnalyzer\Core\Cli\CliArgType;
use PidAnalyzer\Core\Cli\Status;
use PidAnalyzer\Core\Migrations\Concerns\InteractsWithCliArguments;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;

final class RollBackMigrations implements CommandExecutor
{
    use InteractsWithCliArguments;

    public static function execute(array $argv): Status
    {
        $steps = self::getArg($argv, 'steps', CliArgType::KEY_VALUE) ?: 1;


    }

    private function getSteps()
}