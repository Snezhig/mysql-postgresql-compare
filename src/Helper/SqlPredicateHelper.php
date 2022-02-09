<?php

namespace App\Helper;

use Doctrine\Common\Collections\ArrayCollection;

interface SqlPredicateHelper
{
    public function getConnectionName(): string;

    public function getWhereCollection(): ArrayCollection;

    public function getSelect(): array;
}