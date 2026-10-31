<?php

namespace Core\Routing\Enums;

enum RequestType: string
{
    case GET = 'get';
    case POST = 'post';
    case PATCH = 'patch';
    case PUT = 'put';
    case DELETE = 'delete';
}