<?php

namespace Core\Routing\Enums;

use Core\Exceptions\NotImplementedException;

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

    case STRING_ARRAY = 'string_array';

    case INT_ARRAY = 'int_array';

    case MODEL = 'model';

    public function identifier(): ?string
    {
        return match ($this) {
            self::NONE => null,
            self::STRING => '%s',
            self::INTEGER => '%i',
            self::STRING_ARRAY => '%[s]',
            self::INT_ARRAY => '%[i]',
            self::MODEL => '%m',
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
            self::INTEGER => is_numeric($value),
            self::STRING_ARRAY =>
                array_all(explode(',', $value), fn($v) => is_string($v)),
            self::INT_ARRAY =>
                array_all(explode(',', $value), fn($v) => is_numeric($v)),
            self::MODEL => throw new NotImplementedException()
        };
    }

    /**
     * Casts the value of the route param to what it should be.
     *
     * @param list<int>|list<string>|int|string|null $value
     * @return string|int|array|null
     */
    public function castToSameType(mixed $value): mixed
    {
        return match ($this) {
            self::NONE => null,
            self::STRING => (string)$value,
            self::INTEGER => (int)$value,
            self::STRING_ARRAY =>
                array_map(fn($v) => (string)$v, explode(',', $value)),
            self::INT_ARRAY =>
                array_map(fn($v) => (int)$v, explode(',', $value)),
            self::MODEL => throw new NotImplementedException()
        };
    }

    public function isWildcard(): bool
    {
        return $this !== self::NONE;
    }
}
