<?php

namespace Core\Routing\Objects;

class Request
{
    public function __construct(string $route, array $params = [])
    {

    }

    public function __toString(): string
    {
        return json_encode($this, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
}