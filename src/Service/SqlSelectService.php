<?php

namespace App\Service;

use App\Helper\SqlWhereHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;

class SqlSelectService
{
    private ?SqlWhereHelper $helper = null;
    private ?array $filter = null;

    public function __construct(
        private ManagerRegistry $registry
    ) {
    }

    public function setWhereHelper(SqlWhereHelper $helper): self
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
    }

    public function execute(): array
    {
        /**@var EntityManager $manager */
        $manager = $this->registry->getManager($this->helper->getManagerName());
        $sql = $manager->createQueryBuilder()
                       ->select('p.id')
                       ->from($this->helper->getEntityClassName(), 'p')
                       ->getQuery()
                       ->getSQL();
        $results = [];
        foreach ($this->getPredicates() as $name => $predicate) {
            $stack = new DebugStack();
            $manager->getConnection()->getConfiguration()->setSQLLogger($stack);
            $data = $manager->getConnection()->executeQuery($sql . ' WHERE ' . $predicate)->fetchAllAssociative();
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