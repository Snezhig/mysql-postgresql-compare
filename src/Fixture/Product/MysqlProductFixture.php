<?php

namespace App\Fixture\Product;

use App\Entity\Mysql\Product;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class MysqlProductFixture extends ProductAbstractFixture implements FixtureGroupInterface
{

    public static function getGroups(): array
    {
        return ['mysql'];
    }

    protected function getEntity(): \App\Entity\Product
    {
        return new Product();
    }


}