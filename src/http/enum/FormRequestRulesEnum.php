<?php

declare(strict_types=1);

namespace Konarsky\http\enum;

use InvalidArgumentException;
use Konarsky\http\Form\Rule\RequiredFormRequestRule;

enum FormRequestRulesEnum: string
{
    case REQUIRED = RequiredFormRequestRule::class;

    public static function match(string $rule): string
    {
        $normalizedRule = strtoupper($rule);

        foreach (self::cases() as $case) {
            if ($case->name === $normalizedRule) {
                return $case->value;
            }
        }

        throw new InvalidArgumentException("Неизвестное правило: $rule");
    }
}
