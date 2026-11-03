<?php

namespace Core\Routing\Enums;

enum SegmentDataType: string
{
    /**
     * For PathSegment::CONSTANT, as they have no value
     */
    case NONE = 'none';

    /**
     * Maps to a string data type.
     */
    case STRING = 'string';

    /**
     * Maps to an integer data type.
     */
    case INTEGER = 'integer';

    public function identifier(): ?string
    {
        return match ($this) {
            self::NONE => null,
            self::STRING => '%s',
            self::INTEGER => '%i',
        };
    }

    /**
     * Whether a given value matches the expected type.
     *
     * @param mixed $value
     * @return bool
     */
    public function typeMatches(mixed $value): bool
    {
        return match ($this) {
            self::NONE => true,
            self::STRING => is_string($value),
            self::INTEGER => is_numeric($value)
        };
    }

    public function castToSameType(mixed $value): string|int|null
    {
        return match ($this) {
            self::NONE => null,
            self::STRING => (string)$value,
            self::INTEGER => (int)$value,
        };
    }
}
