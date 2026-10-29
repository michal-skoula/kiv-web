<?php

namespace PidAnalyzer\Core\Cli;

use PidAnalyzer\Core\Facades\App;

final class CliArgReader
{
    /**
     * @var array<string, string|bool|null>
     */
    static ?array $argv = null;

    public static function loadArgv(array $argv): void
    {
        self::$argv = $argv;
    }

    /**
     * Helper method for reading CLI arguments from `argv`.
     *
     * @param string $key The --key to look for.
     * @param CliArgType $type How to treat the value, either as a `foo=bar` association or as a `--baz` boolean.
     * @return array{bool, string|bool|null} `[exists, value]` tuple
     */
    public static function read(string $key, CliArgType $type = CliArgType::KEY_VALUE): array
    {
        self::handleMissingArgv();

        // arguments start from the 3rd item.
        foreach(array_slice(self::$argv, offset: 2) as $arg)
        {
            $argument = ltrim($arg, '-');

            if(! str_starts_with(haystack: $argument, needle: $key)) {
                continue;
            }

            // Good luck reading this in a year future me
            return match($type) {
                CliArgType::KEY_VALUE => str_contains($argument, '=')
                    ? [true, explode('=', $argument)[1]]
                    : [true, null], // shape `foo=` (empty)
                CliArgType::BOOLEAN => [true, true],
            };
        }

        return [false, null];
    }

    private static function handleMissingArgv(): void
    {
        if(self::$argv) {
            return;
        }

        App::log()->error("CLI Arguments not loaded. Run CliArgReader::loadArgv() first.");
        Status::FAILURE->exitWithoutMessage();
    }

}