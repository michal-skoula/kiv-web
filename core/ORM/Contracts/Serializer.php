<?php

namespace Core\ORM\Contracts;

interface Serializer
{
    /**
     * Converts a property value into its column value.
     */
    public static function serialize(mixed $value): int|string;

    /**
     * Converts a column value into its property value.
     *
     * @param mixed $value
     * @param string $class the property's type name, as returned by `ReflectionNamedType::getName()`
     */
    public static function deserialize(int|string $value, string $class): mixed;
}
