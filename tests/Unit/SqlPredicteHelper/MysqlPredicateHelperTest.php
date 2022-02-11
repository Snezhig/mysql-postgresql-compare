<?php

namespace App\Tests\Unit\SqlPredicateHelper;

use App\Helper\MysqlPredicateHelper;
use App\Helper\SqlPredicateHelper;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MysqlPredicateHelperTest extends AbstractSqlPredicateHelperTest
{
    public function testCreateJsonSetPredicate(): void
    {
        $predicate = $this->helper->createJsonSetPredicate([
            'int_string' => '32',
            'int'        => 23,
            'string'     => 'string_value',
            'float'      => 98.89
        ]);

        $sql = $this->createUpdatePropertySql($predicate);
        $this->explain($sql);
        $this->addToAssertionCount(1);
    }

    protected function getConnectionName(): string
    {
        return 'mysql';
    }

    protected function getHelperClass(): string
    {
        return MysqlPredicateHelper::class;
    }


}