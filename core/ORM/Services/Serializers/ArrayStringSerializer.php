<?php

namespace Core\ORM\Services\Serializers;

use Core\ORM\Contracts\BuiltInSerializer;
use JsonException;

class ArrayStringSerializer implements BuiltInSerializer
{
    /**
     * @param array $value
     */
    public static function serialize(mixed $value): string
    {
        return json_encode($value) ?: '{}';
    }

    /**
     * @throws JsonException
     */
    public static function deserialize(int|string $value, string $type): mixed
    {
        return json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);
    }
}
