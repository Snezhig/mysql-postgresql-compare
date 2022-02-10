<?php

namespace App\Helper;

use App\Setting\SqlCompareValueSetting;
use Doctrine\Common\Collections\ArrayCollection;

interface SqlPredicateHelper
{
    public function getConnectionName(): string;

    public function getWhereCollection(): ArrayCollection;

    public function getSelect(): array;

    public function getValueSetting(): SqlCompareValueSetting;
}