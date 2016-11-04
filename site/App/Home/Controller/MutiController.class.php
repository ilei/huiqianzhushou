<?php
namespace Home\Controller;
use Think\Upload;

class MutiController extends BaseController{

    public function __construct(){
        parent::__construct();
    }

    public function import(){
        header("Content-type: text/html; charset=utf-8");
        if(IS_POST){
            $time_dir = date('Y_m_d');
            $guid     = I('get.guid');
            $config = array(
                'maxSize'  => 5*1024*1024,
                'exts'     => array('xls', 'xlsx'),
                'rootPath' => UPLOAD_PATH,
                'savePath' => '/etf/' . $time_dir . '/' . $guid . '/signup_users/',
                'subName'  => '',
                'saveName' => $guid,
                'replace' => true,
            );
            $upload = new Upload($config);//实例化上传类
            // 上传文件
            $info = $upload->upload();
            if (!$info) {// 上传错误提示错误信息
                var_dump($upload->getError());
            } else {// 上传成功
                $info = $info['file-name'];
                $data = array(
                    'file' => $config['rootPath'] . $info['savepath'] . $info['savename'], 
                    'guid' => $guid,
                );
            }
            $excel = D('Excel');
            $words = $excel->import_arr($data['file']);
            $activity_guid = $guid;
            if (!$activity_guid){
                return false;
            }
            $res = true;
            $errors = '';
            $mobile = array();
            foreach($words as $key => $value){
                if(!trim($value[1])){
                    continue;
                }
                $user = M('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'mobile' => $value[1],'is_del' => '0'))->find();
                if($user){
                    continue;
                }
                $tickets     = M('ActivityAttrTicket')->where(array('activity_guid' => $activity_guid))->select();
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
                $check_signup = M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'ticket_guid' => $ticket_guid))->count();
                if ($check_signup >= $check_total) {
                    $res = false;
                    $errors = $user_ticket['name'] . '数量不足，请修改票务数量。';
                    continue;
                }
                $params = array();
                if(!$value[1] || !preg_match('/^1[3584]{1}[0-9]{9}$/', $value[1])){
                    $mobile[$key] = $value;
                    $res = false;
                    continue;
                }
                $other = M('ActivityForm')->where(array('activity_guid' => $activity_guid))->select();
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
                $r = D('Signup', 'Logic')->signup($activity_guid, $params, $user_from, $ext);
            }
            if($res){
                $this->assign('msg', '上传成功'); 
            }else{
                if($errors){
                    $this->assign('msg', $errors); 
                }elseif($mobile){
                    $this->assign('mobile', $mobile);
                } 
            }
        }
        $this->show();
    }


}
