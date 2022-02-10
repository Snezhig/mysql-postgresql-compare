<?php

namespace App\Helper;

use App\Enum\JsonColumnEnum;
use App\Setting\SqlCompareValueSetting;

abstract class AbstractSqlPredicateHelper implements SqlPredicateHelper
{
    public function __construct(
        private SqlCompareValueSetting $setting
    ) {
    }

    public function getValueSetting(): SqlCompareValueSetting
    {
        return $this->setting;
    }

    protected function prepare(array $where): array
    {
        $regs = [];
        foreach ($this->getTypeValuePairs() as $type => $pair) {
            $regs["/#$type#/"] = $pair['property'];
            $regs["/#{$type}_v#/"] = $pair['value'];
        }
        return preg_replace(
            array_keys($regs),
            array_values($regs),
            $where
        );
    }

    private function getTypeValuePairs(): array
    {
        $pairs = [];
        foreach (JsonColumnEnum::cases() as $enum) {
            $pairs[mb_strtolower($enum->name)] = [
                'property' => $enum->getProperty(),
                'value'    => match ($enum->name) {
                    JsonColumnEnum::String->name => $this->setting->getString(),
                    JsonColumnEnum::Int->name => $this->setting->getInt(),
                    JsonColumnEnum::Float->name => $this->setting->getFloat(),
                }
            ];
        }
        return $pairs;
    }
}