<?php

namespace Core\Console\Enums;

/**
 * Exit codes returned by CLI tools.
 */
enum Status: int
{
    case SUCCESS = 0;
    case FAILURE = 1;
    case ARG_ERROR = 2;

    /**
     * Terminates the script with this status code.
     */
    public function exitWithoutMessage(): never
    {
        exit($this->value);
    }

    public function exit(string $msg): never
    {
        echo $msg . PHP_EOL;

        $this->exitWithoutMessage();
    }
}
