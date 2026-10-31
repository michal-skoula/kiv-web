<?php

namespace PidAnalyzer\Core\Console\Contracts;

use PidAnalyzer\Core\Console\Enums\Status;

interface CommandExecutor
{
    public static function execute(array &$argv): Status;
}