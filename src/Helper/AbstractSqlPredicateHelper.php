<?php

namespace App\Helper;

use App\Enum\JsonColumnEnum;

abstract class AbstractSqlPredicateHelper implements SqlPredicateHelper
{
    protected function prepare(array $where): array
    {
        $regs = [];
        foreach (JsonColumnEnum::getTypeValuePairs() as $type => $pair) {
            $regs["/#$type#/"] = $pair['property'];
            $regs["/#{$type}_v#/"] = $pair['value'];
        }
        return preg_replace(
            array_keys($regs),
            array_values($regs),
            $where
        );
    }
}