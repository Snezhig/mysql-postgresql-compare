<?php

namespace App\Command;

use App\Enum\JsonColumnEnum;
use App\Helper\Algebra\PercentageHelper;
use App\Service\ProductSqlInsertService;
use Faker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductInsertCompare extends Command
{
    protected static $defaultName = 'app:product:insert:compare';

    public function __construct(
        private ProductSqlInsertService $service,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $items = [
            ['ext_props' => 0, 'batch' => false, 'count' => 5],
            ['ext_props' => 0, 'batch' => true, 'count' => 5],
            ['ext_props' => 4000, 'batch' => false, 'count' => 5],
            ['ext_props' => 4000, 'batch' => true, 'count' => 5],
        ];
        $results = [];

        foreach ($items as $item) {
            $data = $this->createData($item['count'], $item['ext_props']);
            if ($item['batch']) {
                $data = [$data];
            }
            foreach ($data as $datum) {
                $insertResult = [];
                foreach (['mysql', 'postgres'] as $connection) {
                    $this->service->setConnection($connection);
                    $insertResult[$connection] = [
                        'row'   => $item['batch'] ? $item['count'] : 1,
                        'props' => $item['ext_props'] + count(JsonColumnEnum::cases()),
                        'time'  => (match ($item['batch']) {
                            true => $this->service->insertBatch($datum),
                            false => $this->service->insert($datum['name'], $datum['properties'])
                        })['executionMS']
                    ];
                }
                $results[] = $insertResult;
            }
        }

        $table = new Table($output);
        $table->setHeaders(['#', 'Type', 'Rows', 'Props', 'Time']);
        $rowIndex = 0;
        foreach ($results as $result) {
            ++$rowIndex;
            foreach ($result as $type => $item) {
                $time = round($item['time'], 5);
                if ($type === 'postgres') {
                    $mysqlTime = $result['mysql']['time'];
                    $diff = $time > $mysqlTime
                        ? PercentageHelper::calcIncrease($time, $mysqlTime)
                        : PercentageHelper::calcDecrease($mysqlTime, $time);
                    $time = "$time ({$diff}%)";
                }
                $row = [
                    $type,
                    $item['row'],
                    $item['props'],
                    $time
                ];
                if ($type === 'mysql') {
                    array_unshift(
                        $row,
                        new TableCell($rowIndex, ['rowspan' => 2])
                    );
                }
                $table->addRow($row);
            }
            $table->addRow(new TableSeparator());
        }
        $table->render();
        return 1;
    }

    private function createData(int $count, int $propsCount = 0): array
    {
        $faker = Factory::create();
        $data = [];

        for ($i = 1; $i <= $count; $i++) {
            $properties = [];

            for ($pIndex = 1; $pIndex <= $propsCount; $pIndex++) {
                $properties["p_{$pIndex}"] = $faker->randomElement([
                    $faker->randomFloat(2, 1, 100000),
                    $faker->numberBetween(1, 10000),
                    $faker->word
                ]);
            }

            $data[] = [
                'name'       => $faker->word,
                'properties' => json_encode(array_merge([
                    JsonColumnEnum::Int->getProperty()    => $faker->numberBetween(1, 1000000),
                    JsonColumnEnum::String->getProperty() => $faker->word,
                    JsonColumnEnum::Float->getProperty()  => $faker->randomFloat(2, 1, 1000000),
                ], $properties), JSON_THROW_ON_ERROR)
            ];
        }

        return $data;
    }
}