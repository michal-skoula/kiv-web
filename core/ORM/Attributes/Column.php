<?php

namespace Core\ORM\Attributes;

use Attribute;

#[Attribute]
class Column
{
    public function __construct(
        protected bool $fillable = true,
    ) {}
}