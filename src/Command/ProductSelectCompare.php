<?php

namespace App\Command;

use App\Entity\Mysql\Product as ProductMysql;
use App\Entity\Postgres\Product as ProductPostgresql;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
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


        $results = $this->compare([
            ['where' => [], 'params' => []],
            ['where' => ['p.name like :s'], 'params' => ['s' => '%uo%']],
            ['where' => [['p_1', '=', ':p_1']], 'params' => ['p_1' => 4512]],
            ['where' => [['p_1', '>', ':p_1']], 'params' => ['p_1' => 4512]],
            ['where' => [['p_6', '=', ':p_6']], 'params' => ['p_6' => 'aliquam']],
        ]);

        $this->write($results);

        return 1;
    }

    public function compare($items): array
    {
        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'mysql'      => $this->execMysql($item['where'], $item['params']),
                'postgresql' => $this->execPostgres($item['where'], $item['params']),
            ];
        }
        return $results;

    }

    private function execMysql(array $where, array $parameters): array
    {
        $builder = $this->registry->getManager('mysql')->createQueryBuilder();

        $where = array_map(static function (array|string $item) use (&$parameters) {
            if (is_string($item)) {
                return $item;
            }

            switch ($item[1]) {
                case '=':
                    $key = str_replace(':', '', $item[2]);
                    $value = $parameters[$key];
                    $value = is_string($value) ? "\"$value\"" : $value;
                    $where = "JSON_CONTAINS(p.properties, '$value', '$.{$item[0]}') = 1";
                    unset($parameters[$key]);
                    break;
                default:
                    $where = "JSON_EXTRACT(p.properties, '$.{$item[0]}') {$item[1]} {$item[2]}";
            }
            return $where;
        }
            , $where);

        return $this->exec(
            $where,
            $parameters,
            $builder,
            ProductMysql::class
        );
    }

    #[ArrayShape([
        'result' => 'array',
        'time'   => 'float',
        'sql'    => 'string'
    ])]
    private function exec(array $where, array $parameters, QueryBuilder $builder, string $class): array
    {

        $q = $builder->select('p.id')->from($class, 'p');

        foreach ($where as $predicates) {
            $q->where($predicates);
        }
        $q->setParameters($parameters);
        $result = [
            'sql' => sprintf(
                str_replace('?', '%s', $q->getQuery()->getSQL()),
                ...$q->getQuery()->getParameters()->map(static fn(Parameter $p) => $p->getValue())->getValues()
            )
        ];
        $time = microtime(true);
        try {
            $result['result'] = $q->getQuery()->execute();
            $result['time'] = microtime(true) - $time;
        } catch (\Throwable $e) {
            $this->output->writeln($result['sql']);
            throw  $e;
        }
        return $result;
    }

    #[ArrayShape(['result' => "array", 'time' => "float"])]
    private function execPostgres(array $where, array $parameters): array
    {
        $builder = $this->registry->getManager()->createQueryBuilder();
        $gettype = static fn($key) => gettype($parameters[str_replace(':', '', $key)]);
        $where = array_map(static function (array|string $item) use ($gettype) {

            if (is_string($item)) {
                return $item;
            }

            $where = "JSON_GET_TEXT(p.properties, '{$item[0]}')";
            if ($item[1] !== '=' && $gettype($item[2]) === 'integer') {
                $where = "CAST ($where AS INTEGER)";
            }
            return "$where {$item[1]} {$item[2]}";
        }, $where);
        return $this->exec(
            $where,
            $parameters,
            $builder,
            ProductPostgresql::class
        );
    }

    private function write(array $results)
    {
        $table = new Table($this->output);
        $table->setHeaders(['type', 'count', 'time', 'diff', 'sql']);
        $table->setHeaderTitle('Results');
        foreach ($results as $result) {
            foreach ($result as $type => $data) {
                $table->addRow([
                    $type,
                    count($data['result']),
                    $data['time'],
                    match ($type) {
                        'mysql' => (1 - $result['mysql']['time'] / $result['postgresql']['time']) * 100,
                        'postgresql' => (1 - $result['postgresql']['time'] / $result['mysql']['time']) * 100,
                    },
                    $data['sql']
                ]);
            }
            $table->addRow(new TableSeparator());
        }
        $table->render();
    }

}