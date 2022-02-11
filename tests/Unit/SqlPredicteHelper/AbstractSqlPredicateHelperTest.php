<?php

namespace App\Tests\Unit\SqlPredicateHelper;

use App\Helper\SqlPredicateHelper;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractSqlPredicateHelperTest extends KernelTestCase
{
    protected SqlPredicateHelper $helper;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->helper = $this->getContainer()->get($this->getHelperClass());
        $this->registry = $this->getContainer()->get(ManagerRegistry::class);
    }

    abstract protected function getHelperClass(): string;

    protected function createUpdatePropertySql(string $valuePredicate): string
    {
       return sprintf('UPDATE product SET properties = %s', $valuePredicate);
    }

    protected function explain(string $sql): void
    {
        $this->registry->getConnection($this->getConnectionName())->executeQuery("EXPLAIN $sql");
    }

    abstract protected function getConnectionName(): string;
}