<?php 
namespace  Common\Logic;
/**
 * 自营商品订单逻辑 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 
class OwnOrderLogic{

	public $errors = array();

	/**
	 * 创建商品
	 *
	 * @access public 
	 * @param  array  $data 
	 * @param  string $auth_user_guid 用于生成电子票的登录用户的user_guid
	 * @return mixed 
	 **/ 
	public function add($data = array(), $ext = array()){
		if(empty($data)){
			return false;
		}
		if(!validate_data($data, 'goods_guid')){
			array_push($this->errors, L('_GOODS_GUID_NOT_EXIST_'));	
			return false;
		}

		if(!validate_data($data, 'buyer_guid')){
			array_push($this->errors, L("_BUYER_GUID_NOT_EXIST_"));	
			return false;
		}
		$quantity = validate_data($data, 'quantity', 1);
        $cond   = array(
            'guid'   => array('in', $data['goods_guid']), 
            'status' => C('own_goods.ok'), 
            'is_del' => C('own_goods.ok'), 
        );
        $exist  = M('OwnGoods')->where($cond)->select();
        if(!$exist || count($exist) != count($data['goods_guid'])){
			array_push($this->errors, L('_GOODS_NOT_EMPTY_'));	
            return false;
        }else{
            foreach($exist as $key => $value){
                if($value['storage'] != C('own_goods.nolimit') && intval($value['storage']) < $quantity){
			        array_push($this->errors, L('_GOODS_STORAGE_NOT_ENOUGH_'));	
                    return false;
                }
            }
        }
		$buyer = D('User')->find_one(array('guid' => trim($data['buyer_guid'])));
		if(!$buyer){
			array_push($this->errors, L('_BUYER_NOT_EXIST_'));	
			return false;
		}
        $total_price = array_sum(array_columns($exist, 'price', 'id'));
        $goods = array_columns($exist, null, 'guid');
		$order = array(
			'guid'		   => create_guid(),
			'order_id'	   => create_order_id(), 	
			'title'		   => validate_data($data, 'title', ''),	
            'total_price'  => $data['total_price'] ? intval($data['total_price']) : intval($total_price),
			'quantity'	   => $data['quantity'],
			'seller_guid'  => validate_data($data, 'seller_guid', ''),
			'seller_name'  => validate_data($data, 'seller_name', '酷客会签'),
			'buyer_guid'   => trim($data['buyer_guid']),
			'buyer_name'   => $buyer['real_name'],
			'buyer_type'   => validate_data($data, 'buyer_type', 1),
			'payment_type' => validate_data($data, 'payment_type', 1),
			'status'       => 0,
			'created_at'   => time(),
			'updated_at'   => time(),
			'version'      => 1,
            'is_discount'  => 3,
		);
		$order['desc'] = $order['title'];
		$model = D('Order');
		$model->startTrans();
		$res   = $model->data($order)->add();
		if(!$res){
			array_push($this->errors, L('_ORDER_CREATE_FAILED_'));
			$model->rollback();

			return false;	
		}
        $detail = array();
        foreach($data['goods_guid'] as $key => $goods_guid){
            $detail[] = array(
                'guid' => create_guid(),
                'order_guid' => $order['guid'],
                'goods_guid' => $goods_guid,
                'goods_name' => $goods[$goods_guid]['name'],
                'goods_num'  => isset($ext[$goods_guid]['num']) && intval($ext[$goods_guid]['num']) ? intval($ext[$goods_guid]['num']) : 1,
                'created_time' => time(),
                'updated_time' => time(),
                'seller_name'  => '酷客会签',
                'goods_price'  => $goods[$goods_guid]['price'],
            );     
        }
        $res = M('OrderDetail')->addAll($detail);
        if(!$res){
			array_push($this->errors, L('_ORDER_CREATE_FAILED_'));
			$model->rollback();
			return false;	
        }
		$model->commit();
		//免费票，直接支付成功，并生成电子票
		if($order['total_price'] == 0 || $order['payment_type'] == 2){
			$logic = D('OwnOrderPay', 'Logic');
			$res = $logic->orderPaySuccess($order['order_id'], $order['total_price'], array(), $order);
		}
        $this->money = $order['total_price'];
		order_operation_log($order['guid'], C('create_order'), $order);
		return $order['order_id'];
	}	

}
