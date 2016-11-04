<?php 
vendor('YmPush.Content.Content');

/***
 * 邮件内容生成器
 **/ 

class MailContent extends Content{

	/**
	 * 继承父类方法 具体参数说明参看父类函数 
	 **/ 

	public function getContent($type, $data = array(), $ext = array()){
		$callback = 'get' . ucfirst($type['hook_prefix']) . 'Content';	
		return $this->$callback($data, $ext);
	}

	/**
	 * 发送电子票
	 *
	 * @access public 
	 * @param  array $msg_data 
	 * @param  array $ext 
	 * @return array 
	 **/ 

	public function getMailTicketContent($data, $ext = array()){
		$time          = time();
		$aguid 	       = $ext['aguid'];
		$activity_name = html_entity_decode($ext['activity_name']);
		$auth          = $ext['auth'];
		$obj           = unserialize($ext['obj']);
		$ticket_info   = $ext['ticket_info'];
		$ticket_url    = U($ext['app_url'] . '/Mobile/Activity/ticket', array('aid' => $aguid, 'iid' => $data['guid']), true, true);
		$ticket_short_url = get_short_url($ticket_url . '/source/2'); // 长度20
		$obj->views->assign('real_name',     $data['real_name']);
		$obj->views->assign('ticket_url',    $ticket_short_url);
		$obj->views->assign('activity_name', $activity_name);
		$obj->views->assign('tguid',         $ticket_info['guid']);
		C('CACHE_PATH', CACHE_PATH.'Sender/');
		$content = $obj->views->fetch('Sender@Sender/email_ticket');
		$title   = '【' . $ext['app_name'] . '】活动电子票';
		return array($auth['email'], $title, $content);
	}


}
