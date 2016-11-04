<?php
namespace Signin\Controller;

use Think\Controller;

/**
 * API基控制器
 *
 * CT: 2014-09-19 17:00 by YLX
 *
 */
class BaseController extends Controller
{
    /**
     * action前置操作
     * 
     * ct: 2014-09-24 11:15 by ylx
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取当前用户session信息
     * @return mixed
     */
    public function get_auth_session()
    {
        return session(C('auth_session_name'));
    }


    /**
     * 清空当前用户session信息
     * @return mixed
     */
    public function empty_auth_session()
    {
        cookie(C('user_device_uniqueid_name'), null);
        return session(C('auth_session_name'), null);
    }

}