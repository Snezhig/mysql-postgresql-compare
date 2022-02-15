<?php

namespace App\Tests\Unit\SqlPredicateHelper;

use App\Helper\PostgresqlPredicateHelper;

class PostgresqlPredicateHelperTest extends AbstractSqlPredicateHelperTest

{

    /**
     * @dataProvider jsonSetDataProvider
     */
    public function testCreateJsonSetPredicateSingle(array $value)
    {
        $predicate = $this->helper->createJsonUpdatePredicate($value);
        $sql = $this->createUpdatePropertySql($predicate);
        $this->explain($sql);
        $this->addToAssertionCount(1);
    }

    protected function getConnectionName(): string
    {
        return 'postgres';
    }

    protected function getHelperClass(): string
    {
        return PostgresqlPredicateHelper::class;
    }

    private function jsonSetDataProvider(): array
    {
        $base = [
            [['int' => 45]],
            [['float' => 45.6]],
            [['string' => 'string_val']]
        ];
        $base[] = [array_merge(...[...array_merge(...$base)])];
        return $base;
    }
}