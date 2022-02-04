# Fixtures

```bash
php bin/console doctrine:fixtures:load --group=postgres
php bin/console doctrine:fixtures:load --group=mysql --em=mysql
```

# Migrations

```bash
php bin/console doctrine:migration:migrate --configuration=config/doctrine_migrations/postgres.yml
php bin/console doctrine:migration:migrate --configuration=config/doctrine_migrations/mysql.yml
```