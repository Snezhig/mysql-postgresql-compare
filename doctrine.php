<?php

use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $config) {
    $config->dbal()->defaultConnection('default');
    $config->dbal()
           ->connection('default')
           ->host('postgres')
           ->port(5432)
           ->driver('pdo_pgsql')
           ->dbname('%env(resolve:DB_NAME)%')
           ->user('%env(resolve:DB_USER)%')
           ->password('%env(resolve:DB_PASSWORD)%');

    $config->dbal()
           ->connection('mysql')
           ->host('mysql')
           ->driver('pdo_mysql')
           ->port(3306)
           ->dbname('%env(resolve:DB_NAME)%')
           ->user('%env(resolve:DB_USER)%')
           ->password('%env(resolve:DB_PASSWORD)%');

    $config->orm()
           ->defaultEntityManager('default')
           ->entityManager('default')
           ->connection('default')
           ->mapping('Postgres')
           ->isBundle(false)
           ->type('annotation')
           ->dir('%kernel.project_dir%/src/Entity/Postgres')
           ->prefix('App\Entity\Postgres')
           ->alias('Postgres');

    $config->orm()
           ->entityManager('mysql')
           ->connection('mysql')
           ->mapping('Mysql')
           ->isBundle(false)
           ->type('annotation')
           ->dir('%kernel.project_dir%/src/Entity/Mysql')
           ->prefix('App\Entity\Mysql')
           ->alias('Mysql');


};