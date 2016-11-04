<?php
/**
 * 大后台登录
 * CT: 2014-12-02 10:50 by QXL
 * ut: 2015-06-12 10:26 by ylx
 */
namespace Admin\Controller;

use Think\Controller;

class AuthController extends Controller
{
    /**
     * 检查用户是否已经登录
     */
    private function _check_login()
    {
        $session_auth = session('admin::auth');
        if(!empty($session_auth)) {
            $this->redirect('Index/index');
        }
    }
    /**
     * show登录页面
     * CT: 2014-12-02 10:50 by QXL
     */
    public function login()
    {
        $this->_check_login();
        $this->display();
    }

    /**
     * Ajax登录验证
     * CT: 2014-12-02 10:50 by QXL
     */
    public function ajaxLogin()
    {
        if (IS_AJAX) {
            $username = I('post.account');
            $password = md5(I('post.password'));

            if ($username == C('SUPER_ADMIN') && $password == C('SUPER_PASSWORD')) {
                $type  = 'super';
                $login = true;
            }
            if ($username == C('ADMIN_USERNAME') && $password == C('ADMIN_PASWORD')) {
                $type  = 'admin';
                $login = true;
            }

            if ($login == true) {
                session('admin::auth', array('username' => $username, 'type' => $type));
                $this->ajaxReturn(array('code' => '200'));
            } else {
                $this->ajaxReturn(array('code' => '201', 'Msg' => '账号或密码不正确密码'));
            }
        } else {
            $this->error('非法请求');
        }
    }


    /**
     * 退出登录
     * CT: 2014-12-12 15:38 by QXL
     */
    public function loginout()
    {
        if (session('admin::auth')) {
            session('admin::auth', null);
            $this->redirect('Auth/login');
        } else {
            $this->error('您还未登录', U('Auth/login'));
        }
    }
}
