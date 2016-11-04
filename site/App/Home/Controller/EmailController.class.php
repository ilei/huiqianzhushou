<?php
namespace Home\Controller;

use Home\Controller\BaseController;
use 	  Think\Controller;

class EmailController extends BaseController{

	public function __construct(){
        parent::__construct($login = false);
    }

    public function email_verify(){
            $verify = stripslashes(trim($_GET['t']));
            $email = $_GET['e'];
            if(empty($email)){
                $this->error('数据错误，请稍后再试',U('Auth/login'));
            }
            $nowtime = time();
            $model_user = D('User');
            $email_status = $model_user->where(array('email' => $email,'is_del' => 0,'email_verify' => 1))->select();

            if(!empty($email_status)){
                $this->error('邮箱已经被认证',U('Auth/login'));
                exit;
            }
            $info = $model_user->find_one(array('is_del'=>0,'email_token'=>$verify,'email_verify' => 0));
            if($info){
                if($nowtime>$info['token_exptime']){ //24hour
                    $msg = '您的认证有效期已过，请登录您的帐号重新发送认证邮件.';
                    exit($this->error($msg));
                }else{
                    $r = $model_user->where(array('email_token'=>$verify))->save(array('email_verify'=>1,'updated_at' => time()));
                	$msg = '恭喜您成功认证邮箱，请返回重新刷新页面.';
                    exit($this->success($msg, U('Home/User/index')));
                }
            }else{
                $msg = '邮箱认证失败，前往基本信息管理页，确认邮箱';
                exit($this->error($msg));
            }
            // echo $msg;
            
    }

}
