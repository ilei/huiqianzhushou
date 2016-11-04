<?php
namespace Mobile\Controller;

use       Think\Controller;
use Think\Verify;

/**
 *
 * 登录控制器
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/
class FindpwdController extends BaseController
{

    public function __construct()
    {

        parent::__construct();
        //使用layout
        layout('layout');

        $this->title = '找回密码';
        $this->main = '/Public/mobile/js/findpwd.js';
    }

    public function pwd_username(){

        if($_POST){
            $mobile = I('post.username');
            $verify = I('post.verify');
            if(empty($mobile) || empty($verify)){
                $this->error('参数错误请稍后再试');
            }

            session('mobile',$mobile);
//            session('verify',$verify);

//            redirect(U('Mobile/Findpwd/pwd_pwd',array('mobile'=>$mobile),true,true));
            redirect(U('Mobile/Findpwd/pwd_pwd'));
        }

        $this->show();
    }

    public function pwd_pwd(){

        $mobile = session('mobile');
        if(empty($mobile)){
            $this->error('请确认输入手机号',U('Mobile/Findpwd/pwd_username'));
        }
//        $mobile = I('get.mobile');
        $user_model = D('User');
        if(!empty($_POST['password'])){
            $password = I('post.password');
            if(empty($password)){
                $this->error('参数错误请稍后再试');
                exit;
            }
            $data['password'] = md5($password);
            $res = $user_model->where(array('mobile' => $mobile))->data($data)->save();
//            var_dump($mobile);die;
//            if(empty($res)){
//                $this->error('参数错误请稍后再试');
//                exit;
//            }else{
            session('mobile','');
            $this->success('密码修改成功，前往登陆页',U('Mobile/Auth/login','',true,true));
//            }
        }

//        $this->assign('mobile',$mobile);
        $this->show();
    }

    /*
     * 检查手机号是否存在
     *
     * CT: 2015-11-04 by RTH
     */
    public function check_mobile(){

        $mobile = I('post.mobile');
        if(empty($mobile)){
            $this->error('手机号不正确');
        }

        $user_model = D('User');
        $res = $user_model->where(array('mobile' => $mobile,'is_del' => 0))->find();
        if(!empty($res)){
            echo 'true';
//            $this->ajaxReturn('true');
            exit;
        }else{
            echo 'false';
//            $this->ajaxReturn('false');
            exit;
        }
    }

    //验证码
    public function verify(){

        $config = array(
            'imageW' => 150,
            'imageH' => 40,
            'fontSize' => 20,
            'length' => 4,
            'useCurve' => false,
        );
        $verify = new Verify($config);
        $verify->codeSet = '0123456789';
        $verify->entry();
    }

    //ajax验证验证码

    public function ajax_check_verify(){
        $verify = new Verify();
        $code = I('post.verify');
        if($verify->check($code)){
//            echo 'true';
            $data['status'] = 'ok';
            $this->ajaxReturn($data);
//            exit();
        }else{
//            echo 'false';
            $data['status'] = 'ko';
            $this->ajaxReturn($data);
//            exit();
        }
    }


    //ajax发送校验码
    public function ajax_send_code(){
        $mobile = I('post.mobile');//获取用户名信息

        $time = time();
        $code = get_mobile_code();

        $data['guid'] = create_guid();
        $data['created_at'] = $time;
        $data['updated_at'] = $time;
        $data['status'] = 0;
        $data['code'] = $code;
        $data['type'] = 1;//忘记密码
        $data['key'] = md5($mobile.$code);//key   手机号+校验码或者邮箱+校验码以后md5
        $data['expired_at'] = $time+1800;//30分钟有效

        $data['mobile'] = $mobile;
        $res = M('CheckMobile')->data($data)->add();
        if($res){
            send_sms(C('CODE_TYPE.site_find_password'), $mobile, array($code, 30), array('type' => 1));//发送验证码
            $this->ajax_return(array('status' => C('ajax_success'), 'msg' => L('_SEND_SUCCESS_')));
        }
    }

    //ajax检查手机校验码准确性
    public function ajax_check_mobile_code() {
        // 检查验证码是否正确
        $mobile = I('post.mobile');
        $code = I('post.code');
        if($mobile == '' || $code == '') {
            echo 'false';exit();
        }
        $key = md5($mobile.$code);

        $check_data = M('CheckMobile')->field('expired_at')->where(array('key' => $key))->find();
//        var_dump($check_data);die();
        if(time() < $check_data['expired_at']){
            M('CheckMobile')->where(array('key' => $key))->data(array('status' => 1,'updated_at' => time()))->save();
            echo 'true';exit();
        }else{
            echo 'false';exit();
        }
    }

}
