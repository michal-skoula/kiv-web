<?php

namespace Core\ORM\Services;

use BackedEnum;
use Core\Exceptions\ORMMappingException;
use Core\ORM\Contracts\BuiltInSerializer;
use Core\ORM\Contracts\ModelPropertySerializable;
use Core\ORM\Services\Serializers\ArrayStringSerializer;
use Core\ORM\Services\Serializers\BackedEnumSerializer;
use Core\ORM\Services\Serializers\DateTimeSerializer;
use DateTimeImmutable;
use ReflectionIntersectionType as IntersectionType;
use ReflectionNamedType as NamedType;
use ReflectionUnionType as UnionType;

final class ModelPropertyCastingService
{
    /**
     * @var array<string, class-string<BuiltInSerializer>>
     */
    private static array $builtInCasts = [
        DateTimeImmutable::class => DateTimeSerializer::class,
        BackedEnum::class => BackedEnumSerializer::class,
        'array' => ArrayStringSerializer::class,
    ];

    /**
     * Converts a property value into its column value.
     *
     * @throws ORMMappingException
     */
    public static function serialize(IntersectionType|NamedType|UnionType $propertyType, mixed $value): mixed
    {
        $type = self::handleUnsupportedTypes($propertyType);

        if($value === null) {
            return self::handleNull($type);
        }

        // PHP's casting will handle every built-in type apart from arrays.
        if($type->isBuiltin() && $type->getName() !== 'array') {
            return $value;
        }


        $serializer = self::findBuiltInSerializer($type->getName());
        if($serializer !== null) {
            return $serializer::serialize($value);
        }

        if(is_a($type->getName(), ModelPropertySerializable::class, allow_string: true)) {
            return $value->serializeProperty();
        }

        throw ORMMappingException::typeNotSupported($type, 'It must implement ModelPropertySerializable.');
    }

    /**
     * Converts a column value into its property value.
     *
     * @throws ORMMappingException
     */
    public static function deserialize(IntersectionType|NamedType|UnionType $propertyType, mixed $value): mixed
    {
        $type = self::handleUnsupportedTypes($propertyType);

        if($value === null) {
            return self::handleNull($type);
        }

        // Apart from arrays return as-is, PHP's casting will handle it.
        if($type->isBuiltin() && $type->getName() !== 'array') {
            return $value;
        }

        $serializer = self::findBuiltInSerializer($type->getName());
        if($serializer !== null) {
            return $serializer::deserialize($value, $type->getName());
        }

        if(is_a($type->getName(), ModelPropertySerializable::class, allow_string: true)) {
            /** @var ModelPropertySerializable $classString */
            $classString = $type->getName();
            return $classString::deserializeProperty($value);
        }

        throw ORMMappingException::typeNotSupported($type, 'It must implement ModelPropertySerializable.');
    }

    /**
     * Returns null for nullable types or throws for the rest.
     *
     * @throws ORMMappingException
     */
    private static function handleNull(NamedType $type): null
    {
        if(! $type->allowsNull()) {
            throw ORMMappingException::typeNotSupported($type, 'It does not allow null values.');
        }

        return null;
    }

    /**
     * Enums and subclasses are declared by their own name, so
     * `is_a()` matches them to their parent's entry in the map.
     *
     * @return class-string<BuiltInSerializer>|null
     */
    private static function findBuiltInSerializer(string $typeName): ?string
    {
        foreach(self::$builtInCasts as $castedType => $serializer) {
            if($typeName === $castedType || is_a($typeName, $castedType, allow_string: true)) {
                return $serializer;
            }
        }

        return null;
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
