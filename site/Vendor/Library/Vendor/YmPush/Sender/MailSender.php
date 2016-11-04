<?php 
vendor('YmPush.Sender.Sender');
vendor('YmPush.Content.MailContent');

/**
 * 邮件发送器 
 **/ 

class MailSender extends Sender{

	public function __construct(){
		parent::__construct(new MailContent());
	}
	/**
	 * 发送信息函数 
	 *
	 * @access public 
	 * @param  int $type  发送类型 参见父类介绍 
	 * @param  array $msg_data 要发送的内容 
	 * @param  mixed $to_user  目标用户 
	 * @param  array $args     扩展参数    具体查看个推相应的注释
	 *
	 **/ 

	public function send($type, $to_user, $msg_data = array(), $args = array()){
		if(empty($msg_data)){
			return false;
		}
		list($org_name, $title, $content) = $this->content->getContent($type, $msg_data, $args); 
		return send_email(array($to_user), $args['app_name'], $title, $content);
	}

}
