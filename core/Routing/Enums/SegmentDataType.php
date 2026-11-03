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
     * Maps to an array<string> data type.
     */
    case STRING_ARRAY = 'string_array';

    /**
     * Maps to an integer data type.
     */
    case INT = 'integer';

    /**
     * Maps to an array<int> data type.
     */
    case INT_ARRAY = 'int_array';

    /**
     * Maps to an array<Model> retrieved from the database.
     */
    case MODEL = 'model';

    /**
     * Maps to a instance retrieved from the database.
     */
    case MODEL_ARRAY = 'model_array';

    public function identifier(): ?string
    {
        return match ($this) {
            self::NONE => null,
            self::STRING => '%s',
            self::STRING_ARRAY => '%s[]',
            self::INT => '%i',
            self::INT_ARRAY => '%i[]',
            self::MODEL => '%m',
            self::MODEL_ARRAY => '%m[]',
        };
    }

    /**
     * Whether a given value matches the expected type.
     *
     * @param mixed $value
     * @return bool
     */
    public function matches(mixed $value): bool
    {
        return match ($this) {
            self::NONE => true,
            self::STRING => is_string($value),
            self::INT => is_numeric($value),
            self::STRING_ARRAY =>
                array_all(explode(',', $value), fn($v) => is_string($v)),
            self::INT_ARRAY =>
                array_all(explode(',', $value), fn($v) => is_numeric($v)),
            self::MODEL,
            self::MODEL_ARRAY => throw new NotImplementedException()
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
            self::INT => (int)$value,
            self::STRING_ARRAY =>
                array_map(fn($v) => (string)$v, explode(',', $value)),
            self::INT_ARRAY =>
                array_map(fn($v) => (int)$v, explode(',', $value)),
            self::MODEL,
            self::MODEL_ARRAY => throw new NotImplementedException()
        };
    }

    public function isWildcard(): bool
    {
        return $this !== self::NONE;
    }
}
