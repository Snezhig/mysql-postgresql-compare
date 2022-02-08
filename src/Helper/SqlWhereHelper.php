<?php

namespace App\Helper;

use Doctrine\Common\Collections\ArrayCollection;

interface SqlWhereHelper
{
    public function getEntityClassName(): string;

    public function getManagerName(): string;

    public function getWhereCollection(): ArrayCollection;

}