<?php 

vendor('YmPush.YmInfo');

/**
 * 通知信息 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 *
 **/ 

class NoticeInfo extends YmInfo{


	/**
	 * 推送器名称 
	 **/

	private static $senderName = null;

	/**
	 * 设置推送器名称
	 **/ 

	public static function setSender($senderName = 'GeTui'){
		self::$senderName = $senderName;	
	}

	/**
	 * 取得推送器名称
	 **/ 

	public static function getSender(){
		$sender = ucfirst(self::$senderName);
		return 	in_array($sender, C('Notice')) ? $sender : current(C('Notice'));
	}

	/**
	 * 推送通知到个人 
	 *
	 * @access public 
	 * @param  mixed $alias 目的用户 目前是guid 
	 * @param  array $data  通知内容
	 * @param  int   $msg_type 模板ID 
	 * @return mixed 
	 **/ 

	public static function sendToSingle($alias, $data, $msg_type = 1){
		$args = array('msg_type' => intval($msg_type));
		return self::send(1, $alias, $data, $args);	
	}

	/**
	 * 推送通知到整个APP 
	 *
	 * @access public 
	 * @param  array $data 通知内容 
	 * @param  int   $msg_type 模板ID 
	 * @return mixed 
	 **/ 

	public static function sendToApp($data, $msg_type = 1){
		$args = array('msg_type' => intval($msg_type));
		return self::send(3, null, $data, $args);	
	}


	/**
	 * 推送通知到一批人 
	 *	超过限定的人数后使用异步发送 swoole 
	 *
	 * @access public 
	 * @param  mixed $alias 目的用户 目前是guid 
	 * @param  array $data  通知内容
	 * @param  int   $msg_type 模板ID 
	 * @return mixed 
	 **/ 

	public static function sendToList($alias, $data, $msg_type = 1){
		$args  = array('msg_type' => intval($msg_type));
		$total = count($alias);
		if($total > C('YMPUSH.LIST_MAX_NUM')){
			$redis     = self::getRedis();
			$topic_arr = array();
			$alias_arr = array_chunk($alias, C('YMPUSH.LIST_MAX_NUM'));
			foreach($alias_arr as $key => $value){
				$topic_arr[] = $topic = create_guid();
				foreach($value as $k => $v){
					$redis->lpush($topic, $v);
				}
			}
			$callback = array('class' => get_class($this));

			//设置通知的模板ID 
			$data['tem_type'] = intval($msg_type);

			//异步发送 
			self::startAsyncPush($topic_arr, $data, 'send', $callback);
		}else{
			return self::send(2, $alias, $data, $args);	
		}	
	}

	/**
	 * 异步发送时 回调函数 
	 *
	 * @access public 
	 * @param  array $topics redis list 的key 
	 * @param  array $data   要发送的数据
	 * @param  array $args   其他的一些参数 
	 * @return mixed
	 **/

	public static function callbackSend($topics, $data, $args = array()){
		$redis = self::getRedis();	
		foreach($topics as $key => $topic){
			$alias = $redis->lrange($topic, 0, -1);
			$redis->del($topic);
			if(isset($data['tem_type'])){
				$args  = array('tem_type' => intval($data['tem_type']));
				unset($data['tem_type']);
			}else{
				$args  = array('tem_type' => 1);
			}
			$res = self::send(2, $alias, $data, $args); 
		}
	}
}

