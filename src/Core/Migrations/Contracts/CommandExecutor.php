<?php

namespace PidAnalyzer\Core\Migrations\Contracts;

use PidAnalyzer\Core\Cli\Status;

interface CommandExecutor
{
    public static function execute(array $argv): Status;
}