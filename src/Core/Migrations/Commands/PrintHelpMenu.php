<?php

namespace PidAnalyzer\Core\Migrations\Commands;

use PidAnalyzer\Core\Cli\Status;
use PidAnalyzer\Core\Migrations\Command;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;
use PidAnalyzer\Core\Migrations\MigratorData;

final class PrintHelpMenu implements CommandExecutor
{
    private const string GAP = ' ';

    /**
     * @var string Standardized "See help" message.
     */
    public const string HELP_MSG = 'See `' . MigratorData::SCRIPT_NAME . ' help` for more information.';

    public static function execute(array $argv): Status
    {
        $commands = Command::cases();
        $maxWidth = self::longestCharCount();

        echo 'Usage: php ' . MigratorData::SCRIPT_NAME . ' <mode> --key=value or --key' . PHP_EOL;
        echo 'Available commands:' . PHP_EOL;
        foreach ($commands as $command)
        {
            $width = self::charCount($command);
            $gapWidth = $maxWidth - $width;
            echo
                "  - {$command->value}  {$command->parameters()} " . // Left side
                str_repeat(self::GAP, times: $gapWidth) . // Gap
                " {$command->description()} \n";  // Right side
        }

        return Status::SUCCESS;
    }

    private static function longestCharCount(): int
    {
        $commands = Command::cases();
        $longest = 0;

        foreach($commands as $command)
        {
            $length = self::charCount($command);
            if($length > $longest) $longest = $length;
        }

        return $longest;
    }

    private static function charCount(Command $command): int
    {
        return strlen($command->value) + strlen($command->parameters());
    }
}