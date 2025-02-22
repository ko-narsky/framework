<?php

namespace Konarsky\HTTP\Enum;

enum RelationshipTypeEnum: string
{
    case ONE_TO_ONE = 'one-to-one';
    case ONE_TO_MANY = 'one-to-many';
}
