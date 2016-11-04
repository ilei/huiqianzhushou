<?php
/**
 * 个人中心忘记密码页
 *
 * CT: 2015-1-29 12:18 by RTH
 * UT: 2015-1-29 12:18 by RTH
 */
namespace Home\Controller;
use Think\Controller;
use Think\Verify;

class FindpwdController extends BaseController{

    public function __construct(){
        parent::__construct($login = false);
        layout('layout_new');
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

    //密码找回 第一步 填写用户名或手机或邮箱
    public function pwd_username(){
        $this->main = '/Public/meetelf/home/js/build/find_pwd.js';
        $this->css[] = 'meetelf/css/login.css';
        $this->title = L('_FORGET_USER_');

        $this->show();
    }
    public function ajax_check_username(){
        $username = I('post.username');
        if(preg_match('/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/',$username)){
            $user_info = M('User')->where(array('email' => $username,'is_del' => '0','email_verify' => '1'))->find();
            if($user_info){
                session('user_type',1);//用户名类型为邮箱 1邮箱 0手机
                session('username',$username);//用户名
                echo 'true';
                exit;
            }else{
                echo 'false';
                exit;
            }
                //发送邮件校验码
        }else if(preg_match('/^1\d{10}$/',$username)){
            if(strlen($username) == 11){
                $user_info = M('User')->where(array('mobile' => $username,'is_del' => '0'))->find();
                if($user_info){
                    session('user_type',0);//用户名类型为邮箱 1邮箱 0手机
                    session('username',$username);//用户名
                    echo 'true';
                    exit;
                }else{
                    echo 'false';
                    exit;
                }
            }
        }else{
            echo 'false';
            exit;
        }
    }

    //密码找回 修改数据库
    public function pwd_pwd(){
        $this->main = '/Public/meetelf/home/js/build/up_pwd.js';
        $this->css[] = 'meetelf/css/login.css';
        $this->title = L('_FORGET_USER_');

        $user_model = D('User');
        $user_type = I('get.user_type');//获取用户名类型  0为手机号  1为邮箱
        $key = I('get.k');//如果k有值   则是邮件点击过来的
        if($user_type == ''){
            $this->error('请先填写用户名',U('Findpwd/pwd_username'));
        }

        $username = I('post.username');//取post用户名的值给到第二个页面的隐藏域中

        if($key != ''){//如果是邮件点过来的话，做邮件key值验证
            $email_info = M('CheckEmail')->where(array('key' => $key))->find();
            $user_info = $user_model->where(array('email' => $email_info['email'],'email_verify' => '1'))->find();
            $username = $user_info['email'];
        }
        if(I('post.password')){
            //Model验证
            if(!$user_model->create()){
                exit($this->error($user_model->getError()));
            }
            //Model验证
            if($user_type == 1){//检查用户名是否是邮箱
                $user_info = $user_model->where(array('email'=>$username,'email_verify' => '1'))->find();
            }else{
                $user_info = $user_model->where(array('mobile'=>$username))->find();
            }
            $data['password'] = md5(I('post.password'));
            $data['updated_at'] = time();
            // 更新数据库
            $user_res = $user_model->where(array('guid' => $user_info['guid']))->save($data);
            if($user_res){
                $this->success("<p style='color: #009900;'>".L('_PWD_UPDATE_SUCCESS_')."<i class='fa fa-child'></i></p>",U('Auth/login',array(),true,true));
                exit();
            }else{
                $this->error('密码修改失败');
                exit();
            }
        }
        $this->assign('user_type',$user_type);
        $this->assign('username',$username);
        $this->show();
    }

    //ajax发送校验码
    public function ajax_send_code(){
        $user_type = session('user_type').'';//获取用户名类别
        $username = session('username');//获取用户名信息

//        var_dump($username,$user_type);
        if($user_type == '' || $username == ''){
            echo 'false';exit();
        }
        $time = time();
        $code = get_mobile_code();

        $data['guid'] = create_guid();
        $data['created_at'] = $time;
        $data['updated_at'] = $time;
        $data['status'] = 0;
        $data['code'] = $code;
        $data['type'] = 1;//忘记密码
        $data['key'] = md5($username.$code);//key   手机号+校验码或者邮箱+校验码以后md5
        $data['expired_at'] = $time+1800;//30分钟有效

        if($user_type == '0'){//手机用户名
            $data['mobile'] = $username;
            $res = M('CheckMobile')->data($data)->add();
            if($res){
                send_sms(C('CODE_TYPE.site_find_password'), $username, array($code, 30), array('type' => 1));//发送验证码
                $this->ajax_response(array('status' => C('ajax_success'), 'msg' => L('_SEND_SUCCESS_')));
            }
        }else{
            $data['email'] = $username;
            $res = M('CheckEmail')->data($data)->add();

            if($res){
                $this->assign('content',L('_UPDATE_PWD_'));
                $this->assign('k',$data['key']);
                $this->assign('user_type',1);
                $content = $this->fetch('_email_notice');
                $email_res = send_email($username,L('_APP_NAME_'),L('_CLICK_UPDATE_PWD_'),$content);
                if($email_res['status'] != 'success'){
                    send_email($username,L('_APP_NAME_'),L('_CLICK_UPDATE_PWD_'),$content);
                }
                $this->ajax_response(array('status' => C('ajax_success'), 'msg' => L('_SEND_SUCCESS_')));
            }
        }
    }

    //ajax验证验证码

    public function ajax_check_verify(){
        $verify = new Verify();
        $code = I('post.verify');
        if($verify->check($code)){
//            echo 'true';
            $data['status'] = 'ok';
            $this->ajaxResponse($data);
//            exit();
        }else{
//            echo 'false';
            $data['status'] = 'ko';
            $this->ajaxResponse($data);
//            exit();
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

    //检查session，判断查看页是否正确
//    public function check_session(){
//        if(!session('username')){
//            $this->error('请先填写用户名',U('Findpwd/pwd_username'));
//        }
//    }

    //检查用户名是否是邮件
    public function check_username_email(){
        $username = I('post.username');
        if(preg_match('/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/',$username)){
                $res = M('User')->where(array('email' => $username))->find();
                if(!empty($res)){
                    echo 'true';
                    exit;
                }else{
                    echo 'false';
                    exit();
                }
            }else{
                echo 'flase';
                exit;
            }
    }
}
