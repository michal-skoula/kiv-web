<?php

namespace PidAnalyzer\Core\Console\Actions;

use PidAnalyzer\Core\Console\Attributes\PreventInProduction;
use PidAnalyzer\Core\Console\Concerns\InteractsWithCliArguments;
use PidAnalyzer\Core\Console\Enums\ArgType;
use PidAnalyzer\Core\Console\Enums\Status;
use PidAnalyzer\Core\Facades\App;
use PidAnalyzer\Core\Migrations\Contracts\CommandExecutor;
use ReflectionClass;
use ReflectionException;

class PreventCommandIfItCantRun
{
    use InteractsWithCliArguments;

    /**
     * Checks whether the command can run based on if the environment is production
     * and whether it was run forcibly with `--force`, such as in the event of
     * running migrations when deploying a new release of the application.
     *
     * @param array<string, mixed> $argv Pass as-is.
     * @param class-string<CommandExecutor> $command The command class-string.
     *
     * @throws ReflectionException
     */
    public static function handle(string $command, array $argv): void
    {
        $env = App::instance()->env;
        if($env !== 'production') {
            return;
        }

        $reflection = new ReflectionClass($command);
        $isPreventingInProd = $reflection->getAttributes(PreventInProduction::class)[0] ?? null;
        if(! $isPreventingInProd) {
            return;
        }

        /** @var bool $canBeForced */
        $canBeForced = $isPreventingInProd->getArguments()['canBeForced'] ?? null;

        if($canBeForced === true) {
            $usingForceFlag = self::getArg($argv, 'force', ArgType::BOOLEAN);
            if($usingForceFlag === true) {
                return;
            }
        }

        Status::FAILURE->exit("ERROR: Command not allowed in production.");
    }
}