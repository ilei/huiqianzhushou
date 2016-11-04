<?php 
vendor('YmPush.Content.Content');

/**
 * 亿美发送内容生成器
 **/ 

class EMayContent extends Content{


	/**
	 * 继承父类方法 具体参数说明参看父类函数 
	 **/ 

	public function getContent($type, $msg_data = array(), $ext = array()){
		$callback = 'get' . ucfirst($type['hook_prefix']) . 'Content';	
		return $this->$callback($msg_data, $ext);
	}

	/**
	 * api验证手机号验证码发送内容 
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return false or string 
	 **/ 

	public function getSmsApiVerMobileContent($msg_data, $ext = array()){
		list($code, $expire) = $msg_data;
		if(!$code){
			return false;	
		}		
		$expire = $expire ? intval($expire) : 30;
		$content = '【' . $ext['app_name'] . '】您正在进行手机验证,验证码为' . $code . ',于' . $expire . '分钟内有效。工作人员不会向您索要，请勿向任何人透露。';
		return $content;
	}

	/**
	 * web邀请注册验证码发送内容 
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return false or string 
	 **/ 

	public function getSmsSiteInvRegContent($msg_data, $ext = array()){
		list($code, $expire) = $msg_data;
		if(!$code){
			return false;	
		}		
		$expire = $expire ? intval($expire) : 30;
		$content = '【' . $ext['app_name'] . '】感谢您注册' . $ext['app_name'] . ',验证码为' . $code . ',' . $expire . '分钟内有效。工作人员不会向您索要，请勿向任何人透露。';
		return $content;
	}


	/**
	 * web找回密码验证码发送内容 
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return false or string 
	 **/ 

	public function getSmsSiteFindPwdContent($msg_data, $ext = array()){
		list($code, $expire) = $msg_data;
		if(!$code){
			return false;	
		}		
		$expire = $expire ? intval($expire) : 30;
		$content = '【' . $ext['app_name'] . '】您找回密码的验证码为' . $code . ',' . $expire . '分钟内有效。工作人员不会向您索要，请勿向任何人透露。';
		return $content;
	}

	/**
	 * api找回密码验证码发送内容 
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return false or string 
	 **/ 

	public function getSmsApiFindPwdContent($msg_data, $ext = array()){
		return $this->getSmsSiteFindPwdContent($msg_data, $ext);
	}

	/**
	 * 发送电子票
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return string 
	 **/ 

	public function getSmsTicketContent($data, $ext = array()){
		$aguid 	       = $ext['aguid'];
		$activity_name = html_entity_decode($ext['activity_name']);
		$ticket_url    = U($ext['app_url'] . '/Mobile/Activity/ticket', array('aid' => $aguid, 'iid' => $data['guid']), true, true);
		$ticket_short_url = get_short_url($ticket_url . '/source/1'); // 长度20
		return '【' . $ext['app_name'] . '】您申请参加的『' . ym_mb_substr($activity_name, 13, '..') . '』已通过审核，时间地点详见电子票 ：' . $ticket_short_url;
	}

}
