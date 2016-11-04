<?php
namespace Home\Controller;

use Home\Controller\BaseController;
use Think\Verify;

/**
 * 注册登陆
 **/

class AuthController extends BaseController{

    public function __construct(){
        parent::__construct(false);
    }

    /**
     * 登录
     *
     * @access public 
     * @param  void 
     * @return void  
     **/

    public function login(){

        $this->css[]    = 'meetelf/home/css/home.auth.login.css';
        //加载自定义js文件
        $this->main = '/Public/meetelf/home/js/build/home.auth.login.js';
        $this->title    = L('_LOGIN_TITLE_');
        $model_user     = D('User');
        if (IS_POST) {
            $this->ajax_request_limit('login::');
            $username = trim(I('post.username'));
            if (!$username) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_USER_NOT_EMPTY_')));
            }
            $password = md5(I('post.password'));
            if (!$username) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_PASSWORD_NOT_EMPTY_')));
            }
            $where = "mobile='{$username}'";
            if(!preg_match('/^1[34578]{1}\d{9}$/', $username)){
                $email_login = true;
                $where = "email='{$username}'";
                $users = $model_user->where($where)->select();
                $user_info = $users[0];
                foreach($users as $key => $value){
                    if($value['email_verify']){
                        $user_info = $value; 
                        break;
                    } 
                }
            }else{
                $user_info = $model_user->where($where)->find();
            }

            //用户不存在
            if (!$user_info) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_USER_NOT_EXIST_')));
            }
            if($email_login && !$user_info['email_verify']){
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_EMAIL_NOT_VERIFY_')));
            }

            // 检查用户是否被禁止
            if ($user_info['is_lock'] == C('user.locked')) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_USER_BANNED_')));
            }

            //密码是否正确
            if ($user_info['password'] != $password) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_USER_PASSWORD_ERROR_')));
            }

            // 登录后操作
            $remember = I('post.remeber') ? true : false;
            $this->opration_after_login($user_info, $remember);
            $redirect = session('referer');
            $redirect = $redirect && (preg_match('/(login|register)/i') !== false) ? $redirect : U('Home/User/index');
            $this->ajax_response(array('status' => C('ajax_success'), 'url' => $redirect));
        }
        $redirect = $this->getReferer();
        if($redirect){
            session('referer', $redirect);
        }
        $this->show();
    }


    /**
     * 帐号退出登录
     *
     * @access public
     * @param  void
     * @return void
     **/

    public function logout()
    {
        if (session('auth') || $_COOKIE[C('REMEMBER_KEY')]) {
            $guid = $this->kookeg_auth_data('guid');
            $_SESSION['auth'] = array();
            setcookie(C('REMEMBER_KEY'), null, time() - 1);
            $_COOKIE[C('REMEMBER_KEY')] = null;
            session('auth', null);
            $update = array('auto_login' => C('user.not_auto_login'), 'auto_token' => '');
            M('User')->where("guid = '{$guid}'")->save($update);
        }
        $this->redirect('Auth/login');
    }

    /**
     * 用户注册
     *
     * @access public
     * @param  void
     * @return void
     **/

    public function register()
    {
        $this->css[] = 'meetelf/css/login.css';
        $this->main = '/Public/meetelf/home/js/build/home.auth.register.js';
        $this->title = L('_REGISTER_TITLE_');
        $permanent_id = $_COOKIE['__permanent_id'];
        if (IS_POST) {
            //限制注册次数
            $key = md5('auth:register:' . $permanent_id);
            $res = request_nums_limit($key, 10);
            if (!$res) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_REGISTER_TOO_MUCH_')));
            }

            $params = I('post.');

            //是否同意条款
            if (!validate_data($params, 'agree')) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_AGREE_ERROR_')));
            }

            //验证手机验证码
            $key = md5('auth:ajax_send_code:' . $permanent_id . $params['phone']);
            $code = mobile_code($key);
            if ($code != $params['verify']) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_CODE_ERROR_')));
            }

            // 保存用户
            $model_user = D('User');
            $time = time();
            $token = md5($params['email'].$time); //创建用于激活识别码
            $token_exptime = time()+60*60*24;//过期时间为24小时后
            $data = array(
                'guid' => create_guid(),
                'email' => trim($params['email']),
                'mobile' => trim($params['phone']),
                'password' => trim($params['password']),
                're_password'=>trim($params['password']),
                'updated_at' => $time,
                'created_at' => $time,
                'moblie_verify' => 1,
                'email_token'     => $token,
                'token_exptime'   => $token_exptime,
            );
            $result = $model_user->create($data);
            if (!$result) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => $model_user->getError()[0]));
            } else {
                $data['password'] = md5($data['password']);
                if ($res = $model_user->add($data)) {
                    // 存储用户附加信息表
                    $info_data = array(
                        'guid' => create_guid(),
                        'user_guid' => $data['guid'],
                        'updated_at' => $time,
                        'created_at' => $time,
                    );
                    M('UserAttrInfo')->add($info_data);
                    //发送邮件
                    $this->assign('email', $data['email']);     //登录邮箱
                    $this->assign('phone', $data['mobile']);    //登录手机
                    $this->assign('token', $token);             //认证码
                    $content = $this->fetch('email_notice');
                    $content_stb = $this->fetch('email_notice_stb');
                    $email_result = send_email($data['email'], L('_APP_NAME_'), L('_CONGREAT_SUCCESS_'), $content);
                    $email_stb = send_email('service@kookeg.com', L('_APP_NAME_'), L('_NEW_USER_REGISTER_SUCCESS_'), $content_stb);
                    // 发送确认邮件
                    if ($email_result['status'] != 'success') { // 邮件发送不成功,就再发一次
                        send_email($data['email'], L('_APP_NAME_'), L('_CONGREAT_SUCCESS_'), $content);
                    }
                    if ($email_stb['status'] != 'success') { // 邮件发送不成功,就再发一次
                        send_email('service@kookeg.com', L('_APP_NAME_'), L('_NEW_USER_REGISTER_SUCCESS_'), $content_stb);
                    }

                    D('UserAccount', 'Logic')->add_msg_email_nums($data['guid']);
                    M('CodeCheck')->where("guid = '{$key}'")->save(array('status' => 0));
                    $this->ajax_response(array('status' => C('ajax_success'), 'url' => U('Home/Auth/registerSuccessRedirect')));
                } else {
                    $rollback = $model_user->where(array('guid' => $data['guid']))->delete();
                    if ($rollback) {
                        $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_DEFAULT_ERROR_')));
                    }
                }
            }
        }
        $this->show();
    }

    /**
     * 用户Ajax注册成功后的服务端跳转
     *
     */
    public  function registerSuccessRedirect(){
        $this->success("注册成功",U('Home/Auth/login'),3);
    }

    /**
     * 发送手机验证码
     *
     * @access public
     * @param  void
     * @return json data
     **/

    public function ajax_send_code()
    {
        $permanent_id = $_COOKIE['__permanent_id'];
        $phone = I('post.phone');
        $key = md5('auth:ajax_send_code:' . $permanent_id);
        $res = request_nums_limit($key, 5, 24 * 3600);
        if (!$res) {
            $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_REQUEST_TOO_MUCH_')));
        }
        $code = kookeg_get_mobile_code();
        $key = md5('auth:ajax_send_code:' . $permanent_id . $phone);
        mobile_code($key, $code);
        send_sms(C('CODE_TYPE.api_verify_mobile'), $phone, array($code, 30), array('type' => 1));
        $this->ajax_response(array('status' => C('ajax_success'), 'msg' => L('_SEND_SUCCESS_')));
    }

    /**
     * 验证手机验证码
     *
     * @access public
     * @param  void
     * @return json data
     **/

    public function ajax_check_code()
    {
        $permanent_id = $_COOKIE['__permanent_id'];
        $phone = I('post.phone');
        $code = I('post.verify');
        $key = md5('auth:ajax_check_code:' . $permanent_id);
        $res = request_nums_limit($key);
        if (!$res) {
            $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_REQUEST_TOO_MUCH_')));
        }
        $key = md5('auth:ajax_send_code:' . $permanent_id . $phone);
        $exist = mobile_code($key);
        echo 'true';

        exit();
    }


    public function terms()
    {
        $this->title = L('_APP_TERMS_');
        $this->show();
    }


    public function check()
    {
        $permanent_id = $_COOKIE['__permanent_id'];

        //请求次数限制
        $key = md5('check::nums::' . $permanent_id);
        $res = request_nums_limit($key, 10);
        if (!$res) {
            echo 'false';
            $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_REQUEST_TOO_MUCH_')));
        }
        $type = I('get.type');
        switch ($type) {
        case 'email':
            $email = I('post.email');
            //只获取已认证的Email
            $res = D('User')->where(array('email' => trim($email),'email_verify'=>'1','is_del' => '0'))->find();
            echo empty($res) ? 'true' : 'false';
            break;
        case 'mobile':
            $mobile = I('post.mobile');
            $res = D('User')->where(array('mobile' => trim($mobile),'is_del' => '0'))->find();
            echo empty($res) ? 'true' : 'false';
            break;
        case 'username':
            $username = trim(I('post.username'));
            $where = 'email="' . $username . '" OR mobile="' . $username . '" and is_del = 0';
            $res = D('User')->find_one($where);
            echo !empty($res) ? 'true' : 'false';
            break;
        default:
            $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            break;
        }
        exit;
    }

    //验证码
    public function verify(){
        $config = array(
            'imageW' => 140,
            'imageH' => 40,
            'fontSize' => 20,
            'length' => 4,
            'useCurve' => false
        );
        $verify = new Verify($config);
        $verify->codeSet = '0123456789';
        $verify->entry();
    }

    public function check_verify(){
        $verify = new Verify();
        $code = I('post.code');
        if ($verify->check($code)) {
            echo 'true';
        } else {
            echo 'false';
        }
        exit(1);
    }
}
