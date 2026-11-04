<?php

namespace Core\ORM\Enum;

enum SortOrder
{
    case ASC;
    case DESC;

    public function sql(): string
    {
        return match ($this) {
            self::ASC => 'ASC',
            self::DESC => 'DESC'
        };
    }
}
