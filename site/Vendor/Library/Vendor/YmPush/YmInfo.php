<?php 

/**
 * 通知发送类 
 * 	可配置不同的发送器 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 

abstract class YmInfo{

	/**
	 * 发送器实例对象 
	 **/ 

	protected static $sender = null; 

	/**
	 * 初始化发送器 
	 **/ 

	public static function init(){
		//静态延迟绑定
		$sender = static::getSender();	

		$sender = ucfirst($sender) . 'Sender';	
		vendor('YmPush.Sender.' . $sender);
		self::$sender = new $sender();	

	}

	/**
	 * 发送函数  
	 *
	 * @access public 
	 * @param  int  $type 发送信息的类型 查看status.php 
	 * @param  mixed $to_user  目标用户 
	 * @param  array $msg_data 信息内容 
	 * @param  array $args     其他一些不必要的参数
	 * @return mixed
	 **/

	public static function send($type, $to_user, $msg_data, $args = array()){

		//初始化发送器
		self::init();
		return self::$sender->send($type, $to_user, $msg_data, $args);
	}
	abstract static function setSender($sender = '');
	abstract static function getSender();

}
