<?php 

vendor('Ucpaas.Ucpaas');
vendor('YmPush.Sender.Sender');
vendor('YmPush.Content.UcpaasContent');

/**
 * 云之讯 发送器
 **/ 

class UcpaasSender extends Sender{

	/**
	 * 发送接口实例对象 
	 **/ 

	private $push = null;

	public function __construct(){
		parent::__construct(new UcpaasContent());
		$options = array(
			'accountsid' => C('UcPaas.accountsid'),
			'token'      => C('UcPaas.token'),	
		);
		$this->push = new \Ucpaas($options);

	}

	/**
	 * 发送信息函数 
	 *
	 * @access public 
	 * @param  int $type  发送类型 参见父类介绍 
	 * @param  array $msg_data 要发送的内容 
	 * @param  mixed $to_user  目标用户 
	 *
	 **/ 

	public function send($type, $to_user, $msg_data= array(), $args = array()){
		if(empty($msg_data)){
			return false;
		}
		$appid       = isset($args['appid']) && $args['appid'] ? $args['appid'] : C('UcPaas.appid');
		if(isset($args['type']) && (intval($args['type']) == C('UCPAAS_CODE_SEND_TYPE.voice'))){
			list($code, $expire) = $msg_data;
			$data = $this->push->voiceCode($appid, intval($code), $to_user);	
		}else{
			list($template_id, $msg_data) = $this->content->getContent($type, $msg_data, $args);
			$msg_data    = is_array($msg_data) ? implode(',', $msg_data) : $msg_data;
			$data = $this->push->templateSMS($appid, $to_user, $template_id, $msg_data);	
		}
		$data = json_decode($data, true);
		$res  = array();
		$res['code'] = ($data['resp']['respCode'] && $data['resp']['respCode'] == '000000') ? 0 : $data['resp']['respCode']; 
		return $res;
	}

}
