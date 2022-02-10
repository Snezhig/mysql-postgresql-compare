<?php
namespace App\Enum;
enum CompareControllerPathEnum: string
{
    case Postgresql = 'postgres';
    case Mysql = 'mysql';
}