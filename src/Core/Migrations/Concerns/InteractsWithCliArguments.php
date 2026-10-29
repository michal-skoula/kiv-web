<?php

namespace PidAnalyzer\Core\Migrations\Concerns;

use PidAnalyzer\Core\Cli\CliArgType;
use PidAnalyzer\Core\Cli\Status;
use PidAnalyzer\Core\Facades\App;

trait InteractsWithCliArguments
{
    private const string ARGUMENT_SEPARATOR = '=';

    /**
     * Helper for reading CLI arguments from `argv`.
     *
     * @param array $argv as-is argv array
     * @param string $key The --key to look for.
     * @param CliArgType $type How to treat the value, either as a `foo=bar` association or as a `--foo` boolean.
     * @return string|bool|null returns `string|null` for KEY_VALUE, `bool` for BOOLEAN
     */
    public static function getArg(array $argv, string $key, CliArgType $type): string|bool|null
    {
        // arguments start from the 3rd item.
        foreach(array_slice($argv, offset: 2) as $arg)
        {
            // normalized to only the `key` or `key=value` pair
            $argument = ltrim($arg, '-');

            if(! str_starts_with(haystack: $argument, needle: $key)) {
                continue;
            }

            switch($type) {
                case CliArgType::KEY_VALUE:
                    $isValidShape = str_contains($argument, self::ARGUMENT_SEPARATOR);
                    if(! $isValidShape) return null;
                    [$_, $value] = explode(self::ARGUMENT_SEPARATOR, $argument, 2);
                    return $value ?: null;
                case CliArgType::BOOLEAN:
                    return true;
            }
        }

        return match($type) {
            CliArgType::KEY_VALUE => null,
            CliArgType::BOOLEAN => false,
        };
    }
}