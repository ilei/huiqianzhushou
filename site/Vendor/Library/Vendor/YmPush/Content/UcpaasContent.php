<?php 
vendor('YmPush.Content.Content');

class UcpaasContent extends Content{


	/**
	 * 继承父类方法 具体参数说明参看父类函数 
	 **/ 

	public function getContent($type, $msg_data = array(), $ext = array()){
		//不同的类型 对应不同的函数 生成不容的内容信息
		$callback = 'get' . ucfirst($type['hook_prefix']) . 'Content';	
		return $this->$callback($msg_data, $ext);
	}

	/**
	 * api验证手机号验证码发送内容 
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return false or array 
	 **/ 

	public function getSmsApiVerMobileContent($msg_data, $ext = array()){
		list($code, $expire) = $msg_data;
		if(!$code){
			return false;	
		}		
		$expire = $expire ? intval($expire) : 30;
		$template_id = isset($ext['template_id']) ? $ext['template_id'] : '8894';
		return array($template_id, array($code, $expire));
	}

	/**
	 * web邀请注册验证码发送内容 
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return false or array 
	 **/ 

	public function getSmsSiteInvRegContent($msg_data, $ext = array()){
		list($code, $expire) = $msg_data;
		if(!$code){
			return false;	
		}		
		$expire = $expire ? intval($expire) : 30;
		$template_id = isset($ext['template_id']) ? $ext['template_id'] : '8693';
		return array($template_id, array($code, $expire));
	}

	/**
	 * web找回密码验证码发送内容 
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return false or array 
	 **/ 

	public function getSmsSiteFindPwdContent($msg_data, $ext = array()){
		list($code, $expire) = $msg_data;
		if(!$code){
			return false;	
		}		
		$template_id = isset($ext['template_id']) ? $ext['template_id'] : '8695';
		return array($template_id, array($code));
	}

	/**
	 * api找回密码验证码发送内容 
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return false or array 
	 **/ 

	public function getSmsApiFindPwdContent($msg_data, $ext = array()){
		list($code, $expire) = $msg_data;
		if(!$code){
			return false;	
		}		
		$template_id = isset($ext['template_id']) ? $ext['template_id'] : '8695';
		return array($template_id, array($code));
	}

}
