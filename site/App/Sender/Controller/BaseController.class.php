<?php
namespace Sender\Controller;

use Think\Controller;

class BaseController extends Controller{

    public static $dbnum = 1;

    public function __construct() {
        parent::__construct();
    }

    public function _empty() {
        exit('empty');
    }

    /**
     * 发送短信验证码 
     *
     * @access public 
     * @param  int    $type   验证码类型，不同的类型使用不同的模板 参看C('CODE_TYPE'); 
     * @param  string $mobile 要发送的验证码的手机 
     * @param  array  $code   array(123453, 30)  第一个是验证码，第二个是有效时间
     * @param  array  $args   array('type' => 1 or 2) 1是短信验证码 2是语音验证码 
     * @return array 
     **/ 

    public function send($type, $mobile, $code, $args){
        vendor('YmPush.VerCodeInfo');		
        return \VerCodeInfo::send($type, $mobile, $code, $args);
    } 

    /**
     * 改变数据库
     *
     * @access public 
     * @param  string $db_config 
     * @return void 
     **/ 

    public function changedb($db_config = 'MEETELF'){
        M()->db(self::$dbnum++, $db_config);		
    }

    /**
     * swoole发送电子票 
     *
     * @access public 
     * @param  void 
     * @return void 
     **/ 

    public function setList(){
        list($item, $type, $params, $args, $level) = I('post.');
        if(!$params){
            return false;
        }
        $this->changedb();
        $target = $args['target']; 
        if(in_array('sms', $args['send_way'])){
            $send_type = 1;
        }elseif(in_array('email', $args['send_way'])){
            $send_type = 2;
        }else{
            $send_type = 3;
        }
        $data   = array(
            'item'   => $item,
            'type'   => $type,
            'level'  => $level,
            'status' => 1,
            'created_time' => time(),
            'updated_time' => time(),
            'args'   => json_encode($args),
            'activity_guid' => $args['aguid'],
            'send_type' => $send_type,
        );			
        if($target == 'all'){
            $data['types'] = 2; 
        }elseif($target == 'other'){
            $data['types'] = 3; 
        }elseif($target == 'part'){
            $data['types'] = 1; 
        }
        $data['params'] = json_encode($params); 
        $res = M('SendMsg')->data($data)->add();
        if($res){
            self::sendMSG('wait', $res);
        }
        return $res;
    }

    /**
     *
     * 发送消息给 swoole_server 有新任务
     *
     * @access public static 
     * @param  string $cmd 
     * @param  string $msg 
     * @return void 
     **/ 

    public static function sendMSG($cmd = 'wait', $msg_id = 0){
        //连接swoole
        $client = new \swoole_client(SWOOLE_SOCK_TCP);	
        if(!$client->connect(C('swoole_host'), 9502)){
            file_put_contents(C('log_file'), 'Sender Connect Server at failed :' . date('Y-m-d H:i:s'), FILE_APPEND);
        }else{
            $send = array();
            //发送命令 
            $send['type']    = 'wait';
            $send['msg_id']  = intval($msg_id);
            $client->send(json_encode($send));
            $receive = $client->recv();
        }
    }  


    public function resolve(){
	$arr = M('Activity')->where(array('is_del' => 0))->getField('guid');
        foreach($arr as $v){
            $activity = M('Activity')->where(array('guid' => $v))->find();
            $ticket   = M('ActivityUserTicket')->where(array('activity_guid' => $v))->select();
            C('meetelf_url', 'http://m.meetelf.com');
            foreach($ticket as $value){
                $ticket_url = U(C('meetelf_url') . '/Mobile/Activity/ticket', array('aid' => $activity['guid'], 'iid' => $value['userinfo_guid']), true, true);
                $ticket_short_url = get_short_url($ticket_url . '/source/1'); // 长度20
                $exist = M('MsgContent')->where(array('activity_guid' => $v, 'ticket_guid' => $value['guid']))->find();
                if($exist){
                    continue;
                }
                $data = array(
                    'guid' => create_guid(),
                    'title'=> $activity['name'],
                    'url'  => $ticket_short_url,
                    'mobile'=> $value['mobile'],
                    'type' => 1,
                    'status'=>1,
                    'ticket_guid'=> $value['guid'],
                    'activity_guid' => $activity['guid'],
                    'user_guid' => $value['user_guid'],
                    'account_guid' => $activity['user_guid'],
                ); 
                $res = M('MsgContent')->data($data)->add();
                var_dump($res);
            }
        }
        echo 'ok';
    }

}
