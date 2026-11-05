<?php

namespace Core\Routing\Contracts;

use Core\Exceptions\RoutingException;
use Throwable;

interface BindingResolver
{
    /**
     * Resolves populated objects from a class name and values lookup.
     *
     * @param string $name Class name of the bound class
     * @param mixed $value data from which to bind
     *
     * @return mixed
     *
     * @throws RoutingException 404 Not Found, normal and expected.
     *
     * @throws Throwable 500 server error. Most likely that the
     * resolver couldn't find the class due misconfigured route.
     */
    public static function resolve(string $name, mixed $value): mixed;

}