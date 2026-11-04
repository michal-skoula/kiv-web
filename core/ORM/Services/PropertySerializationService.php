<?php

namespace Core\ORM\Services;

use BackedEnum;
use Core\Exceptions\ORMMappingException;
use Core\ORM\Contracts\ModelPropertySerializable;
use Core\ORM\Contracts\Serializer;
use Core\ORM\Services\Serializers\ArrayStringSerializer;
use Core\ORM\Services\Serializers\BackedEnumSerializer;
use Core\ORM\Services\Serializers\CustomClassSerializer;
use Core\ORM\Services\Serializers\DateTimeSerializer;
use DateTimeImmutable;
use ReflectionIntersectionType as IntersectionType;
use ReflectionNamedType as NamedType;
use ReflectionUnionType as UnionType;

final class PropertySerializationService
{
    /**
     * Checked in order, so a class implementing ModelPropertySerializable
     * takes priority over the defaults for its parent type.
     *
     * @var array<string, class-string<Serializer>>
     */
    private static array $serializers = [
        ModelPropertySerializable::class => CustomClassSerializer::class,
        DateTimeImmutable::class => DateTimeSerializer::class,
        BackedEnum::class => BackedEnumSerializer::class,
        'array' => ArrayStringSerializer::class,
    ];

    /**
     * Converts a property value into its column value.
     *
     * Example: `['foo' => 'bar']` -> `{"foo": "bar"}`
     *
     * @throws ORMMappingException
     */
    public static function serialize(IntersectionType|NamedType|UnionType $propertyType, mixed $value): mixed
    {
        $type = self::handleUnsupportedTypes($propertyType);

        if(self::passesThrough($type, $value)) {
            return $value;
        }

        return self::findSerializer($type)::serialize($value);
    }

    /**
     * Converts a column value into its property value.
     *
     * Example: `{"foo": "bar"}` -> `['foo' => 'bar']`
     *
     * @throws ORMMappingException
     */
    public static function deserialize(IntersectionType|NamedType|UnionType $propertyType, mixed $value): mixed
    {
        $type = self::handleUnsupportedTypes($propertyType);

        if(self::passesThrough($type, $value)) {
            return $value;
        }

        return self::findSerializer($type)::deserialize($value, $type->getName());
    }

    /**
     * Whether the value needs no conversion: it's a null on a nullable type,
     * or a built-in type apart from arrays, which PHP's casting will handle.
     *
     * @throws ORMMappingException
     */
    private static function passesThrough(NamedType $type, mixed $value): bool
    {
        if($value === null && ! $type->allowsNull()) {
            throw ORMMappingException::typeNotSupported($type, 'It does not allow null values.');
        }

        return $value === null || ($type->isBuiltin() && $type->getName() !== 'array');
    }

    /**
     * Enums and subclasses are declared by their own name, so
     * `is_a()` matches them to their parent's entry in the map.
     *
     * @return class-string<Serializer>
     *
     * @throws ORMMappingException
     */
    private static function findSerializer(NamedType $type): string
    {
        foreach(self::$serializers as $castedType => $serializer) {
            if($type->getName() === $castedType || is_a($type->getName(), $castedType, allow_string: true)) {
                return $serializer;
            }
        }

        throw ORMMappingException::typeNotSupported($type, 'It must implement ModelPropertySerializable.');
    }

    /**
     * Normalizes to NamedType or throws for unsupported types.
     *
     * @param IntersectionType|NamedType|UnionType $type
     *
     * @return NamedType
     *
     * @throws ORMMappingException
     */
    private static function handleUnsupportedTypes(IntersectionType|NamedType|UnionType $type): NamedType
    {
        // Union types are not supported, except for nullable, where `Type|null`
        // gets turned into to `?Type`, which is a NamedType and not a UnionType.
        if($type instanceof UnionType) {
            throw ORMMappingException::unionTypeNotSupported();
        }

        // Intersection types `Type1&Type2` are not supported, as there
        // would be no method of actually serializing them.
        else if($type instanceof IntersectionType) {
            throw ORMMappingException::intersectionTypeNotSupported();
        }

        return $type;
    }
}
