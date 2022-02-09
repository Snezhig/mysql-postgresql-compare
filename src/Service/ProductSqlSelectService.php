<?php

namespace App\Service;

use App\Entity\Product;
use App\Helper\SqlPredicateHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

class ProductSqlSelectService
{
    private ?SqlPredicateHelper $helper = null;
    private ?array $filter = null;

    public function __construct(
        private ManagerRegistry $registry
    ) {
    }

    public function setWhereHelper(SqlPredicateHelper $helper): self
    {
        $this->helper = $helper;
        return $this;
    }

    public function only(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    public function all(): self
    {
        $this->filter = null;
        return $this;
    }

    public function execute(): array
    {
        /**@var EntityManager $manager */
        $manager = $this->registry->getManager();
        $connection = $this->registry->getConnection($this->helper->getConnectionName());
        $table = $manager->getClassMetadata(Product::class)->getTableName();

        $sqlPart = sprintf('SELECT %s FROM %s',
            implode(',', $this->helper->getSelect()),
            $table
        );
        $results = [];
        foreach ($this->getPredicates() as $name => $predicate) {
            $sql = sprintf("%s WHERE %s ORDER BY ID", $sqlPart, $predicate);
            $stack = new DebugStack();
            $connection->getConfiguration()->setSQLLogger($stack);
            $data = $connection->executeQuery($sql)->fetchAllAssociative();
            $results[$name] = [
                'time' => current($stack->queries)['executionMS'],
                'sql'  => current($stack->queries)['sql'],
                'data' => $data
            ];
        }
        return $results;
    }

    private function getPredicates(): ArrayCollection
    {
        return empty($this->filter)
            ? $this->helper->getWhereCollection()
            : $this->helper->getWhereCollection()
                           ->filter(fn($v, $k) => in_array($k, $this->filter, true));
    }

}