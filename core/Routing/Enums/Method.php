<?php

namespace Core\Routing\Enums;

enum Method: string
{
    case GET = 'get';
    case POST = 'post';
    case PATCH = 'patch';
    case PUT = 'put';
    case DELETE = 'delete';
    case UNSUPPORTED = 'unsupported';
}