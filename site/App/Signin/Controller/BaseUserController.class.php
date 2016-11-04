<?php
/**
 * Created by PhpStorm.
 * User: T430
 * Date: 2014/11/12
 * Time: 15:37
 *
 * 主要针对手机APP端的接口基控制器
 */

namespace Signin\Controller;


class BaseUserController extends BaseController {

    protected $_request_params = array();

    protected $user_info = array();

    public function __construct() {
        parent::__construct();

        // 验证用户是否登录
        $this->check_login();

        // 检查用户在线状态
        if(CONTROLLER_NAME == 'Index' && empty($_GET['token'])) { // 若为首页无token时，自动跳转
            $this->redirect('Index/index');
        }
        $this->check_token();
    }

    /**
     * 检查用户是否已登陆
     * CT: 2015-07-07 15:37 by YLX
     */
    public function check_login()
    {
        $session_auth = $this->kookeg_auth_data();
        if (empty($session_auth)){
            $this->redirect('Auth/login');
//             $this->error('您尚未登录，请登录。', U('Auth/login'));
             exit();
        }
    }
    
    /**
     * 检查用户token
     * CT: 2014-11-13 10:20 by YLX
     */
    public function check_token()
    {
        $session = $this->kookeg_auth_data();
        $token = $session['token'];
        if(empty($token)) {
            $this->empty_auth_session();
            $this->error(L('_RELOGIN_'), U('Auth/login'));
        }

        $post_token = I('post.token');
        $get_token = I('get.token');
        $check_token = !empty($get_token) ? $get_token : $post_token;

        // 检查URL中token与session中token是否一致
        if($check_token != $token) {
            $this->empty_auth_session();
            if(IS_AJAX) {
                $this->ajaxResponse(array('status'=>'ko', 'msg'=>L('_REFRESH_PAGE_')));
            } else {
                $this->error(L('_PAGE_OUT_'), U('Auth/login'));
            }
        }

        $token_expire = C('TOKEN_EXPIRE');
        // 检查token信息是否生成
//        $token_info = S($token.':user_device');
        if(empty($token_info)) {
            $token_info = D('UserDevice')->getTokenInfoByToken($token);
            if(empty($token_info)) {
                $this->empty_auth_session();
                $this->error(L('_RELOGIN_'), U('Auth/login'));
            }
//            else {
//                S($token . ':user_device', $token_info, $token_expire);
//            }
        }

        //检查当前设备是否在线
        if($token_info['status'] != '1'){
            $this->empty_auth_session();
            $this->error(L('_DISTANCE_LOGIN_'), U('Auth/login'));
        }

        // 检查token是否超时, 限制时间为15天
        if(time() > $token_info['last_login']+$token_expire){
            $this->empty_auth_session();
            $this->error(L('_RELOGIN_'), U('Auth/login'));
        }
        

//        if($redis_user_info = S($token.':user_info')){
//            $user_info = $redis_user_info;
//        }else{
        // 检查对应用户是否存在
            $user_info = D('SigninUser')->getUserInfo($token_info['user_guid']);
//        }
          if(!$user_info){
                $user_info = D('User')->where(array('guid' => $token_info['user_guid']))->find();
          }
        if(empty($user_info)) {
            $this->empty_auth_session();
            $this->error(L('_RELOGIN_'), U('Auth/login'));
        } else {
            $this->user_info = $user_info;
//            S($token.':user_info', $this->user_info, $token_expire);
            return true;
        }
    }

} 