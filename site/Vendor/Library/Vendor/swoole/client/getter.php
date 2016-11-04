<?php 
class getter{

    public static function get_msg_from_wait($redis, $db, $redis_config){
        if($redis->lSize($redis_config['wail_list']) >= $redis_config['max_num']){
            $data = $redis->lRange(0, $redis_config['max_num']); 
            $redis->lTrim(0, $redis_config['max_num']);
            if($data){
                $success = $fail = array();
                $send_type = explode(':',$data[0]);
                $send_type = intval($send_type[1]);
                foreach($data as $key => $value){
                    $data[$key] = intval($value); 
                }
                $data = array_unique(array_filter($data));
                $tail = getter::get_msg_tail($data, $db); 
                if($tail){
                    $res = submail_multixsend($tail);
                    $res = json_decode($res, true);
                    foreach($res as $key => $value){
                        if($value['status'] == 'success'){
                            $success[$key] = $tail[$value['to']];
                        }elseif($value['status'] == 'error'){
                            $fail[$key] = $tail[$value['to']];
                        }  
                    }
                    $all_ids = kookeg_array_column(array_merge($success, $fail), 'id'); 
                    getter::delete_msg_wait($all_ids, $db); 
                    setter::set_msg_success($success, $db);
                    setter::set_msg_fail($fail, $db, $redis);
                }
            }
        }
    }


    public static function get_msg_tail($ids = array(), $db = null){
        $ids = trim(implode(',', array_filter($ids))); 
        $sql = "SELECT * FROM ym_msg_content WHERE id IN ({$ids})"; 
        $res = my_mysql_query($sql, $db);      
        $return = array();
        foreach($res as $key => $value){
            $return[$value['mobile']] = array(
                'mobile' => $value['user'], 
                'vars'   => array(
                    'events' => $value['title'],
                    'url'    => $value['url'],
                ),
                'id'     => $value['id'], 
                'activity_guid' => $value['activity_guid'],
                'ticket_guid'   => $value['ticket_guid'], 
            );
        }
        return $return;
    }

    public static function delete_msg_wait($ids, $db = null){
        $ids = trim(implode(',', array_filter($ids))); 
        $sql = "DELETE FROM ym_msg_wait WHERE content_id IN ({$ids})"; 
        return my_mysql_update($sql, $db);  
    }

    public static function update($id, $data){
        $update = '';
        foreach($data as $key => $value){
            $update .= "{$key} = {$value},";	
        }
        $update = trim($update, ',');
        $sql    = "UPDATE ym_send_msg SET " . $update . " WHERE id = {$id}"; 
        return my_mysql_update($sql);
    }

    public static function update_account($guid, $msg_nums = 0, $email_nums = 0){
        $update = 'UPDATE ym_user_account SET';  
        if($msg_nums){
            $update .= " msg_nums = msg_nums-{$msg_nums} "; 
        }
        if($email_nums){
            if($msg_nums){
                $update .= ','; 
            }
            $update .= " email_nums = email_nums-{$email_nums} "; 
        }
        $update .= " WHERE account_guid = '{$guid}'";
        return my_mysql_update($update);

    }



}
