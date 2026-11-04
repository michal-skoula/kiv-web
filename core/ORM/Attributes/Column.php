<?php

namespace Core\ORM\Attributes;

use Attribute;

#[Attribute]
class Column
{
    /**
     * Marks an ORM Model's property as a column.
     *
     * @param bool $computed Marks a column as computed, stopping
     * the model from attempting to write to a non-existent column
     * and throwing an exception. Note that `readonly` would work
     * too, but this way is more explicit.
     */
    public function __construct(
        protected bool $computed = false,
    ) {}
}