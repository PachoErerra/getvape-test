<?php
class ModelToolDebugDB extends Model {
    private static $queries = [];

    public function log($sql, $time) {
        self::$queries[] = [
            'sql' => $sql,
            'time' => $time
        ];

        if(count(self::$queries) > 1000){
            array_shift(self::$queries);
        }
    }

    public static function getQueries() {
        return self::$queries;
    }
}