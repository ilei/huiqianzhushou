<?php 
namespace  Common\Logic;
/**
 * 购买商品逻辑业务 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 

class OrderPayLogic{

	public $errors = array();

	/**
	 * 购买商品付款成功后的各种操作 
	 *
	 * @access  public 
	 * @param   string $account_guid 
	 * @param   int    $balance 
	 * @param   string $order_id 
	 * @param   array  $ext 
	 * @return  true or false 
	 **/ 

	public function orderPaySuccess($order_id, $balance, $ext = array(), $order = array(), $user_ticket = array()){
		if(!is_numeric($balance)){
			array_push($this->errors, '缺少金额');
			return false;
		}
		if(!$order_id || (strlen($order_id) != C('order_length'))){
			array_push($this->errors, '缺少订单ID');
			return false;
		}
		$time    = time();

		if($balance != 0){
			//订单数据
			$update = array(
				'payment_trade_no'     => $ext['trade_no'],	
				'payment_trade_status' => $ext['trade_status'],
				'payment_notify_id'    => $ext['notify_id'],
				'payment_notify_time'  => $ext['notify_time'],
				'payment_buyer_email'  => $ext['buyer_email'],
				'finished_time'        => time(),
				'status'               => C('ORDER_STATUS.payed'),
				'version'              => $order['version']+1,
                'alipay_type'          => $ext['alipay_type'],
			);
		}else{
			$update = array(
				'finished_time'        => time(),
				'status'               => C('ORDER_STATUS.payed'),
				'version'              => $order['version']+1,
                'alipay_type'          => $ext['alipay_type'],
			);
		}
		$order_cond   = array(
			'order_id'     => trim($order_id),
			//'total_price'  => yuan_to_fen($balance),
			'status'       => array('in', array(C('ORDER_STATUS.unpay'), C('ORDER_STATUS.payfailed'))), 
			'version'      => intval($order['version']),
            'alipay_type'  => 0,
		);

		$orderModel = D('Order'); 

		//开启事务
		$orderModel->startTrans();
		$res = $orderModel->where($order_cond)->setField($update);

		if(!$res){
			array_push($this->errors, '订单数据更新失败@' . serialize($order_cond));
			$orderModel->rollback();
			return false;
		}


		//更新商品库存
		$goods   = D('Goods')->find_one(array('guid' => $order['goods_guid'], 'status' => 2));
		$storage = D('GoodsStorage')->find_one(array('goods_guid' => $order['goods_guid'], 'status' => 1));
		if($goods && $storage){
			if($storage['init_storage'] != -1){
				if($storage['curr_storage'] >= $order['quantity']){
					$sto_update = array('version' => $storage['version']+1, 'curr_storage' => intval($storage['curr_storage'] - $order['quantity']));
					$res    = D('GoodsStorage')->where(array('guid' => $storage['guid'], 'version' => $storage['version']))->setField($sto_update);	
					if(!$res){
						array_push($this->errors, '商品库存更新失败' . serialize($sto_update));
						$orderModel->rollback();
						return false;
					}
				}else{
					array_push($this->errors, '商品库存不足');	
					$orderModel->rollback();
					return false;
				}	
			}
		}else{
			array_push($this->errors, '商品不存在');	
			$orderModel->rollback();
			return false;
		}
	    $ticket_guid = validate_data($ext, 'ticket_guid', $goods['ticket_guid']);	
		//生成电子票
		$res = create_user_eticket($order, $ticket_guid, $user_ticket);
            	create_msg_content($res['guid'], $user_ticket['userinfo']);
		if(!$res){
			array_push($this->errors, '用户票务数据添加失败');
			$orderModel->rollback();
			return false;
		}

		//库存变更记录添加
		$record = array(
			'guid'         => create_guid(),
			'record'       => '-' . $order['quantity'],
			'created_time' => time(),	
			'goods_guid'   => $goods['guid'],
		);
		D('GoodsStorageRecord')->create($record);
		$res = D('GoodsStorageRecord')->add();
		if(!$res){
			array_push($this->errors, '商品库存记录添加失败');	
			$orderModel->rollback();
			return false;
		}
		$orderModel->commit();
		order_operation_log($order_id, C('update_order'), array_merge($order_cond, $update));
		goods_operation_log($goods['guid'], C('update_goods_storage'), $sto_update);
		goods_operation_log($goods['guid'], C('create_goods_storage_record'), $record);
		return true ;
	} 
}
