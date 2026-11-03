<?php

namespace Core\Routing\Objects;

use Core\Exceptions\ValueException;
use Core\Routing\Enums\SegmentDataType;

final readonly class RouteParameter
{
    public function __construct(
        public mixed $value,
        public SegmentDataType $type
    ) {}

    /**
     * Get the value as a type-safe string.
     *
     * @throws ValueException Invalid type
     */
    public function asString(): string
    {
        if($this->type !== SegmentDataType::STRING) {
            throw new ValueException("Type has to be STRING to use this method");
        }

        $type = gettype($this->value);
        if ($type === 'string') {
            return (string)$this->value;
        }

        throw new ValueException("Expected value string, got: {$type} instead");
    }

    /**
     * Get the value as a type-safe integer.
     *
     * @throws ValueException Invalid type
     */
    public function asInt(): int
    {
        if($this->type !== SegmentDataType::INTEGER) {
            throw new ValueException("Type has to be INTEGER to use this method");
        }

        $type = gettype($this->value);
        if($type === 'integer' || is_numeric($this->value)) {
            return (int)$this->value;
        }

        throw new ValueException("Expected value integer, got: {$type} instead");
    }
}