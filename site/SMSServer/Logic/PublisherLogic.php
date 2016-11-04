<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 下午4:20
 *
 * 发布者 用于向Redis中插入数据
 */
require_once(dirname(__FILE__). "/../loader.php");
require_once(dirname(__FILE__)."/../Common/DBFactory.php");
class PublisherLogic
{

    public static function hanld_wait_msg($msg_id)
    {

        //读取配置信息
        $config = Loader::config();

        //创建DB
        $db = DBFactory::create($config["db"]);

        //创建Redis
        $redis = RedisQueue::create($config["redis"]);




        $sql = "SELECT * FROM ym_send_msg WHERE id = {$msg_id} and status = 1 ";

        $msg = $db->exec_query($sql);

        if (empty($msg)) {
            echo "Publihser Error: Not Found Msg FromDB" . "\n";
        } else {
            $res = $msg[0];
            $sql = "SELECT * FROM ym_msg_content WHERE `activity_guid` = '{$res['activity_guid']}'";



            if ($res['types'] == 2) {
                $sql .= ' AND status IN(1,3,2)';
            } elseif ($res['types'] == 3) {
                $sql .= ' AND status IN(1,3)';
            } elseif ($res['types'] == 1) {
                $params = array_columns(json_decode($res['params'], true), 'guid');
                $ticket_guids = "'" . trim(implode("','", array_unique($params)), ',\'') . "'";
                $sql .= "AND ticket_guid IN ($ticket_guids)";
            }

            $content = $db->exec_query($sql);

            if (empty($content)) {
                echo "Publihser Error: Not Found MSG Content FromDB" . "\n";
            } else {
                $content = array_chunk($content, 100);
                foreach ($content as $key => $value) {
                    $value['send_type'] = $res['send_type'];



                    var_dump($value);
                    //发送到Redis中
                    $redis->rPush(REDIS_KEY,$value);

                }

                $sql = "UPDATE ym_send_msg set status = 2 where id={$msg_id}";
                $db->exec_update($sql);
            }
        }


        //回收db redis
        unset($db);
        unset($redis);
    }

}