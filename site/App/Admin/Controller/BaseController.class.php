<?php
namespace Admin\Controller;

use Think\Controller;

class BaseController extends Controller{
    
    public function __construct() {
        parent::__construct();
        $this->check_login();
    }

    public function _empty() {
        header('HTTP/1.0 404 Not Found');
        $this->display('Common@Tpl/404');
    }
    
    /**
     * 检查用户是否已登陆
     *
     * CT: 2014-12-02 10:50 by QXL
     */
    public function check_login() {
        $session_auth = session('admin::auth');
        if (empty($session_auth)){
            $this->redirect('Auth/login');
        }
    }

}