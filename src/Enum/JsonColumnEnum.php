<?php

namespace App\Enum;

enum JsonColumnEnum
{
    case Int;
    case String;
    case Float;

    public static function getProperties(): array
    {
        return array_map(static fn(JsonColumnEnum $enum) => $enum->getProperty(), self::cases());
    }

    public function getProperty(): string
    {
        return mb_strtolower($this->name) . '_prop';
    }

}