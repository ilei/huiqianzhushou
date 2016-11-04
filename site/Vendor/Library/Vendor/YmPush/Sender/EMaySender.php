<?php 
use Org\Api\YmSMS;

vendor('YmPush.Sender.Sender');
vendor('YmPush.Content.EMayContent');

/**
 * 亿美发送器 
 **/ 

class EMaySender extends Sender{

	public function __construct(){

		//使用亿美发送内容实例对象
		parent::__construct(new EMayContent());
	}

	/**
	 * 发送信息函数 
	 *
	 * @access public 
	 * @param  int $type  发送类型 参见父类介绍 
	 * @param  mixed $to_user  目标用户 
	 * @param  array $msg_data 要发送的内容 
	 * @return mixed 
	 *
	 **/ 

	public function send($type, $to_user, $msg_data = array(), $args = array()){
		if(empty($msg_data)){
			return false;
		}
		$push = new YmSMS();
		$msg_data = $this->content->getContent($type, $msg_data, $args);
		return $push->sendsms($to_user, $msg_data);	
	}

}
