<?php

namespace PidAnalyzer\Core\Exceptions;

use Exception;
use Throwable;

class ApplicationException extends Exception
{
    public static function from(Throwable $e, ?string $message = null): static
    {
        $code = $e->getCode();

        return new static(
            message: $message ?? $e->getMessage(),
            code: is_numeric($code) ? $e->getCode() : 0,
            previous: $e
        );
    }
}