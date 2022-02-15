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
php bin/console app:product:fixture:prepare --icount 20000 --pcount 4000
```

* Apply fixtures

```bash
php bin/console doctrine:fixtures:load
php bin/console doctrine:fixtures:load --em=mysql
```

* Run command to compare:

```shell
# output a table with results
php bin/console app:product:select:compare
php bin/console app:product:insert:compare
php bin/console app:product:update:compare
```

---

# Api

## Types

* Path: `[GET]/api/compare/types` - return types

## Select

* Path:
    * `[GET]/api/compare/{type}/select/?values=<array>` - return where predicates for type with values
    * `[POST]/api/compare/{type}/select/?values=<array>&?only=<array>` - return queries results
* Query params:
    * values = [int => integer, string => string, float => float]
    * only = array[keys of predicates]

## Insert

* Path: `[POST]/api/compare/{type}/insert`
* Json:

```json
{
  "data": [
    {
      "name": "first element",
      "properties": {
        "int": 12345,
        "string": "string_value"
      }
    },
    {
      "name": "second element",
      "properties": {
        "int": 54321,
        "string": "eulav_gnirts"
      }
    }
  ],
  "batch": false
}
```

To insert with one query: `batch: true`.

---

## Update

* Path: `[POST]/api/compare/{type}/update` - update json keys
* Json:

```json
{
  "data": {
    "int": 43243,
    "string": "string_value"
  },
  "limit": 100
}
```

To update all records: `limit: 0`