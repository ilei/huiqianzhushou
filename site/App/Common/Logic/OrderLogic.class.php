<?php 
namespace  Common\Logic;

class OrderLogic{

    public $errors = array();

    /**
     * 创建商品
     *
     * @access public 
     * @param  array  $data 
     * @param  string $auth_user_guid 用于生成电子票的登录用户的user_guid
     * @return mixed 
     **/ 
    public function add($data = array(), $ticket_guid = '', $ext = array()){
        if(empty($data)){
            return false;
        }
        if(!validate_data($data, 'goods_guid')){
            array_push($this->errors, '缺少商品GUID');	
            return false;
        }

        if(!validate_data($data, 'target_guid')){
            array_push($this->errors, '缺少目标GUID');	
            return false;
        }

        if(!validate_data($data, 'buyer_guid')){
            array_push($this->errors, '缺少购买者GUID');	
            return false;
        }
        $data['quantity'] = validate_data($data, 'quantity', 1);
        $goods   = D('Goods')->find_one(array('guid' => trim($data['goods_guid']), 'status' => 2));
        $storage = D('GoodsStorage')->find_one(array('goods_guid' => trim($data['goods_guid']), 'status' => 1));
        if(!$goods || !$storage){
            array_push($this->errors, '商品不存在');	
            return false;
        }
        $goods['storage'] = $storage['curr_storage'];
        $quantity = intval(validate_data($data, 'quantity', 1));
        if($goods['storage'] != -1 && $goods['storage'] < $quantity){
            array_push($this->errors, '商品库存不足！');	
            return false;
        }
        $buyer = D('ActivityUserinfo')->find_one(array('guid' => trim($data['buyer_guid']), 'activity_guid' => trim($data['target_guid'])));
        $ext['userinfo'] = $buyer;
        if(!$buyer){
            array_push($this->errors, '购买者不存在');	
            return false;
        }
        $order = array(
            'guid'		   => create_guid(),
            'order_id'	   => create_order_id(), 	
            'title'		   => validate_data($data, 'title', $goods['name']),	
            'quantity'	   => $data['quantity'],
            'goods_guid'   => trim($data['goods_guid']),
            'target_guid'  => trim($data['target_guid']),
            'goods_name'   => validate_data($data, 'goods_name', $goods['name']),
            'goods_price'  => validate_data($data, 'goods_price') ? yuan_to_fen($data['goods_price']) : $goods['price'],
            'seller_guid'  => validate_data($data, 'seller_guid', $goods['seller_guid']),
            'seller_name'  => validate_data($data, 'seller_name', $goods['seller_name']),
            'buyer_guid'   => trim($data['buyer_guid']),
            'buyer_name'   => $buyer['real_name'],
            'buyer_type'   => validate_data($data, 'buyer_type', 1),
            'payment_type' => validate_data($data, 'payment_type', 1),
            'status'       => 0,
            'created_at'   => time(),
            'updated_at'   => time(),
            'user_guid'    => validate_data($data, 'user_guid', ''),
            'version'      => 1,
        );
        $activity = M('Activity')->where(array('guid' => $order['target_guid']))->find();
        if($activity){
            $order['activity_name'] = $activity['name']; 
        }
        $order['desc'] = $order['title'];
        $order['total_price'] = $this->_getRealMoney($data, $goods);
        $model = D('Order');
        $model->startTrans();
        $res   = $model->create($order);
        $res   = $model->add();
        if(!$res){
            array_push($this->errors, '创建订单失败');
            $model->rollback();
            return false;	
        }
        $model->commit();
        //免费票，直接支付成功，并生成电子票
        if($order['total_price'] == 0 || $order['payment_type'] == 3){
            $logic = D('OrderPay', 'Logic');
            $res = $logic->orderPaySuccess($order['order_id'], $order['total_price'], array('ticket_guid' => $ticket_guid), $order, $ext);
        }
        order_operation_log($order['guid'], C('create_order'), $order);
        return $order['order_id'];
    }	

    private function _getRealMoney($data, $goods){
        $is_discount = validate_data($data, 'is_discount', 0);
        if(!$is_discount){
            return $data['quantity'] * $goods['price'];	
        }	
        return 10;
    }

    /**
     * 通过票的GUID创建订单 
     *
     * @access public 
     * @param  string $ticket_guid 票的GUID 
     * @param  array  $data        必须的订单数据 
     * @return  true or false 
     **/

    public function create_order_by_ticket($ticket_guid, $data){
        $goods = D('Goods')->find_one(array('ticket_guid' => trim($ticket_guid)));
        if(!$goods){
            return false;
        }		
        $order = array(
            'goods_guid'  => $goods['guid'],	
            'target_guid' => $data['activity_guid'], 
            'buyer_guid'  => $data['userinfo_guid'],
            'quantity'    => validate_data($data, 'quantity', 1),
            'user_guid'   => $data['user_guid'],
            'payment_type' => $data['payment_type'],
        );
        $ext = array();
        if(isset($data['user_ticket_status'])){
            $ext = array(
                'user_ticket_status' => $data['user_ticket_status'],
                'signin_status'      => $data['signin_status'], 
            );
        }
        return $this->add($order, $ticket_guid, $ext);
    }
}
