<?php

namespace Core\Logging;

class Logger
{
    public function __construct(
        private LogLevel $minLogLevel
    ) {}

    function log(string $message, LogLevel $level = LogLevel::DEBUG,  array $data = []): void
    {
        if($level->numeric() < $this->minLogLevel->numeric()) {
            return;
        }

        $dataMsg = $data !== []
            ? json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)
            : '';

        error_log("[{$level->label()}] $message $dataMsg");
    }

    function debug(string $message, array $data = []): void
    {
        $this->log($message, LogLevel::DEBUG, $data);
    }

    function info(string $message, array $data = []): void
    {
        $this->log($message, LogLevel::INFO, $data);
    }

    function warning(string $message, array $data = []): void
    {
        $this->log($message, LogLevel::WARNING, $data);
    }

    function error(string $message, array $data = []): void
    {
        $this->log($message, LogLevel::ERROR, $data);
    }

    function critical(string $message, array $data = []): void
    {
        $this->log($message, LogLevel::CRITICAL, $data);
    }
}