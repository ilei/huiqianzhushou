<?php 
vendor('YmPush.Sender.Sender');
vendor('YmPush.Content.SubMailContent');
vendor('submail.lib.messagexsend');

/**
 * 邮件发送器 
 **/ 

class SubMailSender extends Sender{

	public function __construct(){
		parent::__construct(new SubMailContent());
		$this->push = new \MESSAGEXsend(C('SubMail')); 
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
		list($project, $vars) = $this->content->getContent($type, $msg_data, $args); 
		$this->push->SetProject($project);
		foreach($vars as $key => $value){
			$this->push->AddVar($key, $value);	
		}
		$this->push->AddTo($to_user);
		$res = $this->push->xsend();
		return isset($res['status']) && $res['status'] == 'success' ? array('code' => 0) : $res;
	}

}
