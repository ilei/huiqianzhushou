<?php

class setter
{

    public static function set_msg_to_wait($msg_id, $db = null, $redis = null)
    {
        $sql = "SELECT * FROM ym_send_msg WHERE id = {$msg_id} and status = 1 ";
        $res = my_mysql_query($sql);
        $res = $res[0];
        $redis = empty($redis) ? init_redis() : $redis;
        if ($res) {
            //全部
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


            $content = my_mysql_query($sql, $db);


            if ($content) {

                $content = array_chunk($content, 100);
                foreach ($content as $key => $value) {
                    $value['send_type'] = $res['send_type'];
                    $value = json_encode($value);
                    $redis->rPush('data', $value);

                }

                $sql = "UPDATE ym_send_msg set status = 2 where id={$msg_id}";
                my_mysql_update($sql);
            }
        }

    }

    //
    public static function set_msg_status($success = array(), $fail = array(), $db = null)
    {
        if ($success) {
            $ids = array_columns($success, 'id');
            $ticket_guids = array_columns($success, 'ticket_guid');
            $success = array_columns($success, 'account_guid');
            $user_guid = $success[0];
            $sql = 'UPDATE ym_msg_content set status = 2 where id IN (' . trim(implode(',', $ids), ',') . ')';
            my_mysql_update($sql);
            $ticket_guids = "'" . trim(implode("','", array_unique($ticket_guids)), ',\'') . "'";
            $sql = 'UPDATE ym_activity_user_ticket set status = 2 where guid IN (' . $ticket_guids . ')';
            my_mysql_update($sql);
        }
        if ($fail) {
            $ids = array_columns($fail, 'id');
            $ticket_guids = array_columns($fail, 'ticket_guid');
            $fail = array_columns($fail, 'account_guid');
            $user_guid = $fail[0];
            $sql = 'UPDATE ym_msg_content set status = 3 where id IN (' . trim(implode(',', $ids), ',') . ')';
            my_mysql_update($sql);
            $ticket_guids = "'" . trim(implode("','", array_unique($ticket_guids)), ',\'') . "'";
            $sql = 'UPDATE ym_activity_user_ticket set status = 1 where guid IN (' . $ticket_guids . ')';
            my_mysql_update($sql);
        }
        if ($user_guid) {
            $nums = intval(count($success) + count($fail));
            $sql = 'UPDATE ym_user_account set msg_nums = msg_nums -' . $nums . ' where account_guid = \'' . $user_guid . '\'';
            my_mysql_update($sql);
        }
    }
}
