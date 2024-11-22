<?php

declare(strict_types=1);

namespace Konarsky\http\enum;

enum ResourceActionTypesEnum: string
{
    case CREATE = 'create';
    case READ = 'read';
    case PUT = 'put';
    case PATCH = 'patch';
    case DELETE = 'delete';
}
