<?php

namespace Core\ORM\Services\Serializers;

use BackedEnum;
use Core\ORM\Contracts\Serializer;

class BackedEnumSerializer implements Serializer
{
    /**
     * @param BackedEnum $value
     */
    public static function serialize(mixed $value): int|string
    {
        return $value->value;
    }

    /**
     * @param class-string<BackedEnum> $class
     */
    public static function deserialize(int|string $value, string $class): BackedEnum
    {
        return $class::from($value);
    }
}
