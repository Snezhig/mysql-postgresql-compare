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

    public static function getTypeValuePairs(): array
    {
        $pairs = [];
        foreach (self::cases() as $enum) {
            $pairs[mb_strtolower($enum->name)] = [
                'property' => $enum->getProperty(),
                'value'    => $enum->getPropertyStaticValue()
            ];
        }
        return $pairs;
    }

    public function getPropertyStaticValue(): string
    {
        return match ($this->name) {
            self::Float->name => 4539.52,
            self::Int->name => 4512,
            self::String->name => 'aliquam',
        };
    }
}