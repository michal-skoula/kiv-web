<?php

namespace Core\Console\Enums;

enum ArgType
{
    /**
     * Argument defined as a key = value pair, Ex. `--foo=bar`.
     */
    case KEY_VALUE;

    /**
     * Argument defined as just a key, without a value. Ex. `--foo`.
     */
    case BOOLEAN;
}
