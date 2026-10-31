<?php

namespace Core\Logging;

readonly class Logger
{
    public function __construct(
        private LogLevel $minLogLevel
    ) {}

    function log(string $message, LogLevel $level, mixed $data = null): void
    {
        if($level->numeric() < $this->minLogLevel->numeric()) {
            return;
        }

        if($data === null) {
            error_log("[{$level->label()}] $message");
            return;
        }

        $dataMsg = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
        error_log("[{$level->label()}] $message $dataMsg");
    }

    function debug(string $message, mixed $data = null): void
    {
        $this->log($message, LogLevel::DEBUG, $data);
    }

    function info(string $message, mixed $data = null): void
    {
        $this->log($message, LogLevel::INFO, $data);
    }

    function warning(string $message, mixed $data = null): void
    {
        $this->log($message, LogLevel::WARNING, $data);
    }

    function error(string $message, mixed $data = null): void
    {
        $this->log($message, LogLevel::ERROR, $data);
    }

    function critical(string $message, mixed $data = null): void
    {
        $this->log($message, LogLevel::CRITICAL, $data);
    }
}