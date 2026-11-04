<?php

namespace Core\ORM\Contracts;

interface ModelPropertySerializable
{
    /**
     * Converts this property value into its column value.
     */
    public function serializeProperty(): string;

    /**
     * Creates the property value from its column value.
     */
    public static function deserializeProperty(string $value): static;
}
