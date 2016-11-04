<?php
namespace Api\Controller;

use Api\Controller\BaseController;
use Org\Api\YmChat;
use Org\Api\YmPush;
use Org\Api\YmREST;

/**
 * 消息发送控制器
 *
 * CT: 2014-09-25 12:00 by YLX
 *
 */
class MessageController extends BaseRestController
{

    /**
     * 发送信息
     * $json: from_id	from_name	from_iconID	to_id	to_name	to_iconID	content	send_time	msg_type	type
     * CT: 2014-09-25 15:00 by YLX
     *
     */
    public function send() {
        $this->check_request_method('post');

        // from_id	from_name	from_iconID	to_id	to_name	to_iconID	content	send_time	msg_type	type
        $json = I('post.message');
        $param = api_json_explode($json);
        // 如果所传参数为空, 返回错误
        if (empty($param)) return $this->output_error('10003');
        if (empty($param['type'])) return $this->output_error('10003');

        $msg_guid = create_guid();
        $time = time();
        $send_msg = true; // 是否通过服务器发送信息, 默认为是

        // 获取消息类型
        switch($param['msg_type']){
            case '11101':
                $msg_type = 'txt';
                break;
            case '11102':
                $msg_type = 'img';
                break;
            case '11103':
                $msg_type = 'audio';
                break;
            default:
                $msg_type = 'txt';
                break;
        }

        switch ($param['type']){
        	case '11001': // 个人&个人
                $this->output_error('10008');
                $mid = $param['from_id'];
                $tid = $param['to_id'];
                $model_contacts = D('Contacts');
                // 检查是否已经发出过申请
                $where_a2b = array(
                    'user_guid_1' => $mid,
                    'user_guid_2' => $tid
                );
                $a2b = $model_contacts->where($where_a2b)->getField('status');
                // 检查是否已收到过请求
                $where_b2a = array(
                    'user_guid_1' => $tid,
                    'user_guid_2' => $mid
                );
                $b2a = $model_contacts->where($where_b2a)->getField('status');
                // A对B, B对A, 同时为2时,才能发送消息
                if($a2b != '2' || $b2a != '2') {
                    $this->output_error('10026', 'not friends');
                }

        	    $m = D('UserMsg');
        	    $data = array(
        	    	    'guid'       => $msg_guid,
        	            'from_guid'  => $param['from_id'],
        	            'from_name'  => $param['from_name'],
        	            'from_photo' => $param['from_iconID'],
        	            'to_guid'    => $param['to_id'],
        	            'to_name'    => $param['to_name'],
        	            'to_photo'   => $param['to_iconID'],
        	            'content'    => utf8_encode($param['content']),
        	            'sent_time'  => $time,
        	            'created_at' => $time,
        	            'updated_at' => $time,
                        'msg_type'   => $msg_type
         	    );
//        	    $res = $m->data($data)->add();
                $from_user = $param['from_id'];
                $to_user = array($param['to_id']);
                $to_type = 'users';
        	    break;
        	case '11002': // 社团&个人
        	    $send_msg = false;
        	    $m = D('OrgMsg');
        	    $data = array(
                    'guid'           => $msg_guid,
                    'from_guid'      => $param['from_id'],
                    'from_name'      => $param['from_name'],
                    'from_photo'     => $param['from_iconID'],
                    'to_guid'        => $param['to_id'],
                    'to_name'        => $param['to_name'],
                    'to_photo'       => $param['to_iconID'],
                    'content'        => $param['content'],
                    'sdk_msg_status' => '3',
                    'sent_time'      => $time,
                    'created_at'     => $time,
                    'updated_at'     => $time,
                    'msg_type'       => $msg_type
        	            
        	    );
        	    $res = $m->data($data)->add();
                if(empty($res)) {
                    $this->output_error('10008', 'send failed');
                }
        	    break;
        	case '11003': // 个人讨论组
                $this->output_error('10008');
                $m = D('GroupUserDiscMsg');
                $data = array(
                    'guid'       => $msg_guid,
                    'user_guid'  => $param['user_id'],
                    'user_name'  => $param['username'],
                    'user_photo' => $param['user_iconID'],
                    'group_disc_guid'    => $param['group_id'],
                    'content'    => $param['content'],
                    'created_at' => $time,
                    'updated_at' => $time,
                    'msg_type'   => $msg_type
                );
//                $res = $m->data($data)->add();
                $from_user = $param['user_id'];
                $to_id = M('GroupUserDisc')->where(array('guid'=>$param['group_id']))->getField('chat_group_id');
                $to_user = array($to_id);
                $to_type = 'chatgroups';
        	    break;
        	case '11004': // 社团讨论组
                $this->output_error('10008');
                $m = D('GroupOrgDiscMsg');
                $data = array(
                    'guid'       => $msg_guid,
                    'user_guid'  => $param['user_id'],
                    'user_name'  => $param['username'],
                    'user_photo' => $param['user_iconID'],
                    'group_disc_guid'    => $param['group_id'],
                    'content'    => $param['content'],
                    'created_at' => $time,
                    'updated_at' => $time,
                    'msg_type'   => $msg_type
                );
//                $res = $m->data($data)->add();
                $from_user = $param['user_id'];
                $to_id = M('GroupOrgDisc')->where(array('guid'=>$param['group_id']))->getField('chat_group_id');
                $to_user = array($to_id);
                $to_type = 'chatgroups';
        	    break;
        	default:
                return $this->output_error('10007');
        	    break;
        }
        
        if ($send_msg == true) {
            $content = json_encode($param);
            $chat = new YmChat();
            $res = $chat->sendMsg($from_user, $to_user, $msg_type, $param['content'], $to_type, array('content' => $content));

            // 发送失败
            if($res['status'] != 200) {
                return $this->output_error('10008');
            }
            // 保存聊天记录到数据库
            $data['sdk_msg_status'] = '1';
            $m->data($data)->add();
        }
        $this->output_data();
        exit();
    }
}