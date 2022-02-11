<?php

namespace App\Helper;

use Doctrine\Common\Collections\ArrayCollection;

class MysqlPredicateHelper extends AbstractSqlPredicateHelper
{
    public function getSelect(): array
    {
        return [
            'id',
        ];
    }

    public function getConnectionName(): string
    {
        return 'mysql';
    }

    public function getWhereCollection(): ArrayCollection
    {
        return new ArrayCollection($this->prepare([
            'name_like'            => "name LIKE '%uo%'",
            'json_int_eq'          => "JSON_CONTAINS(properties, '#int_v#', '$.#int#') = 1",
            'json_int_gt'          => "JSON_EXTRACT(properties, '$.#int#') > #int_v#",
            'json_string_contains' => "JSON_CONTAINS(properties, '\"#string_v#\"', '$.#string#') = 1",
            'json_float_gt'        => "JSON_EXTRACT(properties, '$.#float#') > #float_v#",

            'name_like_json_int_lte' => "
            name like '%us%'
            and
            JSON_EXTRACT(properties, '$.#int#') <= #int_v#
            ",

            'name_like_json_int_lte_string_contains' => "
            name like '%us%'
            and
            JSON_EXTRACT(properties, '$.#int#') <= #int_v#
            and
            JSON_CONTAINS(properties, '\"#string_v#\"', '$.#string#') = 1
            ",

            'json_float_gt_and_string' => "
            JSON_EXTRACT(properties, '$.#float#') > #float_v# 
            and
            JSON_CONTAINS(properties, '\"#string_v#\"', '$.#string#') = 1",

            'json_float_gt_and_not_string' => "
            JSON_EXTRACT(properties, '$.#float#') > #float_v# 
            and
            JSON_CONTAINS(properties, '\"#string_v#\"', '$.#string#') = 0",

            'json_float_gt_int_lte_string_like' => "
            JSON_EXTRACT(properties, '$.#float#') > #float_v#
            and
            JSON_EXTRACT(properties, '$.#int#') <= #int_v#
            and
            JSON_EXTRACT(properties, '$.#string#') like '%a%'
            "
        ]));
    }

    public function createJsonSetPredicate(array $data): string
    {
        $format = sprintf(
            'JSON_SET(properties, %s)',
            implode(',',
                array_fill(0, count($data) * 2, '%s'))
        );
        $flatData = [];
        foreach ($data as $key => $value) {
            $flatData[] = "'$.$key'";
            $flatData[] = match (gettype($value)) {
                'string' => sprintf('"%s"', $value),
                default => $value,
            };
        }
        return sprintf($format, ...$flatData);
    }
}