<?php

namespace Core\ORM\Contracts;

interface BuiltInSerializer
{
    /**
     * Converts a property value into its column value.
     */
    public static function serialize(mixed $value): int|string;

    /**
     * Converts a column value into its property value.
     *
     * @param string $type the property's type name, as returned by `ReflectionNamedType::getName()`
     */
    public static function deserialize(int|string $value, string $type): mixed;
}
