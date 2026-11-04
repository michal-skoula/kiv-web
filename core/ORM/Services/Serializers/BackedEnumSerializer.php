<?php

namespace Core\ORM\Services\Serializers;

use BackedEnum;
use Core\ORM\Contracts\BuiltInSerializer;

class BackedEnumSerializer implements BuiltInSerializer
{
    /**
     * @param BackedEnum $value
     */
    public static function serialize(mixed $value): int|string
    {
        return $value->value;
    }

    /**
     * @param class-string<BackedEnum> $type
     */
    public static function deserialize(int|string $value, string $type): BackedEnum
    {
        return $type::from($value);
    }
}
