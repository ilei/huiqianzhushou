<?php 
namespace  Common\Logic;

class UserAccountLogic{

    public $errors = array();

    public function add_msg_email_nums($user_guid){
        if(!$user_guid){
            return false;
        }
        $type = C('AUTO_ADD_TYPE'); 
        $save = array();
        $nums = $guid = $order = array();
        if(in_array('sms', $type)){
            $msg = M('OwnGoods')->where(array('category' => 1, 'type' => 1))->find(); 
            $order['title'] =  C('AUTO_ADD_NUMS').'条'.$msg['name'];
            array_push($guid, $msg['guid']);
            $nums[$msg['guid']]['num'] = C('AUTO_ADD_NUMS');
            $save['msg_nums'] = C('AUTO_ADD_NUMS');
        }
        if(in_array('email', $type)){
            $email = M('OwnGoods')->where(array('category' => 2, 'type' => 1))->find(); 
            $order['title'] = $order['title'] ? $order['title'] . '和' .C("AUTO_ADD_NUMS").'条'. $email['name']  :  C("AUTO_ADD_NUMS").'条'.$email['name'];
            array_push($guid, $email['guid']);
            $nums[$email['guid']]['num'] = C('AUTO_ADD_NUMS');
            $save['email_nums'] = C('AUTO_ADD_NUMS');
        }
        if(!$msg && !$email){
            return false;
        }
        $order['goods_guid'] = $guid;
        $order['buyer_guid'] = $user_guid;
        $order['payment_type'] = 2;
        $order['total_price'] = $msg['price'] * C('AUTO_ADD_NUMS') + $email['price'] * C('AUTO_ADD_NUMS');
        $logic = D('OwnOrder', 'Logic');
        $order_id = $logic->add($order, $nums);
        if($order_id){
            $exist = M('UserAccount')->where(array('account_guid' => $user_guid))->find();
            $save['updated_time'] = time();
            if($exist){
                $save['msg_nums'] = intval($save['msg_nums']) + $exist['msg_nums'];
                $save['email_nums'] = intval($save['email_nums']) + $exist['email_nums'];
                M('UserAccount')->where(array('guid' => $exist['guid']))->save($save);
            }else{
                $save['guid'] = create_guid(); 
                $save['account_guid'] = $user_guid;
                $save['creater_guid'] = $user_guid;
                $save['created_time'] = time();
                M('UserAccount')->add($save);
            }
            $auth = session('auth');
            $auth['msg_nums'] = $save['msg_nums'];
            $auth['email_nums'] = $save['email_nums'];
            session('auth', $auth);
            return true;
        }     
        return false; 
    }    

}
