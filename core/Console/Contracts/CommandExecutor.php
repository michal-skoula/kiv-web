<?php

namespace Core\Console\Contracts;

use Core\Console\Enums\Status;

interface CommandExecutor
{
    public static function execute(array &$argv): Status;
}