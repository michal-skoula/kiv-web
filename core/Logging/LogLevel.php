<?php

namespace Core\Logging;

enum LogLevel: string
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';

    /**
     * Get the numeric representation of the level, for easy LOG_LEVEL comparisons.
     */
    public function numeric(): int
    {
        return match($this) {
            self::DEBUG => 1,
            self::INFO => 2,
            self::WARNING => 3,
            self::ERROR => 4,
            self::CRITICAL => 5,
        };
    }

    public function label(): string
    {
        return match ($this) {
            LogLevel::DEBUG => 'DEBUG',
            LogLevel::INFO => 'INFO',
            LogLevel::WARNING => 'WARNING',
            LogLevel::ERROR => 'ERROR',
            LogLevel::CRITICAL => 'CRITICAL',
        };
    }
}
