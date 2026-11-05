<?php
class ControllerToolDebugDB extends Controller {
    private static $queries = [];
    
    // логирование SQL
    public function log($sql, $time){
        self::$queries[] = ['sql'=>$sql,'time'=>$time];
        // защита: максимум 1000 запросов
        if(count(self::$queries) > 1000){
            array_shift(self::$queries);
        }
    }

    // получить все запросы
    public static function getQueries(){
        return self::$queries;
    }
}
