<?php
namespace Home\Controller;

use Think\Controller;
use Org\Api\YmPush;

/**
 * Home模块基控制器
 * CT: 2014-09-19 15:00 by YLX
 *
 */
class BaseHomeController extends BaseController
{
    protected $time;
    
    /**
     * 魔术方法
     *
     * CT: 2014-09-28 15:37 by YLX
     */
    public function __construct()
    {
        parent::__construct();
        $this->check_login();
        $auth = $this->get_auth_session();
        $this->assign('auth', $auth);
        $this->assign('token', $auth['guid']);

        // 检查token是否一致
        if(CONTROLLER_NAME == 'Index' && empty($_GET['token'])) { // 若为首页无token时，自动跳转
            $this->redirect('Index/index');
        }
        $this->check_token($auth['guid']);
        $this->time = time();
    }

    /**
     * 检查token是否是当前用户的token
     * @param $current_token
     * CT: 2014-12-24 16:00 BY YLX
     */
    public function check_token($current_token) {
        $post_token = I('post.token');
        $get_token = I('get.token');
        $token = !empty($get_token) ? $get_token : $post_token;

        if(ACTION_NAME == 'qrcode' || ((CONTROLLER_NAME == 'Pay' || CONTROLLER_NAME == 'Payment' || CONTROLLER_NAME == 'Info') && (ACTION_NAME == 'notify_url' || ACTION_NAME == 'return_url' || ACTION_NAME == 'dopay' || ACTION_NAME == 'myorder'))){
            return true;exit();
        }
        if(empty($token) || (!empty($token) && $token != $current_token)) {
            if(IS_AJAX) {
                $this->ajaxReturn(array('status'=>'ko', 'msg'=>'帐号登陆失效, 请刷新当前页面.'));
            } else {
                if(IS_AJAX) {
                    $this->ajaxReturn(array('status'=>'ko', 'msg'=>'帐号登陆失效, 请刷新当前页面.'));
                } else {
                    $session_auth = $this->get_auth_session();
                    if(empty($session_auth)) {
                        $url = U('Auth/login');
                    } else {
                        $url = U('Index/index');
                    }
                    $this->error('页面无法找到', $url);
                }
            }
        }
    }


    /**
     * 检查用户是否已登陆
     *
     * CT: 2014-09-28 15:37 by YLX
     */
    public function check_login()
    {
        $session_auth = $this->get_auth_session();
		if((CONTROLLER_NAME == 'Pay' || CONTROLLER_NAME == 'Payment' || CONTROLLER_NAME == 'Info') && (ACTION_NAME == 'notify_url' || ACTION_NAME == 'return_url' || ACTION_NAME == 'dopay' || ACTION_NAME == 'myorder')){
			return true;exit;
		}
        if (empty($session_auth)){
            $this->auto_login();
//                 $this->error('请先登录1', U('Auth/login'));
//                 exit();
        }
    }

    /**
     * 记住我自动登录
     *
     * CT: 2014-10-13 11:16 by YLX
     */
    public function auto_login()
    {
        if (!isset($_COOKIE[C('REMEMBER_KEY')])) {
//            $this->error('请先登录.', U('Auth/login'));exit();
            $this->redirect('Auth/login');
        }

        list($token, $userGuid, $ip) = explode(':', $_COOKIE[C('REMEMBER_KEY')]);

        //$res = $this->check_remember($token, $userGuid, $ip);
		$res = false;

        if (!$res) {
//            $this->error('请先登录.', U('Auth/login'));exit();
            $this->redirect('Auth/login');
        }

        // cookie信息正确, 执行登录操作
        $userInfo = D('User')->where(array('guid' => $userGuid))->find();
        $this->opration_after_login($userInfo);
    }

    /**
     * Ueditor图片上传插件
     *
     * CT: 2014-11-24 17:07 by ylx
     */
    public function ueditor(){
        $data = new \Org\Util\Ueditor();
        echo $data->output();
    }
    
    /**
     * 获取VIP信息
     *
     * CT: 2014-12-15 15:40 by QXL
     */
    public function get_vip_info(){
        $auth = $this->get_auth_session();
        return C($auth['vip']);
    }
}
