<?php

namespace Core\Routing\Enums;

use Core\Routing\BoundBy;
use Core\Routing\Services\BindingResolvers\ModelArrayResolver;
use Core\Routing\Services\BindingResolvers\ModelResolver;

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
     * Maps to an instance retrieved from the database.
     */
    #[BoundBy(ModelResolver::class)]
    case MODEL = 'model';

    /**
     * Maps to an array<Model> retrieved from the database.
     */
    #[BoundBy(ModelArrayResolver::class)]
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
            self::MODEL,
            self::INT => is_numeric($value),
            self::STRING_ARRAY =>
                array_all(explode(',', $value), fn($v) => is_string($v)),
            self::INT_ARRAY,
            self::MODEL_ARRAY =>
                array_all(explode(',', $value), fn($v) => is_numeric($v)),
        };
    }

    /**
     * Casts the value of the route param to what it should be.
     *
     * @param list<int>|list<string>|int|string|null $value
     * @return string|int|array|null
     */
    public function castToSame(mixed $value): mixed
    {
        return match ($this) {
            self::NONE => null,
            self::STRING => (string)$value,
            self::MODEL,
            self::INT => (int)$value,
            self::STRING_ARRAY =>
                array_map(fn($v) => (string)$v, explode(',', $value)),
            self::MODEL_ARRAY,
            self::INT_ARRAY =>
                array_map(fn($v) => (int)$v, explode(',', $value)),
        };
    }

    public function isWildcard(): bool
    {
        return $this !== self::NONE;
    }
}
