<?php
namespace Admin\Controller;

/**
 * 测试程序
 * CT 2015-07-31 11:00 by ylx
 * */

class TestController extends BaseController{

	public function __construct(){
		parent::__construct();
	}

	/**
	 * 模拟发票
	 * CT 2015-07-31 11:00 by ylx
	 */
	public function sendTicket()
	{
		$aguid = I('get.aid');
		if(empty($aguid)) {
			var_dump('活动guid不能为空。');die();
		}
		$user_info = M('ActivityUserinfo')
			->field('guid, user_guid, real_name, mobile')
			->where(array('activity_guid' => $aguid, 'is_del' => 0))
			->select();
		$urls = array();
		foreach($user_info as $k => $m) {
			$code_prefix = substr($aguid, 0, 4);

			// 判断是否电子票已发, 若已发则维持原电子票号
			$ticket_info = M('ActivityUserTicket')
				->field('ticket_code')
				->where(array('user_guid' => $m['user_guid'], 'activity_guid' => $aguid))
				->find();
			if(!empty($ticket_info['ticket_code']) && strlen($ticket_info['ticket_code']) == 19) {
				$ticket_code = $ticket_info['ticket_code'];
			} else {
				$ticket_code = generate_ticket_code($code_prefix);

				$data_user_ticket = array('ticket_code' => $ticket_code, 'status'=>2, 'updated_at' => time());
				M('ActivityUserTicket')
					->where(array('user_guid' => $m['user_guid'], 'activity_guid' => $aguid))
					->save($data_user_ticket);
			}

			$urls[$k]['name'] = $m['real_name'];
			$urls[$k]['mobile'] = $m['mobile'];
			$urls[$k]['url'] = U('Mobile/Activity/ticket', array('aid' => $aguid, 'iid' => $m['guid']), true, true);
		}
		$this->assign('urls', $urls);
		$this->display();
	}
}
