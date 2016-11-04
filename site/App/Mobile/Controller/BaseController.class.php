<?php
namespace Mobile\Controller;
use Think\Controller;

/**
 * Class BaseController
 * @package Mobile\Controller
 *
 * CT: 2015-02-04 16:23 by ylx
 */
class BaseController extends Controller{

	protected $title  = '酷客会签';

	protected $module = array(); 

	// HTTP提交来的数据
	protected $_request_params = array();

	// 通过token获取的用户信息
	protected $_userinfo = array();

	// HTTP提交方法
	protected $_method;

	public function __construct($login = true) {
		$this->_method  =  strtolower(REQUEST_METHOD);
		parent::__construct();
        if($login){
            $this->check_login();
        }
	}

	public function getReferer(){
		return $_SERVER['HTTP_REFERER'];
	}


	/**
	 *
	 * 页面展示 
	 * 
	 * @access public 
	 * @param  string $html 
	 * @return void 
	 **/ 

	public function show($html = ''){
        if(!$this->main){
            $this->main = '/Public/mobile/js/common.js';	
        }
        if(APP_DEBUG){
            if($this->main && !strpos($this->main, 'act.add')){
                $name = substr($this->main, strrpos($this->main, '/')+1);
                $path = substr($this->main, 0, strrpos($this->main, '/'));
                $this->main = $path . '/min/' . $name; 
            }
            foreach($this->css as $key => $value){
                $this->css[$key] = substr($value, 0, -4) . '.min.css'; 
            }
        }else{
            $this->module[] = $this->main . '.js'; 
            $this->main = '/Public/mobile/js/main'; 
        }
		$this->assign(
			array(
				'main' => $this->main,	
				'title'  => $this->title,
                'auth'   => $this->get_login_user(),
                'back_url' => urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']),
			)
		);	
		$this->display($html);
	}

	/**
	 * ajax数据返回 
	 *
	 * @access public 
	 * @param  array  $data 
	 * @return json数据 
	 **/ 

	public function ajax_response($data){
		exit(json_encode($data));
	}

	/**
	 * 登陆后保存Session, 记录登陆次数, 登陆时间等操作
	 *
	 * @access public 
	 * @param  array  $userInfo
	 * CT: 2015-08-26 11:00 BY wangleiming 
	 */

	public function operation_after_login($userInfo){

		// 获取登录用户社团信息
		if (empty($userInfo)) {
			return false;
		}
		// 存储相关session
		$auth = array(
			'guid'     => $userInfo['guid'],
			'email'    => $userInfo['email'],
			'mobile'   => $userInfo['mobile'],
			'vip'      => $userInfo['vip'],
			'nickname' => $userInfo['nickname'],
			'photo'    => $userInfo['photo'],
		);

		M('UserAttrAuth')->where(array('user_guid' => $userInfo['guid']))->save($data);
        $userAttr = M('UserAttrInfo')->where("user_guid = '{$userInfo['guid']}'")->find();
        $account  = M('UserAccount')->where(array('account_guid' => $userInfo['guid']))->find();
        $auth['nickname'] = validate_data($userAttr, 'nickname', '');
        $auth['photo']    = validate_data($userAttr, 'photo', '');
        $auth['realname'] = validate_data($userAttr, 'realname', '');
        $auth['msg_nums'] = validate_data($account, 'msg_nums', 0);
        $auth['email_nums'] = validate_data($account, 'email_nums', 0);
        $auth['balance']  = yuan_to_fen($account['balance'], false);
		session('auth', $auth);
        $this->set_remember($auth['guid']);
		return true;
	}

	/**
	 * 保存remember me cookie信息
	 * 
	 * CT: 2014-10-13 11:35 by wangleiming 
	 */

	public function set_remember($guid){
		$token  = md5(uniqid(rand(), TRUE));
		$expire = time() + C('REMEMBER_EXPIRE', null, '2592000');
		// 保存cookie
		setcookie(C('REMEMBER_KEY'), $token . ':' . $guid, $expire, '/');
		// 保存cookie信息到数据库
		$data = array('remember_token' => $token, 'remember_expire' => $expire);
		M('User')->where(array('guid'  => $guid))->data($data)->save();
	}
   
	/**
     * 检查用户是否已登陆
     *
     * CT: 2015-09-28 15:37 by wangleiming 
     **/

    public function check_login(){
        $filter = array('Auth', 'Pay');
        return true;
        if(in_array(CONTROLLER_NAME, $filter)){
            return true;
        }
        if(preg_match('/^\/activity\/signup_user\/aid\/([\w\d]{32})/', $_SERVER['REQUEST_URI'], $match)){
            session('signup_user:aguid', $match[1]);
        }
        if(!(CONTROLLER_NAME == 'Activity' && ACTION_NAME == 'signup_user')){
            return true;
        }
        $session_auth = $this->kookeg_auth_data();
        if (empty($session_auth)) {
            $this->auto_login();
        }
		return true;
    }

    /**
     * 记住我自动登录
     *
     * CT: 2015-10-13 11:16 by wangleiming 
     **/

    public function auto_login(){
        if (!isset($_COOKIE[C('REMEMBER_KEY')])) {
        	$this->redirect('Mobile/Auth/login');
        }

        list($token, $user_guid) = explode(':', $_COOKIE[C('REMEMBER_KEY')]);
        $res = $this->check_remember($token, $user_guid);
        if (!$res) {
        	$this->redirect('Mobile/Auth/login');
        }
        // cookie信息正确, 执行登录操作
        $user = D('User')->where(array('guid' => $user_guid))->find();
        $this->operation_after_login($user);
    }

    /**
     * 检查用户是否选择记住我
     *
     * CT: 2015-10-13 10:50 by wangleiming 
     **/

    public function check_remember($token, $user_guid){
        if (!ctype_alnum($user_guid) || !ctype_alnum($token)){
            return false;
        }
        $user = M('User')->where(array('guid'=>$user_guid))->find();
        if (empty($user)) {
            return false;
        }
        if ($user['remember_token'] != $token || time() > $user['remember_expire']) {
            return false;
        }
        return true;
	}

    public function kookeg_auth_data(){
        return session('auth');
    }

	/**
	 * 跳转的错误页
	 * @param $msg
	 * @param string $countdown
	 * CT: 2015-02-04 17:01 BY YLX
	 */
	public function _show_error($msg, $countdown='')
	{
		if(!empty($countdown)){
			$this->assign('countdown', $countdown);
		}
		$this->assign('status', 'error');
		$this->assign('title', $msg);
		$this->display('Tpl/placeholder');exit();
	}

	/**
	 * 检查用户token
	 * CT: 2014-11-13 10:20 by YLX

	public function check_token()
	{
		$this->_request_params = $params = I($this->_method.'.');
		$token = $params['token'];
		if(empty($token)) {
			$token = I('get.token');
		}
		if(empty($token)) {
			$this->_show_error('您的权限不足, 无法参加该活动');
		}

		$model_user_device = D('UserDevice');
		$token_expire = C('TOKEN_EXPIRE');
		// 检查token信息是否生成
		//$token_info = S($token.':user_device');
		if(empty($token_info)) {
			$token_info = $model_user_device->getTokenInfoByToken($token);
			if(empty($token_info)) {
				$this->_show_error('您的权限不足, 无法参加该活动');
			}
			//S($token.':user_device', $token_info, $token_expire);
		}

		// 检查token是否超时, 限制时间为15天
		if(time() > $token_info['last_login']+$token_expire){
			$this->_show_error('登录已超时，请重新登录。');
		}

		//检查当前设备是否在线
		if($token_info['status'] != '1'){
			$this->_show_error('登录已超时，请重新登录。');
		}
		//if($redis_user_info = S($token.':user_info')){
		//	$this->_userinfo = $redis_user_info;
		//}else{
			// 检查对应用户是否存在
			$model_user = D('User');
			$this->_userinfo = $model_user->getUserInfo($token_info['user_guid']);
		//}

		if(empty($this->_userinfo)) {
			$this->_show_error('登录已超时，请重新登录。');
		} else {
			//S($token.':user_info', $this->_userinfo, $token_expire);
			return true;
		}
	}
*/
	/**
	 * 在用户信息 和 用户报名页检查
	 * CT: 2014-11-13 10:20 by YLX

	public function check_token_in_userpage()
	{
		$this->_request_params = $params = I($this->_method.'.');
		$token = $params['token'];
		if(empty($token)) {
			$token = I('get.token');
		}
		if(empty($token)) {
			$this->redirect('Activity/view', array('aid' => $params['aid']));
		}

		$model_user_device = D('UserDevice');
		$token_expire = C('TOKEN_EXPIRE');
		// 检查token信息是否生成
		//$token_info = S($token.':user_device');
		if(empty($token_info)) {
			$token_info = $model_user_device->getTokenInfoByToken($token);
			if(empty($token_info)) {
				$this->redirect('Activity/view', array('aid' => $params['aid']));
			}
			//S($token.':user_device', $token_info, $token_expire);
		}

		// 检查token是否超时, 限制时间为15天
		if(time() > $token_info['last_login']+$token_expire){
			$this->redirect('Activity/view', array('aid' => $params['aid']));
		}

		//检查当前设备是否在线
		if($token_info['status'] != '1'){
			$this->redirect('Activity/view', array('aid' => $params['aid']));
		}
		//if($redis_user_info = S($token.':user_info')){
		//	$this->_userinfo = $redis_user_info;
		//}else{
			// 检查对应用户是否存在
			$model_user = D('User');
			$this->_userinfo = $model_user->getUserInfo($token_info['user_guid']);
		//}

		if(empty($this->_userinfo)) {
			$this->redirect('Activity/view', array('aid' => $params['aid']));
		} else {
			//S($token.':user_info', $this->_userinfo, $token_expire);
			return true;
		}
	}
	 */

	/**
	 * 若查找action为空, 则返回404页面
	 */
	public function _empty() {
		header('HTTP/1.0 404 Not Found');
		$this->display('Tpl/404');
	}

    /**
     * 返回登录用户信息
     *
     * @access public 
     * @param  void 
     * @return array 
     **/ 

    public function get_login_user(){
        $auth = $this->kookeg_auth_data();
        if(!$auth && isset($_COOKIE[C('REMEMBER_KEY')]) && $_COOKIE[C('REMEMBER_KEY')]) {
            list($token, $userGuid, $ip) = explode(':', $_COOKIE[C('REMEMBER_KEY')]);
            $condition = array('guid' => $userGuid, 'auto_token' => $token, 'auto_login' => 1);
            $userInfo  = M('User')->where($condition)->find();
            if(!$userInfo){
                return false;
            }
            // 存储相关session
            $auth = array(
                'guid'   => $userInfo['guid'],
                'email'  => $userInfo['email'],
                'mobile' => $userInfo['mobile'],
                'vip'    => $userInfo['vip'],
            );
            $userAttr = M('UserAttrInfo')->where("user_guid = '{$userInfo['guid']}'")->find();
            $account  = M('UserAccount')->where(array('account_guid' => $userInfo['guid']))->find();
            $auth['nickname']   = validate_data($userAttr, 'nickname', '');
            $auth['photo']      = validate_data($userAttr, 'photo', '');
            $auth['realname']   = validate_data($userAttr, 'realname', '');
            $auth['msg_nums']   = validate_data($account, 'msg_nums', 0);
            $auth['balance']    = yuan_to_fen($account['balance'], false);
            $auth['email_nums'] = validate_data($account, 'email_nums', 0);
            session('auth', $auth);
        }
        return $auth;
    }
}
