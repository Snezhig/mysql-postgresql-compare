<?php

namespace App\Helper;

use App\Entity\Mysql\Product;
use Doctrine\Common\Collections\ArrayCollection;

class MysqlWhereHelper extends AbstractSqlWhereHelper
{

    public function getEntityClassName(): string
    {
        return Product::class;
    }

    public function getManagerName(): string
    {
        return 'mysql';
    }

    public function getWhereCollection(): ArrayCollection
    {
        return new ArrayCollection($this->prepare([
            'name_like'                => "name LIKE '%uo%'",
            'json_int_eq'              => "JSON_CONTAINS(properties, '#int_v#', '$.#int#') = 1",
            'json_int_gt'              => "JSON_EXTRACT(properties, '$.#int#') > #int_v#",
            'json_string_contains'     => "JSON_CONTAINS(properties, '\"#string_v#\"', '$.#string#') = 1",
            'json_float_gt'            => "JSON_EXTRACT(properties, '$.#float#') > #float_v#",

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
}