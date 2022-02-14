<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ExpectedValues;

class ProductSqlInsertService
{
    private Connection $connection;

    public function __construct(
        private ManagerRegistry $registry
    ) {
        $this->setConnection($this->registry->getDefaultConnectionName());
    }

    public function setConnection(
        #[ExpectedValues(['mysql', 'postgres'])]
        string $name
    ): self {
        $this->connection = $this->registry->getConnection($name);
        return $this;
    }

    public function insert(string $name, string $jsonProperties)
    {
        return $this->insertBatch([[$name, $jsonProperties]]);
    }

    public function insertBatch(array $data): array
    {
        $table = $this->registry->getManager()->getClassMetadata(Product::class)->getTableName();
        $stmt = 'INSERT INTO %s(name, properties) VALUES ' . str_repeat("('%s', '%s'),", count($data));
        $values = array_merge(...array_map(static fn(array $row) => array_values($row), $data));
        $sql = rtrim(sprintf($stmt, $table, ...$values), ',');
        $stack = new DebugStack();
        $this->connection->getConfiguration()->setSQLLogger($stack);
        $this->connection->executeQuery($sql);
        return  current($stack->queries);
    }
}