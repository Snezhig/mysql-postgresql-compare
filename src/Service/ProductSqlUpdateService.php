<?php

namespace App\Service;

use App\Entity\Product;
use App\Helper\SqlPredicateHelper;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\Persistence\ManagerRegistry;

class ProductSqlUpdateService
{
    private ?SqlPredicateHelper $helper = null;
    private ?array $filter = null;

    public function __construct(
        private ManagerRegistry $registry
    ) {
    }

    public function setPredicateHelper(SqlPredicateHelper $helper): self
    {
        $this->helper = $helper;
        return $this;
    }

    public function setPropertyKeys(array $data, int $limit = 0): DebugStack
    {
        $predicate = $this->helper->createJsonUpdatePredicate($data);
        $manager = $this->registry->getManager();
        /**@var Connection $connection*/
        $connection = $this->registry->getConnection($this->helper->getConnectionName());
        $table = $manager->getClassMetadata(Product::class)->getTableName();

        $where = '';

        if ($limit) {
            $ids = $connection->executeQuery("SELECT id FROM $table ORDER BY id LIMIT $limit")->fetchFirstColumn();
            $where = sprintf('WHERE id in (%s)', implode(',', $ids));
        }

        $stack = new DebugStack();
        $connection->getConfiguration()->setSQLLogger($stack);

        $sql = sprintf(
            'UPDATE %s SET properties = %s %s',
            $table,
            $predicate,
            $where
        );
        $connection->executeQuery($sql);

        return $stack;

    }
}