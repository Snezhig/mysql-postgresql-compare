<?php

namespace App\Fixture\Product;

use App\Entity\Postgres\Product;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class PostgresProductFixture extends ProductAbstractFixture implements FixtureGroupInterface
{


    public static function getGroups(): array
    {
        return ['postgres'];
    }

    protected function getEntity(): \App\Entity\Product
    {
        return new Product();
    }


}