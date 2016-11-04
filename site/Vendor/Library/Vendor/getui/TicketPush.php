<?php 
use Org\Api\YmSMS;
class TicketPush{

	public function send($topics, $args){
		$_redis = new Redis();
		$_redis->connect('127.0.0.1', '6379');
		foreach($topics as $key => $topic){
			if(!$_redis->llen($topic)){
				unset($topics[$key]);	
				continue;
			}
			$data = $_redis->lpop($topic);
			$this->send_ticket(array($data), $args);
			break;
		}
	}

	private function send_ticket($data, $args){
		if(empty($data)){
			return;
		}
		$time = time();
		$aguid 	  = $args['aguid'];
		$send_way = $args['send_way'];
//        $send_content = $args['send_content'];
//        $send_sign    = $args['send_sign'];
		$activity_name = $args['activity_name'];
		$auth = $args['auth'];
		$obj  = unserialize($args['obj']);
		$ticket_content_sms = '【' . C('APP_NAME') . '】您申请参加' . ym_mb_substr($activity_name, 13, "..") . '已通过审核，时间地点详见电子票：';
//		$ticket_content_email = '您申请参加『'.$activity_name.'』已通过审核，时间地点详见电子票;';
		foreach ($data as $m) {
			$m = json_decode($m, true);
			// 获取票务信息
			$ticket_info = M('ActivityUserTicket')
				->field('status, ticket_code')
				->where(array('user_guid' => $m['user_guid'], 'activity_guid' => $aguid))
				->find();

			// 当票已验证则跳过
			if($ticket_info['status'] == '4') {
				continue;
			}

			$code_prefix = substr($aguid, 0, 4);
			// 判断是否电子票已发, 若已发则维持原电子票号
			if(!empty($ticket_info['ticket_code']) && strlen($ticket_info['ticket_code']) == 19) {
				$ticket_code = $ticket_info['ticket_code'];
			} else {
				$ticket_code = generate_ticket_code($code_prefix);
			}

			$ticket_url = U('Mobile/Activity/ticket', array('aid' => $aguid, 'iid' => $m['guid']), true, true);
			$ticket_short_url = get_short_url($ticket_url); // 长度20

			if(in_array('sms', $send_way)) { // 发送SMS
				$is_send_sms = true;
				$content        = $ticket_content_sms . $ticket_short_url;
				$content_length = mb_strlen($content, 'utf8');
				if ($content_length > 500) {
					continue;
				}
				$sms        = new YmSMS();
				$sms_result = $sms->sendsms($m['mobile'], $content);
				if($sms_result['code'] == '0') { // 短信发送成功
					$data_user_ticket = array('ticket_code' => $ticket_code, 'status'=>2, 'updated_at' => $time);
					$sms_status = 1;
					$is_send = true;

					// 更新社团短信余额
					D('Org')->inc_and_dec($auth['org_guid'], 'num_sms', ceil($content_length/67), 2);

				} else { // 短信发送失败
					$data_user_ticket = array('ticket_code' => $ticket_code, 'status'=>1, 'updated_at' => $time);
				}
			}
			$email = '';
			if(in_array('email', $send_way)) {
				$is_send_email = true;
				$email = M('ActivitySignupUserinfoOther')
					->where(array('activity_guid' => $aguid, 'signup_userinfo_guid' => $m['guid'], 'ym_type' => 'email'))
					->getField('value');
				if(!empty($email) && is_valid_email($email)) {
					// 发送邮箱
					$obj->views->assign('real_name', $m['real_name']);
					$obj->views->assign('ticket_url', $ticket_short_url);
					$obj->views->assign('activity_name', $activity_name);
					$obj->views->assign('tguid', $ticket_info['guid']);
					C('CACHE_PATH', CACHE_PATH.'Home/');
					$content = $obj->views->fetch('Home@Activity/email_ticket');
					$email_result = send_email(array($email), $auth['org_name'], '【'.C('APP_NAME').'】活动电子票，来自：'.$auth['org_name'], $content);

					if($email_result['status'] == 'success') { // 邮件发送成功
						$data_user_ticket = array('ticket_code' => $ticket_code, 'status'=>'2', 'updated_at' => $time);
						$email_status = 1;
						$is_send = true;
						// 更新社团邮件余额
						D('Org')->inc_and_dec($auth['org_guid'], 'num_email', 1, 2);
					} else { // 邮件发送失败
						$data_user_ticket = array('ticket_code' => $ticket_code, 'status'=>'1', 'updated_at' => $time);
					}
				}

			}

			M('ActivityUserTicket')
				->where(array('user_guid' => $m['user_guid'], 'activity_guid' => $aguid))
				->save($data_user_ticket);

			// 存储到notice表
			if($sms_status || $email_status) {
				$data_notice = array(
					'guid'         => create_guid(),
					'is_sms'       => isset($is_send_sms) ? true : false,
					'is_email'     => isset($is_send_email) ? true : false,
					'title'        => '电子票发送' . $ticket_code,
					'from'         => $auth['org_name'],
					'from_guid'    => $auth['user_guid'],
					'from_type'    => 2,
					'content'      => $content,
					'to_mobile'    => $m['mobile'],
					'to_email'     => $email,
					'to_guid'      => $m['user_guid'],
					'is_multiple'  => 0,
					'sms_status'   => $sms_status,
					'email_status' => $email_status,
					'created_at'   => $time,
					'updated_at'   => $time
				);
				M('Notice')->add($data_notice);
			}
		}
		return;
	}
}
