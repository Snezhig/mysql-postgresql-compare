<?php

namespace App\Command;

use App\Entity\Mysql\Product as ProductMysql;
use App\Entity\Postgres\Product as ProductPostgresql;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\QueryBuilder;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductSelectCompare extends Command
{

    protected static $defaultName = 'app:product:select:compare';
    private ?OutputInterface $output = null;


    public function __construct(
        private \Doctrine\Persistence\ManagerRegistry $registry
    ) {
        parent::__construct();
    }

    public function getDescription(): string
    {
        return 'Выбирает данные из двух баз и сравнивает скорость выборки';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        $items = [
            [
                'p' => "p0_.name LIKE '%uo%'",
                'm' => "p0_.name LIKE '%uo%'"
            ],
            [
                'p' => "(p0_.properties ->> 'p_1')::int = 4512",
                'm' => "JSON_CONTAINS(p0_.properties, '4512', '$.p_1') = 1"
            ],
            [
                'p' => "(p0_.properties @> '{\"p_1\": 4512}')",
                'm' => "JSON_CONTAINS(p0_.properties, '4512', '$.p_1') = 1"
            ],
            [
                'p' => "(p0_.properties ->> 'p_1')::int > 4512",
                'm' => "JSON_EXTRACT(p0_.properties, '$.p_1') > 4512",
            ],
            [
                'p' => "p0_.properties ->> 'p_6' = 'aliquam'",
                'm' => "JSON_CONTAINS(p0_.properties, '\"aliquam\"', '$.p_6') = 1"
            ],
            [
                'p' => "p0_.properties @> '{\"p_6\": \"aliquam\"}'",
                'm' => "JSON_CONTAINS(p0_.properties, '\"aliquam\"', '$.p_6') = 1"
            ],
            [
                'p' => "(properties -> 'p_1338')::float > 4539.52",
                'm' => "JSON_EXTRACT(p0_.properties, '$.p_1338') > 4539.52",
            ],
            [
                'p' => "
                (properties -> 'p_1338')::float > 4539.52 
                and 
                p0_.properties @> '{\"p_6\": \"aliquam\"}'",
                'm' => "
                JSON_EXTRACT(p0_.properties, '$.p_1338') > 4539.52 
                and 
                JSON_CONTAINS(p0_.properties, '\"aliquam\"', '$.p_6') = 1",
            ],

        ];

        $results = $this->compare($items);

        $this->write($results);

        return 1;
    }

    public function compare($items): array
    {
        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'mysql'      => $this->execMysql($item['m']),
                'postgresql' => $this->execPostgres($item['p']),
            ];
        }
        return $results;

    }

    #[ArrayShape(['result' => "array", 'time' => "float", 'sql' => "string"])]
    private function execMysql(string $where): array
    {
        $builder = $this->registry->getManager('mysql')->createQueryBuilder();

        return $this->exec(
            $where,
            $builder,
            ProductMysql::class
        );
    }

    #[ArrayShape([
        'result' => 'array',
        'time'   => 'float',
        'sql'    => 'string'
    ])]
    private function exec(string $where, QueryBuilder $builder, string $class): array
    {
        $q = $builder->select('p.id')->from($class, 'p');
        $sql = $q->getQuery()->getSQL() . ' WHERE ' . $where;
        $result = ['sql' => $sql];
        $stack = new DebugStack();
        $connection = $builder->getEntityManager()->getConnection();
        $connection->getConfiguration()->setSQLLogger($stack);
        try {
            $result['result'] = $connection->executeQuery($sql)->fetchAllAssociative();
            $result['time'] = current($stack->queries)['executionMS'];
        } catch (\Throwable $e) {
            $this->output->writeln($result['sql']);
            throw  $e;
        }
        return $result;
    }

    #[ArrayShape(['result' => "array", 'time' => "float"])]
    private function execPostgres(string $where): array
    {
        $builder = $this->registry->getManager()->createQueryBuilder();

        return $this->exec(
            $where,
            $builder,
            ProductPostgresql::class
        );
    }

    private function write(array $results)
    {
        $table = new Table($this->output);
        $table->setHeaders(['#', 'type', 'count', 'time', 'diff', 'sql']);
        $table->setHeaderTitle('Results');
        foreach ($results as $i => $result) {
            $number = new TableCell($i + 1, ['rowspan' => 2]);
            foreach ($result as $type => $data) {
                $row = [
                    $type,
                    count($data['result']),
                    $data['time'],
                    match ($type) {
                        'mysql' => round((1 - $result['mysql']['time'] / $result['postgresql']['time']) * 100, 2),
                        'postgresql' => round((1 - $result['postgresql']['time'] / $result['mysql']['time']) * 100, 2),
                    },
                    $data['sql']
                ];
                if ($number) {
                    array_unshift($row, $number);
                    $number = null;
                }
                $table->addRow($row);
            }
            $table->addRow(new TableSeparator());
        }
        $table->render();
    }

}