<?php
namespace Home\Controller;
use 	  Home\Controller\BaseController;

/**
 * 社团帐号注册登陆
 * CT: 2014-09-15 15:00 by YLX
 */
class WeixinController extends BaseController{

	/**
	 * 微信帐号登录
	 *
	 * CT: 2014-09-15 15:00 by YLX
	 * UT: 2015-10-13 10:40 by wangleiming
	 */

	public function login(){
		$logic = D('Weixin', 'Logic');
		$state = create_guid();
		session('weixin::login::state', $state);
		$logic->get_code($state);
	}

	public function callback(){
		$state = I('get.state');
		$code  = I('get.code'); 	
		if($state != session('weixin::login::state')){
			$this->error(L('_INVALID_REQUEST_'));	
		}
		$logic  = D('Weixin', 'Logic');
	    $access = $logic->get_access_info($code);			
		$token  = $access['access_token'];
		$openid = $access['openid'];
		$user   = $logic->get_userinfo($token, $openid);
		if(isset($user['errcode'])){
			$this->error(L('_INVALID_REQUEST_'));	
		}
		$res = $this->weixin_after_login($user);
		$this->redirect('Index/index');		
	}
}
