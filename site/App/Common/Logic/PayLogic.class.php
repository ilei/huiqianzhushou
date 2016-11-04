<?php 
namespace  Common\Logic;

class PayLogic{

	public $errors = array();

	//只是用于标记是否余额不足
	public $balance = 1;
	/**
	 * 创建客户充值记录， 类似于订单
	 * 
	 * @access public  
	 * @param  array  $data 
	 * @param  array  $ext 
	 * @return $id  or false 
	 **/ 

	public function createOrder($data, $ext = array()){
		if(empty($data)){
			array_push($this->errors, '创建订单缺少数据');
			return false; 
		}
		if(!isset($data['org_guid']) || !$data['org_guid']){
			array_push($this->errors, '创建订单缺少账户GUID');
			return false;
		}
		if(!isset($data['money']) || !$data['money']){
			array_push($this->errors, '创建订单缺少金额');
			return false;
		}

		$time = time();
		$order = array(
			'guid'         => create_guid(),	
			'account_guid' => trim($data['org_guid']),
			'balance'      => yuan_to_fen($data['money']),
			'creater_guid' => isset($data['creater_guid']) && $data['creater_guid'] ? trim($data['creater_guid']) : $data['org_guid'],
			'is_del'       => 1,
			'status'       => isset($data['status']) && intval($data['status']) ? intval($data['status']) : C('ORDER_STATUS.unpay'),

			'type'         => isset($data['type']) && in_array(intval($data['type']), C('pay_type')) ? intval($data['type']) : C('pay_type.alipay'),
			'order_id'     => create_order_id(),
			'created_time' => $time,
			'updated_time' => $time,
		);

		$ip = format_ip();
		$order = array_merge($order, $ip);
		D('RechargeRecord')->create($order);
		$id = D('RechargeRecord')->add();
		if(!$id){
			array_push($this->errors, '创建订单失败');
			return false;
		}
		operation_log($order['account_guid'], C('create_recharge_record'), $order);
		return $order['order_id'];	
	}

	/**
	 * 用户充值成功后的各种操作 
	 *
	 * @access  public 
	 * @param   string $account_guid 
	 * @param   int    $balance 
	 * @param   string $order_id 
	 * @param   array  $ext 
	 * @return  true or false 
	 **/ 

	public function orderPaySuccess($account_guid, $balance, $order_id, $ext = array()){
		if(!$account_guid || (strlen($account_guid) != 32)){
			array_push($this->errors, '缺少账户GUID');
			return false;
		}
		if(!is_numeric($balance)){
			array_push($this->errors, '缺少金额');
			return false;
		}
		if(!$order_id || (strlen($order_id) != 28)){
			array_push($this->errors, '缺少订单ID');
			return false;
		}
		$time    = time();

		//用户账户数据
		$account = array(
			'guid'         => create_guid(),	
			'balance'      => yuan_to_fen($balance),
			'status'       => 1,
			'account_guid' => trim($account_guid),
			'creater_guid' => (isset($ext['creater_guid']) && trim($ext['creater_guid'])) ? trim($ext['creater_guid']) : trim($account_guid),
			'created_time' => $time,
			'updated_time' => $time,
		);

		//用户余额记录表数据
		$ip = format_ip();
		$balance_record = array_merge($account, array(
			'guid' => create_guid(),	
			'status' => 1,
		), $ip);

		$order_update = array(
			'status'       => C('ORDER_STATUS.payed'),
			'updated_time' => $time,
		);
		$order_cond   = array(
			'order_id'     => trim($order_id),
			'account_guid' => trim($account_guid),
			'creater_guid' => $account['creater_guid'],	
			'balance'      => yuan_to_fen($balance),
			'status'       => array('in', array(C('ORDER_STATUS.unpay'), C('ORDER_STATUS.payfailed'))) 
		);

		$userAccount = D('UserAccount'); 

		//开启事务
		$userAccount->startTrans();
		//为保证账号唯一，数据库已添加唯一索引
		$exist = $userAccount->where(array('account_guid' => trim($account_guid)))->find();
		$u = false;
		$acc = null;
		if($exist){
			//乐观锁处理
			$update = array('account_guid' => trim($account_guid), 'version' => intval($exist['version']));	
			$updata = array(
				'balance' => intval(intval($exist['balance']) + yuan_to_fen($balance)),
				'version' => intval($exist['version'] + 1),
			); 
			$res    = $userAccount->where($update)->setField($updata);
			$acc    = array_merge($update, array('balance' => yuan_to_fen($balance))); 
			$u = true;
		}else{
			$userAccount->create($account);
			$res    = $userAccount->add();
			$acc    = $account;
		}
		if(!$res){
			array_push($this->errors, '账户数据添加或更新失败@' . serialize($acc));
			$userAccount->rollback();
			return false;
		}

		D('BalanceRecord')->create($balance_record);
		$res = D('BalanceRecord')->add();
		if(!$res){
			array_push($this->errors, '账户余额记录表数据添加失败@' . serialize($balance_record));
			$userAccount->rollback();
			return false;
		}

		$up = D('RechargeRecord')->where($order_cond)->save($order_update);
		if(!$up){
			array_push($this->errors, '用户订单状态更新失败' . serialize($order_cond));
			$userAccount->rollback();
			return false;
		}
		$userAccount->commit();
		operation_log($account_guid, C('update_recharge_record'), $order_cond);
		operation_log($account_guid, C('create_balance_record'), $balance_record);
		if($u){
			operation_log($account_guid, C('update_user_account'), $balance);
		}else{
			operation_log($account_guid, C('create_user_account'), $account);
		}
		return true ;
	} 


	/**
	 *	发送电子票后的一些操作 
	 *
	 *	@access public 
	 *	@param  string $account_guid 
	 *	@param  int    $balance      以“分”为单位 
	 *	@param  string $goods_guid 
	 *	@param  int    $num          消耗商品的数量
	 *	@param  array  $ext       
	 *	@return true or false 
	 **/ 

	public function afterSendTicket($account_guid, $balance, $goods_guid, $nums, $ext = array(), $type = 'sms'){
		if(!$account_guid || (strlen($account_guid) != 32)){
			array_push($this->errors, '缺少账户GUID');
			return false;
		}
		if(!is_numeric($balance)){
			array_push($this->errors, '缺少金额');
			return false;
		}
		if(!$goods_guid || (strlen($goods_guid) != 32)){
			array_push($this->errors, '缺少消费品GUID');
			return false;
		}
		if(!intval($nums)){
			array_push($this->errors, '缺少消费品数量');
			return false;
		}

		$time    = time();
		$userAccount = D('UserAccount'); 

		//开启事务
		$userAccount->startTrans();

		//查看是否有相应的消费品

		$cond = array('guid' => trim($goods_guid), 'status' => 1, 'is_del' => 1);	
		$good = D('OwnGoods')->where($cond)->find();
		if(!$good){
			array_push($this->errors, '没有找到相应的消费品');
			$userAccount->rollback();
			return false;
		}

		/*if($balance != (intval($good['price']) * intval($nums))){
			array_push($this->errors, '金额和消费品与数量的乘积不一致');
			$userAccount->rollback();
			return false;
		}
         */
        
		$exist = $userAccount->where(array('account_guid' => trim($account_guid)))->find();
		if(!$exist){
			array_push($this->errors, '尚未充值');
			$this->balance = 0;
			$userAccount->rollback();
			return false;
		}
        $msg_nums = intval($exist['msg_nums']);
        $email_nums = intval($exist['email_nums']);
        if($type == 'sms' && (intval($nums) > $msg_nums)){
			array_push($this->errors, '短信条数不够，请先购买');
			$userAccount->rollback();
			return false;
        }elseif($type == 'sms'){
            $msg_nums = $msg_nums-intval($nums); 
        }
        if($type == 'email' && (intval($nums) > $email_nums)){
			array_push($this->errors, '邮件条数不够，请先购买');
			$userAccount->rollback();
			return false;
        }elseif($type == 'email'){
            $email_nums = $email_nums-intval($nums); 
        }
	   /*if(intval($exist['balance']) < $balance){
			array_push($this->errors, '余额不足，请先充值');
			$this->balance = 0;
			$userAccount->rollback();
			return false;
		}
        */

		//乐观锁处理
		$update = array('account_guid' => trim($account_guid), 'version' => intval($exist['version']));	
		$updata = array(
			'balance' => intval(intval($exist['balance']) - $balance),
			'version' => intval($exist['version'] + 1),
            'msg_nums'=> $msg_nums, 
            'email_nums'=> $email_nums,
		); 
		//$res    = $userAccount->where($update)->setField($updata);
        $res = true;
		if(!$res){
			array_push($this->errors, '账户数据更新失败@' . serialize($update));
			$userAccount->rollback();
			return false;
		}

		//用户余额更新记录表数据
		$balance_record = array(
			'guid'         => create_guid(),	
			'balance'      => '-' . $balance,
			'status'       => 1,
			'account_guid' => trim($account_guid),
			'creater_guid' => (isset($ext['creater_guid']) && trim($ext['creater_guid'])) ? trim($ext['creater_guid']) : trim($account_guid),
			'created_time' => $time,
			'updated_time' => $time,
		);

		$ip = format_ip();
		$balance_record = array_merge($balance_record, $ip);
		D('BalanceRecord')->create($balance_record);
		$res = D('BalanceRecord')->add();
		if(!$res){
			array_push($this->errors, '账户余额记录表数据添加失败@' . serialize($balance_record));
			$userAccount->rollback();
			return false;
		}

		//用户消费记录表

		$custom_record = array(
			'guid'         => create_guid(),	
			'goods_guid'   => trim($goods_guid),
			'nums'         => intval($nums),
			'status'       => 1,
			'created_time' => $time,
			'updated_time' => $time,
			'target_guid'  => isset($ext['target_guid']) && $ext['target_guid'] ? $ext['target_guid'] : '',
		);	

		D('CustomRecord')->create($custom_record);
		$id = D('CustomRecord')->add();
		if(!$id){
			array_push($this->errors, '用户消费记录添加失败@' . serialize($custom_record));
			$userAccount->rollback();
			return false;
		}
		$userAccount->commit();
		operation_log($account_guid, C('create_custom_record'), $custom_record);
		operation_log($account_guid, C('create_balance_record'), $balance_record);
		operation_log($account_guid, C('update_user_account'), $balance);
		return true;
	}


}
