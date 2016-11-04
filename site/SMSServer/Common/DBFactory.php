<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 下午3:23
 */

require_once(dirname(__FILE__)."/DB/MySqlDB.php");

//数据库工厂 用于创建数据库的实例
class DBFactory
{
    static function create($config)
    {

        $db = null;
        if (empty($config)) {
            echo "CREATE DB OBJECT Error : No Config"."\n";
        }else{
            $connection_type = $config["type"];
            switch ($connection_type) {
                case "mysql": {
                    $db= new MySqlDB($config);
                    break;
                }
                default: {
                    echo "Not Match DB Type:".$connection_type."\n";
                }
            }
        }
        return $db;
    }
}