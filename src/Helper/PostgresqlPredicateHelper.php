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
            'name_like'                => "name LIKE '%uo%'",
            'json_int_eq'              => "(properties ->> '#int#')::int = #int_v#",
            'json_int_eq_ext'          => "(properties @> '{\"#int#\": #int_v#}')",
            'json_int_gt'              => "(properties ->> '#int#')::int > #int_v#",
            'json_string_contains'     => "properties ->> '#string#' = '#string_v#'",
            'json_string_contains_ex'  => "properties @> '{\"#string#\": \"#string_v#\"}'",
            'json_float_gt'            => "(properties -> '#float#')::float > #float_v#",

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
}