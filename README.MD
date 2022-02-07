# Description

It's just to compare mysql vs postgresql and json particularly.

# How to

* Pull repo
* Run docker `docker-compose up --build -d`
* Next commands are needed to run inside php container or through
  `docker-compose exec php -u 1000 #command#`
* Run migrations:

```bash
php bin/console doctrine:migration:migrate --configuration=config/doctrine_migrations/postgres.yaml
php bin/console doctrine:migration:migrate --configuration=config/doctrine_migrations/mysql.yaml
```

* Creates fixtures (we must have the same data for two databases):

```shell 
php bin/console app:product:fixture:prepare
```

* Apply fixtures

```bash
php bin/console doctrine:fixtures:load --group=postgres
php bin/console doctrine:fixtures:load --group=mysql --em=mysql
```

* Run command to compare:

```shell
php bin/console app:product:select:compare
```


