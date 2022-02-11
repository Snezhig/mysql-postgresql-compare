<?php

namespace App\Command;

use App\Helper\MysqlPredicateHelper;
use App\Helper\PostgresqlPredicateHelper;
use App\Service\ProductSqlUpdateService;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductUpdateCompare extends Command
{
    protected static $defaultName = 'app:product:update:compare';
    private Generator $faker;

    public function __construct(
        private PostgresqlPredicateHelper $postgresqlPredicateHelper,
        private MysqlPredicateHelper      $mysqlPredicateHelper,
        private ProductSqlUpdateService   $service
    ) {
        $this->faker = Factory::create();
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newValues = [
            ['string' => $this->faker->word],
            ['int' => $this->faker->numberBetween(1, 1000000)],
            ['float' => $this->faker->randomFloat(2, 1, 1000000)],
            [
                'string' => $this->faker->word,
                'int'    => $this->faker->numberBetween(1, 1000000),
                'float'  => $this->faker->randomFloat(2, 1, 1000000),
            ],
        ];
        $results = [];
        foreach ($newValues as $value) {
            $valueResults = [];
            $helpers = [
                $this->mysqlPredicateHelper,
                $this->postgresqlPredicateHelper
            ];
            foreach ($helpers as $helper) {
                $stack = $this->service
                    ->setPredicateHelper($helper)
                    ->setPropertyKeys($value, 1000);
                $valueResults[$helper->getConnectionName()] = [
                    'time' => current($stack->queries)['executionMS'],
                    'sql'  => current($stack->queries)['sql']
                ];
            }
            $results[] = $valueResults;
        }

        $table = new Table($output);
        $table->setHeaders(['Type', 'Time', 'Sql']);

        foreach ($results as $result) {
            foreach ($result as $type => $data) {
                $time = $data['time'];
                if ($type === $this->postgresqlPredicateHelper->getConnectionName()) {
                    $mysqlTime = $result[$this->mysqlPredicateHelper->getConnectionName()]['time'];
                    $diff = round((1 - $time / $mysqlTime) * 100, 2);
                    $time = "$time ({$diff}%)";
                }
                $table->addRow([
                    $type,
                    $time,
                    $this->formatSql($data['sql'])
                ]);
            }
        }

        $table->render();
        return 1;
    }


    private function formatSql(string $sql): string
    {
        $regexp = '\((?<ids>[\d+,]+)\)';
        preg_match("/$regexp/", $sql, $matches);
        $ids = explode(',', $matches['ids']);
        $range = current($ids) . ' ... ' . end($ids);
        return preg_replace("/$regexp/", "($range)", $sql);
    }
}