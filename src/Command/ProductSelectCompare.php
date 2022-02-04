<?php

namespace App\Command;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ProductSelectCompare extends Command
{

    protected static $defaultName = 'app:product:select:compare';


    public function __construct(
        private \Doctrine\Persistence\ManagerRegistry $registry
    ) {
        parent::__construct();
    }

    public function getDescription(): string
    {
        return 'Выбирает данные из двух баз и сравнивает скорость выборки';
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**@var QueryBuilder $builder*/
       $builder =  $this->registry->getConnection()->createQueryBuilder();
        return 1;
    }


}