<?php 

vendor('YmPush.YmInfo');

/**
 * 电子票信息 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 *
 **/ 

class TicketInfo extends YmInfo{


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
		return 	in_array($sender, C('eTicket')) ? $sender : C('TICKET_SENDER');
	}

	/**
	 * 推送电子票到一批人 
	 *	使用异步发送 swoole 
	 *
	 * @access public 
	 * @param  mixed $alias 目的用户 目前是guid 
	 * @param  array $data  通知内容
	 * @param  int   $msg_type 模板ID 
	 * @return mixed 
	 **/ 

	public static function sendToList($users, $data, $args = array()){
		$redis = self::getRedis();	
		$topic_arr = array();
		$alias_arr = array_chunk($users, C('YMPUSH.LIST_MAX_NUM'));
		foreach($alias_arr as $key => $value){
			$topic_arr[] = $topic = create_guid();
			foreach($value as $k => $v){
				$redis->lpush($topic, json_encode($v));
			}
		}
		$callback = array('class' => get_class($this));
		//异步发送 
		self::startAsyncPush($topic_arr, $data, 'tick', $callback);
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
			if(!$redis->llen($topic)){
				unset($topics[$key]);	
				continue;
			}
			$tmp = $redis->lpop($topic);
			self::sendTicket($tmp, $data);
			break;
		}
	}

	public static function sendTicket($data, $args){
		if(empty($data)){
			return;
		}

		$send_way = $args['send_way'];
		$aguid 	  = $args['aguid'];
		$time     = time();
		$auth     = $args['auth'];
		$m        = json_decode($data, true);
		$acvitity_name = $args['activity_name'];
		// 获取票务信息
		$ticket_info = M('ActivityUserTicket')
			->field('status, ticket_code, guid')
			->where(array('user_guid' => $m['user_guid'], 'activity_guid' => $aguid))
			->find();
		// 当票已验证则跳过
		if(!$ticket_info || $ticket_info['status'] == '4') {
			return;
		}

		$code_prefix = substr($aguid, 0, 4);
		// 判断是否电子票已发, 若已发则维持原电子票号
		if(!empty($ticket_info['ticket_code']) && strlen($ticket_info['ticket_code']) == 19) {
			$ticket_code = $ticket_info['ticket_code'];
		} else {
			$ticket_code = generate_ticket_code($code_prefix);
		}
		$ticket_url = U('Mobile/Activity/ticket', array('aid' => $aguid, 'iid' => $m['guid']), true, true);
		$acvitity_name = html_entity_decode($acvitity_name);
		// 发送SMS
		if(in_array('sms', $send_way) || $send_way == 'sms') {
			$is_send_sms = true;
			$ticket_short_url = get_short_url($ticket_url . '/source/1'); // 长度20
			$content     = '【' . C('APP_NAME') . '】您申请参加的『' . ym_mb_substr($acvitity_name, 10, '...') . '』已通过审核,时间地点详见电子票：' . $ticket_short_url;
			$content_length = mb_strlen($content, 'utf8');
			if ($content_length > 500) {
				return;
			}
			$sms_result = self::send(C('TICKET_TYPE.sms_ticket'), $m['mobile'], $m, $args);
			if($sms_result['code'] == '0') { // 短信发送成功
				$data_user_ticket = array('ticket_code' => $ticket_code, 'status'=>2, 'updated_at' => $time);
				$sms_status = 1;
				$is_send = true;
				ticket_send_log($auth['org_guid'], $aguid, C('send_sms_ticket_succ'), $ticket_info, $sms_result);
			} else { // 短信发送失败
				$data_user_ticket = array('ticket_code' => $ticket_code, 'status'=>1, 'updated_at' => $time);
				ticket_send_log($auth['org_guid'], $aguid, C('send_sms_ticket_fail'), $ticket_info, $sms_result);
			}
		}

		// 发送邮件
		if(in_array('email', $send_way) || $send_way == 'email') {
			$is_send_email = true;
			if(!empty($m['email'])) { // 此为单个重新发送时，可以自定义模板
				$email = $m['email'];
			} else { // 或者从用户报名填写信息里获取第一个邮箱
				$email = M('ActivitySignupUserinfoOther')
					->where(array('activity_guid' => $aguid, 'signup_userinfo_guid' => $m['guid'], 'ym_type' => 'email'))->order('id asc')->getField('value');
			}
			if(!empty($email) && is_valid_email($email)) {
				self::setSender('Mail');
				$args['ticket_info'] = $ticket_info;
				$email_result = self::send(C('TICKET_TYPE.mail_ticket'), $email, $m, $args);
				unset($args['ticket_info']);
				if($email_result['status'] == 'success') { // 邮件发送成功
					$data_user_ticket = array(
						'ticket_code' => $ticket_code, 
						'status'      =>'2', 
						'updated_at'  => $time
					);
//					$ticket_short_url = get_short_url($ticket_url . '/source/2'); // 长度20
//					create_ticket_qrcode($ticket_short_url, $ticket_info['guid']); // 生成电子票连接二维码
					$email_status = 1;
					$is_send      = true;
					ticket_send_log($auth['org_guid'], $aguid, C('send_mail_ticket_succ'), $ticket_info, $email_result);
				} else { // 邮件发送失败
					$data_user_ticket = array(
						'ticket_code' => $ticket_code, 
						'status'      => '1', 
						'updated_at'  => $time
					);
					ticket_send_log($auth['org_guid'], $aguid, C('send_mail_ticket_fail'), $ticket_info, $email_result);
				}
			}
		}
		M('ActivityUserTicket')
			->where(array('user_guid' => $m['user_guid'], 'activity_guid' => $aguid))
			->save($data_user_ticket);

		// 存储到notice表
//		if($sms_status || $email_status) {
//			$data_notice = array(
//				'guid'         => create_guid(),
//				'is_sms'       => isset($is_send_sms) ? true : false,
//				'is_email'     => isset($is_send_email) ? true : false,
//				'title'        => '电子票发送' . $ticket_code,
//				'from'         => $auth['org_name'],
//				'from_guid'    => $auth['user_guid'],
//				'from_type'    => 2,
//				'content'      => $content,
//				'to_mobile'    => $m['mobile'],
//				'to_email'     => $email,
//				'to_guid'      => $m['user_guid'],
//				'is_multiple'  => 0,
//				'sms_status'   => $sms_status,
//				'email_status' => $email_status,
//				'created_at'   => $time,
//				'updated_at'   => $time
//			);
//			M('Notice')->add($data_notice);
//		}
		return true;
	}

}

