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
* Response:

```json
{
  "Mysql": "mysql",
  "Postgresql": "postgres"
}
```

## Select

### Path:

* `[GET]/api/compare/{type}/select/?values=<array>` - return where predicates for type with values
* `[POST]/api/compare/{type}/select/?values=<array>&?only=<array>` - return queries results

### Query params:

* values = [int => integer, string => string, float => float]
* only = array[keys of predicates]

### Examples:

* Request: `[GET]/api/v1/compare/mysql/select?values[int]=4444`
* Response:

    ```json
    {
      "name_like": "name LIKE '%uo%'",
      "json_int_eq": "JSON_CONTAINS(properties, '4444', '$.int_prop') = 1",
      "json_int_gt": "JSON_EXTRACT(properties, '$.int_prop') > 4444",
      "json_string_contains": "JSON_CONTAINS(properties, '\"4512\"', '$.string_prop') = 1",
      "json_float_gt": "JSON_EXTRACT(properties, '$.float_prop') > 4512",
      "name_like_json_int_lte": "name like '%us%' and JSON_EXTRACT(properties, '$.int_prop') <= 4444",
      "name_like_json_int_lte_string_contains": "name like '%us%' and JSON_EXTRACT(properties, '$.int_prop') <= 4444 and JSON_CONTAINS(properties, '\"4512\"', '$.string_prop') = 1",
      "json_float_gt_and_string": "JSON_EXTRACT(properties, '$.float_prop') > 4512 and JSON_CONTAINS(properties, '\"4512\"', '$.string_prop') = 1",
      "json_float_gt_and_not_string": "JSON_EXTRACT(properties, '$.float_prop') > 4512 and JSON_CONTAINS(properties, '\"4512\"', '$.string_prop') = 0",
      "json_float_gt_int_lte_string_like": "JSON_EXTRACT(properties, '$.float_prop') > 4512 and JSON_EXTRACT(properties, '$.int_prop') <= 4444 and JSON_EXTRACT(properties, '$.string_prop') like '%a%'"
    }
    ```

* Request: `[POST]/api/v1/compare/mysql/select?values[int]=5432&only[]=json_int_eq`
* Response:

    ```json
    {
      "json_int_eq": {
        "time": 0.820807933807373,
        "sql": "SELECT id FROM product WHERE JSON_CONTAINS(properties, '5432', '$.int_prop') = 1 ORDER BY ID",
        "data": [
          {
            "id": 7935
          }
        ]
      }
    }
    ```

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

To insert with one query: `batch = true`.

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

To update all records: `limit = 0`