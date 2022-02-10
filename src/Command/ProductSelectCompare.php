<?php

namespace App\Command;

use App\Helper\MysqlPredicateHelper;
use App\Helper\PostgresqlPredicateHelper;
use App\Service\ProductSqlSelectService;
use App\Setting\SqlCompareValueSetting;
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
        private PostgresqlPredicateHelper $postgresqlWhereHelper,
        private MysqlPredicateHelper      $mysqlWhereHelper,
        private ProductSqlSelectService   $service,
    ) {
        parent::__construct();
    }

    public function getDescription(): string
    {
        return 'Выбирает данные из двух баз и сравнивает скорость выборки';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filters = [
            ['name_like', 'name_like'],
            ['json_int_eq', 'json_int_eq'],
            ['json_int_eq_ext', 'json_int_eq'],
            ['json_int_gt', 'json_int_gt'],
            ['json_string_contains', 'json_string_contains'],
            ['json_string_contains_ex', 'json_string_contains'],
            ['json_float_gt', 'json_float_gt'],
            ['json_float_gt_and_string', 'json_float_gt_and_string'],
            ['json_float_gt_and_not_string', 'json_float_gt_and_not_string'],
            ['json_float_gt_int_lte_string_like', 'json_float_gt_int_lte_string_like'],
            ['name_like_json_int_lte', 'name_like_json_int_lte'],
            ['name_like_json_int_lte_string_contains', 'name_like_json_int_lte_string_contains'],
        ];

        $this->output = $output;

        $results = $this->compare($filters);

        $this->write($results);

        return 1;
    }

    public function compare(array $filters): array
    {
        $results = [];
        foreach ($filters as $item) {
            $mysql = $this->service
                ->setWhereHelper($this->mysqlWhereHelper)
                ->only([$item[1]])
                ->execute();
            $postgresql = $this->service
                ->setWhereHelper($this->postgresqlWhereHelper)
                ->only([$item[0]])
                ->execute();
            $results[] = [
                'mysql'      => current($mysql),
                'postgresql' => current($postgresql),
            ];
        }
        return $results;

    }

    #[ArrayShape([
        'result' => 'array',
        'time'   => 'float',
        'sql'    => 'string'
    ])]
    private function write(array $results)
    {
        $table = new Table($this->output);
        $table->setHeaders(['#', 'type', 'count', 'time', 'sql']);
        $table->setHeaderTitle('Results');
        foreach ($results as $i => $result) {
            $number = new TableCell($i + 1, ['rowspan' => 2]);

            foreach ($result as $type => $data) {
                $diff = $type === 'postgresql'
                    ? sprintf(
                        " (%s%s)",
                        round(
                            (1 - $result['postgresql']['time'] / $result['mysql']['time']) * 100,
                            2
                        ), '%')
                    : '';

                $row = [
                    $type,
                    count($data['data']),
                    round($data['time'], 4) . $diff,
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