<?php

namespace App\Helper;

use Doctrine\Common\Collections\ArrayCollection;

class PostgresqlPredicateHelper extends AbstractSqlPredicateHelper
{
    public function getConnectionName(): string
    {
        return 'postgres';
    }

    public function getSelect(): array
    {
        return [
            'id',
        ];
    }

    public function getWhereCollection(): ArrayCollection
    {

        return new ArrayCollection($this->prepare([
            'name_like'               => "name LIKE '%uo%'",
            'json_int_eq'             => "(properties ->> '#int#')::int = #int_v#",
            'json_int_eq_ext'         => "(properties @> '{\"#int#\": #int_v#}')",
            'json_int_gt'             => "(properties ->> '#int#')::int > #int_v#",
            'json_string_contains'    => "properties ->> '#string#' = '#string_v#'",
            'json_string_contains_ex' => "properties @> '{\"#string#\": \"#string_v#\"}'",
            'json_float_gt'           => "(properties -> '#float#')::float > #float_v#",

            'name_like_json_int_lte' => "
            name like '%us%'
            and
            (properties ->> '#int#')::int <= #int_v#
            ",

            'name_like_json_int_lte_string_contains' => "
            name like '%us%'
            and
            (properties ->> '#int#')::int <= #int_v#
            and
            properties @> '{\"#string#\": \"#string_v#\"}'
            ",

            'json_float_gt_and_string' => "
            (properties -> '#float#')::float > #float_v#
            and
            properties @> '{\"#string#\": \"#string_v#\"}'",

            'json_float_gt_and_not_string' => "
            (properties -> '#float#')::float > #float_v#
            and
            not properties @> '{\"#string#\": \"#string_v#\"}'",

            'json_float_gt_int_lte_string_like' => "
            (properties -> '#float#')::float > #float_v#
            and
            (properties -> '#int#')::int <= #int_v#
            and
            properties ->> '#string#' like '%a%'
            "
        ]));
    }

    public function createJsonSetPredicate(array $data): string
    {
        return match (count($data)) {
            1 => $this->createJsonSet(array_key_first($data), current($data)),
            default => $this->createJsonMerge($data)
        };
    }

    private function createJsonSet(string $key, string $value): string
    {
        return sprintf(
            "jsonb_set_lax(properties, '{%s}', '%s', true)",
            $key,
            match (gettype($value)) {
                'string' => sprintf('"%s"', $value),
                default => $value
            }
        );
    }

    private function createJsonMerge(array $data): string
    {
        return sprintf("properties || '%s'", json_encode($data, JSON_THROW_ON_ERROR));
    }
}