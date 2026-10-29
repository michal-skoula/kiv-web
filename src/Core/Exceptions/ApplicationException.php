<?php

namespace PidAnalyzer\Core\Exceptions;

use Exception;

class ApplicationException extends Exception
{
    public static function from(\Throwable $e, ?string $message = null): static
    {
        return new static($message ?? $e->getMessage(), $e->getCode(), $e);
    }
}