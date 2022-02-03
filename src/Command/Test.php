<?php
namespace App\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Test extends Command{

    protected static $defaultName = 'app:test';


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this
            ->getApplication()
             ->get('doctrine:migrations:migrate')
             ->run($input, $output);
return 1;
    }
}