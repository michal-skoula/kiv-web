<?php

namespace PidAnalyzer\Core\Migrations\Concerns;

use PidAnalyzer\Core\Cli\CliArgReader;
use PidAnalyzer\Core\Cli\CliArgType;
use PidAnalyzer\Core\Cli\Status;
use PidAnalyzer\Core\Facades\App;


trait RequiresForceToRunInProd
{

    /**
     * Terminates the script in production environments.
     *
     * If the `--force` flag is passed, this check is bypassed.
     *
     * @param bool $forceCheckDisabled Prevent --force from being applied.
     */
    public static function preventInProd(bool $forceCheckDisabled = false): void
    {
        // Ignores the second value on purpose, as boolean flags don't pass a value.
        [$forced, $_] = CliArgReader::read('force', CliArgType::BOOLEAN);

        if (App::env() !== 'production') {
            return;
        }

        if(! $forceCheckDisabled && $forced) {
            return;
        }

        Status::FAILURE->exit("ERROR: Operation not allowed in production.");
    }
}