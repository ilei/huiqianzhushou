<?php 
namespace  Common\Logic;
/**
 * 购买自营商品逻辑业务 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 

class OwnOrderPayLogic{

	public $errors = array();

	/**
	 * 购买商品付款成功后的各种操作 
	 *
	 * @access  public 
	 * @param   int    $balance 
	 * @param   string $order_id 
	 * @param   array  $ext
	 * @param   array  $order
	 * @return  true or false 
	 **/ 

	public function orderPaySuccess($order_id, $balance, $ext, $order = array()){
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
                'payment_time'         => time(),
				'status'               => C('ORDER_STATUS.payed'),
				'version'              => $order['version']+1,
                'alipay_type'          => $ext['alipay_type'],
			);
		}else{
			$update = array(
				'finished_time'        => time(),
                'payment_time'         => time(),
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
        $order_guid = $order['guid'];
        $order_detail = M('OrderDetail')->where(array('order_guid' => $order_guid))->select();
        $goods_guid   = array_unique(array_columns($order_detail, 'goods_guid', 'id')); 
        $goods        = M('OwnGoods')->where(array('guid' => array('in', $goods_guid),'status' => C('own_goods.ok')))->select();
        $goods_ext    = M('OwnGoodsExt')->where(array('goods_guid' => array('in', $goods_guid),'status' => C('own_goods.ok')))->select();
        $goods_ext    = array_columns($goods_ext, 'nums', 'goods_guid');
        $email = $msg = 0;
        foreach($goods as $key => $good){
            $email += $good['category'] == 2 ? intval($goods_ext[$good['guid']]) : 0;  
            $msg   += $good['category'] == 1 ? intval($goods_ext[$good['guid']]) : 0;  
        }
        $user   = M('UserAccount')->where(array('account_guid' => $order['buyer_guid']))->find();
        if($user){
            $updates = array(
                'msg_nums' => $user['msg_nums'] + $msg, 
                'email_nums' => $user['email_nums'] + $email, 
                'updated_time' => time(),
                'version' => $user['version']+1,
            );
            $cond   = array('account_guid' => $user['account_guid'], 'version' => $user['version']);
            $res    = M('UserAccount')->where($cond)->save($updates);
        }else{
            $updates = array(
                'guid' => create_guid(),
                'account_guid' => $order['buyer_guid'], 
                'creater_guid' => $order['buyer_guid'],
                'msg_nums'     => $msg,
                'email_nums'   => $email, 
                'created_time' => time(),
                'updated_time' => time(),
            ); 
            $res = M('UserAccount')->add($updates);
        }
        $auth = session('auth');
        $auth['msg_nums']   = $updates['msg_nums'];
        $auth['email_nums'] = $updates['email_nums'];
        session('auth', $auth);
		if(!$res){
			array_push($this->errors, '用户账户数据更新失败@' . serialize($updates));
			$orderModel->rollback();
			return false;
		}
		$orderModel->commit();
		order_operation_log($order_id, C('update_order'), array_merge($order_cond, $update));
		return true ;
	} 
}
