<?php

namespace REDBObjects\MySQL;

use PDO;

class SQLHelper
{
    /**
     *
     * @param PDO $pdo
     * @param array $keyValuePairs
     * @return string
     */
    public static function createAndWhere(PDO $pdo, array $keyValuePairs): string
    {
        $where = [];
        foreach ($keyValuePairs as $k => $v) {
            $k = '`' . str_replace('.', '`.`', $k) . '`';
            $where[] = " " . $k . " = " . $pdo->quote($v);
        }
        return implode(' and ', $where);
    }
}