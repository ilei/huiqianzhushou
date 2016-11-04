<?php

/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 下午5:15
 */

require_once(dirname(__FILE__) . "/../loader.php");
require_once(dirname(__FILE__) . "/../Common/DBFactory.php");

class SubscriberLogic
{


    /**
     * @param $data 数据调度
     */
    static public function dispatcher($data)
    {

        try {
            //转换数据
            if (is_string($data)) {
                $data = json_decode($data, true);
            }


            $send_type = isset($data['send_type']) ? $data['send_type'] : 1;
            $data = kookeg_array_column($data, null, 'mobile');


            switch ($send_type) {
                case 1: {
                    SubscriberLogic::sendSMS($data);
                    break;
                }
                case 2: {
                    SubscriberLogic::sendEmail($data);
                    break;
                }
                default: {
                    echo "SubscriberLogic Dispatcher Error: Unknow Send_Type" . "\n";
                }
            }

        } catch (Exception $e) {
            echo "SubscriberLogic Dispatcher Error:" . $e->getTraceAsString() . "\n";
        }

    }


    static public function sendSMS($data)
    {
        $config = Loader::config();

        //服务商
        $sp = $config["SMS_SP"];

        //结果
        $success_arr = array();
        $fail_arr = array();
        $res = '[]';

        //判断服务商
        switch ($sp) {
            case "YunPian": {
                $res = yunpian_multixsend($config["SMS"]["YunPian"], $data);

                break;
            }
            case "Submail": {
                $res = submail_multixsend($config["SMS"]["Submail"], $data);
                break;
            }
            default: {
                echo "SubscriberLogic_SendSMS Error: Not Match SP " . $sp . "\n";
                return;
            }
        }


        //处理发送结果
        if (!is_array($res)) {
            $res = json_decode($res, true);
        }
        if (!$res) {
            return false;
        }
        //遍历结果集 区分成功失败
        foreach ($res as $value) {
            if ($value['status'] == 'success') {
                $success_arr[] = $data[$value['to']];
            } else {
                $fail_arr[] = $data[$value['to']];
            }
        }
        unset($res);

        //更新数据库状态
        SubscriberLogic::updateState($success_arr, $fail_arr, 'message');

    }

    static public function sendEmail($data)
    {

        $config = Loader::config();

        //服务商
        $sp = $config["Email_SP"];

        //结果
        $success_arr = array();
        $fail_arr = array();
        $res = '[]';


        switch ($sp) {
            case "Submail": {

                foreach ($data as $key => $value) {
                    $subject = '【' . $value['app_name'] . '】活动电子票';
                    $res = send_email($config["Email"]['Submail'], $value['email'], $value['app_name'], $subject, $value['content']);
                    if (!$res) {
                        return false;
                    }
                    if (!is_array($res)) {
                        $res = json_decode($res, true);
                    }
                    if ($res['status'] == 'success') {
                        $success_arr[] = $value;
                    } else {
                        $fail_arr[] = $value;
                    }
                    sleep(0.05);
                }


                break;
            }
            default: {
                echo "SubscriberLogic_SendSMS Error: Not Match SP " . $sp . "\n";
                return;
            }
        }

        unset($res);

        //更新数据库状态
        SubscriberLogic::updateState($success_arr, $fail_arr, 'email');

    }


    static public function  updateState($success, $fail, $type)
    {
        $user_guid = null;
        $config = Loader::config();

        //创建DB
        $db = DBFactory::create($config["db"]);

        if ($success) {
            $ids = kookeg_array_column($success, 'id');
            $ticket_guids = kookeg_array_column($success, 'ticket_guid');
            $success = kookeg_array_column($success, 'account_guid');
            $user_guid = $success[0];
            $sql = 'UPDATE ym_msg_content set status = 2 where id IN (' . trim(implode(',', $ids), ',') . ')';
            $db->exec_update($sql);
            $ticket_guids = "'" . trim(implode("','", array_unique($ticket_guids)), ',\'') . "'";
            $sql = 'UPDATE ym_activity_user_ticket set status = 2 where guid IN (' . $ticket_guids . ')';
            $db->exec_update($sql);
        }
        if ($fail) {
            $ids = kookeg_array_column($fail, 'id');
            $ticket_guids = kookeg_array_column($fail, 'ticket_guid');
            $fail = kookeg_array_column($fail, 'account_guid');
            $user_guid = $fail[0];
            $sql = 'UPDATE ym_msg_content set status = 3 where id IN (' . trim(implode(',', $ids), ',') . ')';
            $db->exec_update($sql);
            $ticket_guids = "'" . trim(implode("','", array_unique($ticket_guids)), ',\'') . "'";
            $sql = 'UPDATE ym_activity_user_ticket set status = 1 where guid IN (' . $ticket_guids . ')';
            $db->exec_update($sql);
        }
        if ($user_guid) {

            $sql = "";
            $nums = intval(count($success));

            if ($type == 'email') {
                $sql = 'UPDATE ym_user_account set email_nums = email_nums -' . $nums . ' where account_guid = \'' . $user_guid . '\'';
            } else if ($type == 'message') {
                $sql = 'UPDATE ym_user_account set msg_nums = msg_nums -' . $nums . ' where account_guid = \'' . $user_guid . '\'';
            }

            if (!empty($sql)) {

                $db->exec_update($sql);
            }
        }

        unset($db);
    }


}