<?php
namespace Cli\Controller;
use       Cli\Controller\BaseController;

class MutiController extends BaseController{

    public function __construct(){
        parent::__construct();
    }

    public function import(){
        $file = UPLOAD_PATH . '/excel/signup.xls';   
        $excel = D('Excel');
        $words = $excel->import_arr($file);
        $guids = array(
            'E7D2AFF4736084C5F7D206DD759C4BD9',
            '1924ACA6DAB600CD7D55FEE0295FF9E7',
            '561D96DF3C28731C7F422D1BA17F3DA0',
            '495EA3E1A4E25121CA0C082643D2670F',
            'AB29787A0C76BCC629EA590754624993',
            'F9CC66F363E6C4C659E8FC7D2C2DBB79',
            '2CC57E6D51281298F13883EC46F17305',     
        );
        foreach($guids as $guid){
	    echo $guid . "\r\n";
            foreach($words as $key => $value){
                if(!trim($value[1])){
                    continue;
                }
                $user = M('ActivityUserinfo')->where(array('activity_guid' => $guid, 'mobile' => $value[1],'is_del' => '0'))->find();
                if($user){
                    continue;
                }
                $tickets     = M('ActivityAttrTicket')->where(array('activity_guid' => $guid))->select();
                if(!$tickets){
                    $res = false;
                    $errors = '该活动没有票务信息';
                    break;
                }else{
                    $ticket_guid = array_columns($tickets, 'guid'); 
                }
                $ticket_guid = intval($value[5]) > 1 ? $ticket_guid[1] : $ticket_guid[intval($value[5])];
                $user_ticket = M('ActivityUserTicket')->where(array('guid' => $ticket_guid))->find();
                // 当报名时, 判断是否有余票
                $check_total  = M('ActivityAttrTicket')->where(array('guid' => $ticket_guid))->getField('num');
                $check_signup = M('ActivityUserTicket')->where(array('activity_guid' => $guid, 'ticket_guid' => $ticket_guid))->count();
                if ($check_signup >= $check_total) {
                    $res = false;
                    $errors = $user_ticket['name'] . '数量不足，请修改票务数量。';
                    continue;
                }
                $params = array();
                if(!$value[1] || !preg_match('/^1[35847]{1}[0-9]{9}$/', $value[1])){
                    $mobile[$key] = $value;
                    $res = false;
                    continue;
                }
                $other = M('ActivityForm')->where(array('activity_guid' => $guid))->select();
                if(!$other){
                    $res = false;
                    $errors = '没有设置报名表单';
                    break;
                }
                $user_from = 2;
                $params['ticket'] = $ticket_guid;
                $params['info']['mobile'] = $value[1];
                $params['info']['email'] = $value[2];
                $params['info']['real_name'] = $value[0];
                foreach($other as $key => $v){
                    if($v['ym_type'] == 'email'){
                        $params['other'][$v['guid']] = array(
                            'ym_type' => 'email',
                            'build_guid' => $v['guid'],
                            'guid'    =>'',
                            'value' => $value[2] ? trim($value[2]) : '',
                            'key'   => '邮箱',
                        );

                    } 
                    if($v['ym_type'] == 'company'){
                        $params['other'][$v['guid']] = array(
                            'ym_type' => 'company',
                            'build_guid' => $v['guid'],
                            'guid'    => '',
                            'value' => $value[3] ? trim($value[3]) : '',
                            'key'   => '公司',
                        );
                    }
                    if($v['ym_type'] == 'position'){
                        $params['other'][$v['guid']] = array(
                            'ym_type' => 'position',
                            'build_guid' => $v['guid'],
                            'guid'    => '',
                            'value' => $value[4] ? trim($value[4]) : '',
                            'key'   => '职位',
                        );
                    }
                } 
                $ext['payment_type']  = 3;
                $r = D('Signup', 'Logic')->signup($guid, $params, $user_from, $ext);
                var_dump($r);
            }
        }
        echo 'ok';
    }


}
