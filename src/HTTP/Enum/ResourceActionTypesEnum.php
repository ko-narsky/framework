<?php

declare(strict_types=1);

namespace Konarsky\HTTP\Enum;

enum ResourceActionTypesEnum: string
{
    case INDEX = 'index';
    case VIEW = 'view';
    case CREATE = 'create';
    case UPDATE = 'update';
    case PATCH = 'patch';
    case DELETE = 'delete';
}
