<?php

namespace App\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use JsonException;

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

    /**
     * @throws JsonException
     */
    public function createJsonUpdatePredicate(array $data): string
    {
        return match (count($data)) {
            1 => $this->createJsonSetPredicate(array_key_first($data), current($data)),
            default => $this->createJsonMergePatch($data)
        };
    }

    private function createJsonSetPredicate(string $key, float|int|string $value): string
    {
        return sprintf(
            "JSON_SET(properties, '$.%s', %s)",
            $key,
            match (gettype($value)) {
                'string' => sprintf('"%s"', $value),
                default => $value,
            }
        );
    }

    private function createJsonMergePatch(array $data): string
    {
        return sprintf(
            "JSON_MERGE_PATCH(properties, '%s')",
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }
}