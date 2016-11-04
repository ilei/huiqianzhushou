<?php 

vendor('getui.Push');

/**
 * 个推服务 
 *
 * CT 2015-05-06 15:00 by wangleiming 
 **/ 

class YmPush extends Push{

	public function __construct(){
		parent::__construct();
	}

	//按列表推 一组50人 
	/**
	 * 推送列表 一组50人 
	 *
	 * @access public 
	 * @param  array  $alias 要推送的别名数组 
	 * @param  array  $data  要推送的内容 
	 * @return void 
	 **/ 

	public function pushList($alias, $data){
        $data = array('status' => 1);
	    return $this->pushMessageToList($alias, json_encode($data));	
	}  


	/**
	 * swoole_client 链接server端注册发送任务 
	 *
	 * @access private 
	 * @param  array  $topics 队列key 
	 * @param  $data  要发送的数据
	 * @return mixed 
	 **/ 

	private function _startPush($topics, $data){
		$client = new swoole_client(SWOOLE_SOCK_TCP);	
		if(!$client->connect(C('SWOOLE_HOST'), C('SWOOLE_PORT'))){
			file_put_contents(C('LOG_FILE'), 'YmPush Connect Server at :' . date('Y-m-d H:i:s'), FILE_APPEND);
		}else{
			//参照 cli/server控制器理解
			$send = array();
			//发送命令 
			$send['cmd']    = isset($data['cmd']) ? $data['cmd'] : 'send';
			unset($data['cmd']);
			$send['object'] = isset($data['object']) ? $data['object'] : '';
			$send['method'] = isset($data['method']) ? $data['method'] : '';
			
			//回调函数的参数
			$send['args'] = array($topics, $data);
			$client->send(json_encode($send));
			$receive = $client->recv();	
		}
	}

	private function _getRedis(){
		static $_redis = null;
		if(!$_redis){
			$_redis = new Redis();
			$_redis->connect('127.0.0.1', '6379');
		}
		return $_redis;
	}	


	public function sendTicket($userList, $data = array()){
		$redis = $this->_getRedis();
		$topic_arr = array();
		$alias_arr = array_chunk($userList, C('YMPUSH.LIST_MAX_NUM', null, 50));
		foreach($alias_arr as $key => $value){
			$topic_arr[] = $topic = create_guid();
			foreach($value as $k => $v){
				$redis->lpush($topic, json_encode($v));
			}
		}
		vendor('getui.TicketPush');
		$data['object'] = new \TicketPush(); 
		$data['object'] = serialize($data['object']); 
		$data['method'] = 'send';
		$data['aguid']  = $data['aguid'];
		$data['send_way']  = $data['send_way'];
		$data['cmd'] = 'tick';
		$this->_startPush($topic_arr, $data);
	}
}
