<?php 

vendor('YmPush.YmInfo');

/**
 * 验证码信息 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 

class VerCodeInfo extends YmInfo{

	/**
	 * 推送器名称 
	 **/

	private static $senderName = null;

	/**
	 * 设置推送器名称
	 **/ 

	public static function setSender($senderName = ''){ 
		self::$senderName = $senderName;	
	}

	/**
	 * 取得推送器名称
	 **/ 

	public static function getSender(){
		$sender = ucfirst(self::$senderName);
		return 	in_array($sender, C('VerCode')) ? $sender : C('VERCODE_SENDER');
	}

}
