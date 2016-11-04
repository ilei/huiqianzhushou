<?php
namespace Mobile\Controller;

use       Think\Controller;

/**
 *
 * 登录控制器
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/
class AuthController extends BaseController
{

    public function __construct()
    {

        parent::__construct();
        //使用layout
        layout('layout');
    }

    /**
     * 登录
     **/
    public function login()
    {
        $this->title = L('_LOGIN_TITLE_');
        $this->main  = '/Public/mobile/js/login.js';
        if (IS_AJAX) {
            $username = trim(I('post.username'));
            $password = I('post.password');
            if (!$username) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_USER_NOT_EMPTY_')));
            }
            if (!$password) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_PASSWORD_NOT_EMPTY_')));
            }
            $where = " (email = '{$username}' AND email_verify='1') OR mobile = '{$username}'";
            $user = M('User')->where($where)->find();
            if (!$user || ($user['password'] != md5($password))) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_USERNAME_OR_PASSWORD_ERROR_')));
            }
            $refer = $this->getReferer();
            $u     = urldecode(substr($refer, (strpos($refer, '?u=')+3)));
            $redirect = $u ? $u : $this->getReferer();
            $this->operation_after_login($user);
            $redirect = ($redirect && (!preg_match('/(login|register|logout)/i', $redirect))) ? $redirect : U('Index/index', '', true, true);
            $this->set_remember($user['guid']);
            $this->ajax_response(array('status' => C('ajax_success'), 'url' => $redirect));
        }
        $this->assign('login', true);
        $this->show();
    }


    /**
     * 注册
     *
     * @access public
     * @param  void
     * @return json
     **/

    public function register()
    {
        $this->title = L('_REGISTER_TITLE_');
        $this->main  = '/Public/mobile/js/register.js';
        if (IS_AJAX) {
            $params = I('post.');
            if (!validate_data($params, 'mobile')) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_MOBILE_EMPTY_')));
            }
            if (!validate_data($params, 'email')) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_EMAIL_EMPTY_')));
            }
            if (!validate_data($params, 'password')) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_PASSWORD_EMPTY_')));
            }
            if (!validate_data($params, 'code')) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_CODE_EMPTY_')));
            }
            $res = $this->check_data($params);
            if (!$res) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => $this->_error));
            }
            $level = M('grade_level')->order('sort ASC')->find();
            $time = time();
            $user = array(
                'guid' => create_guid(),
                'email' => trim($params['email']),
                'mobile' => trim($params['mobile']),
                'password' => md5($params['password']),
                'vip' => $level['guid'],
                'created_at' => $time,
                'updated_at' => $time,
                'moblie_verify' => C('default_ok_status'),
            );
            $res = M('User')->add($user);
            if ($res) {
                $model_user_attr_auth = M('UserAttrAuth');
                $auth_data = array(
                    'guid' => create_guid(),
                    'user_guid' => $user['guid'],
                    'login_num' => 1,
                    'last_login_at' => $time,
                    'last_login_ip' => get_client_ip(),
                    'updated_at' => $time,
                    'created_at' => $time,
                );
                if ($model_user_attr_auth->add($auth_data)) {

                    // 存储用户附加信息表
                    $info_data = array(
                        'guid' => create_guid(),
                        'user_guid' => $user['guid'],
                        'updated_at' => $time,
                        'created_at' => $time,
                    );
                    M('UserAttrInfo')->add($info_data);
                    //发送邮件
                    $this->assign('email', $user['email']);     //登录邮箱
                    $this->assign('phone', $user['mobile']);    //登录手机
                    $content = $this->fetch('email_notice');
                    $content_stb = $this->fetch('email_notice_stb');
                    $email_result = send_email($user['email'], L('_APP_NAME_'), L('_CONGREAT_SUCCESS_'), $content);
                    $email_stb = send_email('service@yunmai365.com', L('_APP_NAME_'), L('_NEW_USER_REGISTER_SUCCESS_'), $content_stb);
                    // 发送确认邮件
                    if ($email_result['status'] != 'success') { // 邮件发送不成功,就再发一次
                        send_email($user['email'], L('_APP_NAME_'), L('_CONGREAT_SUCCESS_'), $content);
                    }
                    if ($email_stb['status'] != 'success') { // 邮件发送不成功,就再发一次
                        send_email('service@yunmai365.com', L('_APP_NAME_'), L('_NEW_USER_REGISTER_SUCCESS_'), $content_stb);
                    }
                    D('UserAccount', 'Logic')->add_msg_email_nums($user['guid']);
                    $this->operation_after_login($user);
                    $redirect = $this->getReferer();
                    $redirect = ($redirect && (preg_match('/(login|register|meetelf)/i') !== false)) ? $redirect : U('Index/index', '', true, true);
                    $this->set_remember($user['guid']);
                    $this->ajax_response(array('status' => C('ajax_success'), 'url' => $redirect));
                } else {
                    $rollback = $model_user->where(array('guid' => $user['guid']))->delete();
                }
            } else {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => C('_PARAM_ERROR_')));
            }
        }
        $this->show();
    }

    /**
     * 检测数据是否正确
     *
     * @access public
     * @param  array $data
     * @return boolean
     **/

    private function check_data($data)
    {
        if (!((strlen($data['mobile']) == 11) && (preg_match('/1[3458]{1}\d{9}/', $data['mobile']) !== false))) {
            $this->_error = L('_EMAIL_FORMAT_ERROR_');
            return false;
        }
        if (preg_match('/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/i', $data['mobile'])) {
            $this->_error = L('_EMAIL_FORMAT_ERROR_');
            return false;
        }
        //验证手机验证码
        $key = md5('mobile:auth:ajax_send_code:' . $permanent_id . $data['mobile']);
        $code = mobile_code($key);
        if ($code != $data['code']) {
            $this->_error = L('_CODE_ERROR_');
            return false;
        }
        if (!(strlen($data['password']) >= 6 && strlen($data['password']) <= 18)) {
            $this->_error = L('_PASSWORD_LENGTH_ERROR_');
            return false;
        }
        $mobile = M('User')->where("mobile = '{$data['mobile']}'")->find();
        if ($mobile) {
            $this->_error = L('_MOBILE_EXIST_');
            return false;
        }
        $email = M('User')->where("email = '{$data['email']}' and email_verify='1'")->find();
        if ($email) {
            $this->_error = L('_EMAIL_EXIST_');
            return false;
        }
        return true;
    }


    /**
     * 注册时相关ajax检查
     *
     * CT: 2014-09-17 17:07 by wangleiming
     */
    public function check()
    {
        $type = trim(I('get.type'));
        switch ($type) {
            case 'email':
                $email = I('post.email');
                //$res   = D('User')->getFieldByEmail($email, 'guid');
                $res = D('User')->where(array('email' => $email, 'email_verify' => '1'))->find();//重新获取已校验的Email
                echo empty($res) ? 'true' : 'false';
                break;
            case 'mobile':
                $mobile = I('post.mobile');
//			$res    = D('User')->getFieldByMobile($mobile, 'guid');
                $res = D('User')->where(array('mobile' => $mobile))->find();
                echo empty($res) ? 'true' : 'false';
                break;
            case 'username':
                $username = I('post.username');
                $where = "((email= '{$username}' AND email_verify='1') OR mobile = '{$username}') AND is_lock=0 AND is_del=0";
                $res = D('User')->find_one($where);
                echo !empty($res) ? 'true' : 'false';
                break;
            default:
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
                break;
        }
        exit;
    }

    /**
     * 发送验证码
     *
     * @access public
     * @param  void
     * @return json
     **/

    public function ajax_send_code()
    {
        if (IS_AJAX) {
            $mobile = I('post.mobile');

            //校验是否存在重复的手机号
            if( M('User')->where(array('mobile'=>$mobile))->count()>0){
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => 'Duplicate Mobile'));
                return;
            }





            $key = md5('mobile:auth:ajax_send_code:' . $permanent_id . $mobile);
            $res = request_nums_limit($key, 5, 24 * 3600);
            if (!$res) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_REQUEST_TOO_MUCH_')));
            }
            $code = kookeg_get_mobile_code();
            mobile_code($key, $code);
            send_sms(C('CODE_TYPE.api_verify_mobile'), $mobile, array($code, 30), array('type' => 1));
            $this->ajax_response(array('status' => C('ajax_success'), 'msg' => L('_SEND_SUCCESS_')));
        }else {
            $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_INVALID_REQUEST_')));
        }
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
        $this->redirect('Mobile/Auth/login');
    }
}
