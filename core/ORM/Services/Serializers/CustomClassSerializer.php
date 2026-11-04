<?php

namespace Core\ORM\Services\Serializers;

use Core\ORM\Contracts\ModelPropertySerializable;
use Core\ORM\Contracts\Serializer;

class CustomClassSerializer implements Serializer
{
    /**
     * @param ModelPropertySerializable $value
     */
    public static function serialize(mixed $value): string
    {
        return $value->serializeProperty();
    }

    /**
     * @param class-string<ModelPropertySerializable> $class
     */
    public static function deserialize(int|string $value, string $class): ModelPropertySerializable
    {
        return $class::deserializeProperty($value);
    }
}
