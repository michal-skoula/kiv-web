<?php

namespace Core\Routing;

use Attribute;
use Core\Routing\Contracts\BindingResolver;

#[Attribute]
class BoundBy
{
    /**
     * @param class-string<BindingResolver> $handler Defines which
     * handler is responsible for resolving this data type.
     */
    public function __construct(
        public string $handler
    ) {}
}