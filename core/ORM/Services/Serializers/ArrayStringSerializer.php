<?php

namespace Core\ORM\Services\Serializers;

use Core\Exceptions\ORMMappingException;
use Core\ORM\Contracts\Serializer;
use JsonException;

class ArrayStringSerializer implements Serializer
{
    /**
     * @param array $value
     */
    public static function serialize(mixed $value): string
    {
        return json_encode($value) ?: '{}';
    }

    /**
     * @throws ORMMappingException
     */
    public static function deserialize(int|string $value, string $class): mixed
    {
        try {
            return json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch(JsonException $e) {
            throw ORMMappingException::from($e, "Cannot decode column value as JSON: {$e->getMessage()}");
        }
    }
}
