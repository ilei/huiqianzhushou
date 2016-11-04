<?php 
namespace  Common\Logic;

class SignupLogic{

    public $errors = array();

    public function signup($activity_guid, $params, $user_from = 2, $ext = array(), $user_ticket = array()){
        if(!$params){
            return array(false, false, false);
        }
        //开启事务
        M()->startTrans();
        $res = $res1 = $res2 = $res3 = $res4 = true;
        $time = time();
        $info = $params['info'];
        $mobile = trim($info['mobile']);
        $real_name = trim($info['real_name']);
        if(!$mobile || !preg_match('/^1[3584]{1}[0-9]{9}$/', $mobile)){
            return array(false, false, false);
        }
        if($user_ticket && $mobile == $user_ticket['mobile']){
            $exist = false;
        }else{
            $exist  = M('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'mobile' => $mobile, 'is_del' => 0))->find();
        }
        if($exist){
            return array(false, false, false);
        }
        $cond   = "mobile = '{$mobile}' and is_del = '0'";
        $email  = trim($info['email']);
        if(isset($params['other']) && $params['other']){
            $tmp_other = kookeg_array_column($params['other'], 'value', 'ym_type');    
            if(in_array('email', array_keys($tmp_other)) && $tmp_other['email']){
                $email = $tmp_other['email']; 
            }
        }
        $user_model = M('User');
        $new = false;
        $user = $user_model->where($cond)->find();
        if($user){
            $user_guid = $user['guid'];
        }else{
            $new  = true;
            $user_guid = create_guid();
            $data = array(
                'guid'       => $user_guid,
                'email'      => $email,
                'mobile'     => $mobile,
                'password'   => md5($mobile),
                'updated_at' => $time,
                'created_at' => $time,
            );
            $res1 = $user_model->data($data)->add();
            $tmp_info = array(
                'guid' => create_guid(),
                'user_guid' => $user_guid,
                'realname' => $info['real_name'],
                'updated_at' => $time,
                'created_at' => $time,
            );
            $res2 = M('UserAttrInfo')->data($tmp_info)->add();
            $res3 = D('UserAccount', 'Logic')->add_msg_email_nums($user_guid);
        }
        $data_info      = array(
            'activity_guid' => $activity_guid,
            'user_guid'     => $user_guid,
            'type'          => $user_from,
            'created_at'    => $time,
            'updated_at'    => $time
        );
        $data_info      = array_merge($data_info, $info);
        $data_info['email'] = $email;
        $model_userinfo = D('ActivityUserinfo');
        if($user_ticket){
            $res = D('ActivityUserinfo')->where(array('guid' => $user_ticket['userinfo_guid']))->save($data_info);
            $data_info['guid'] = $user_ticket['userinfo_guid'];
            if(!$res){
                M()->rollback();
                return array(false, false, false); 
            }
        }else{
            $data_info['guid'] = create_guid(); 
            list($check, $r) = $model_userinfo->insert($data_info);
            if (!$check || !$r) {
                M()->rollback();
                return array(false, false, false);
            }
        }
        $email = '';

        // 保存其它信息
        $other      = $params['other'];
        $data_other = array();
        foreach ($other as $o) {
            if ($o['ym_type'] == 'email') {
                $o['value'] = trim($o['value']);
            }
            $tmp = array(
                'guid'          => create_guid(),
                'userinfo_guid' => $data_info['guid'],
                'activity_guid' => $activity_guid,
                'build_guid'    => $o['build_guid'],
                'ym_type'       => $o['ym_type'],
                'key'           => $o['key'],
                'value'         => is_array($o['value']) ? implode('_____', $o['value']) : (isset($o['value']) ? $o['value'] : ''),
                'created_at'    => $time,
                'updated_at'    => $time
            );
            if($user_ticket){
                unset($tmp['guid'], $tmp['created_at']);
                $tmp['userinfo_guid'] = $user_ticket['userinfo_guid'];
                M('ActivityUserinfoOther')->where(array('guid' => $o['guid']))->save($tmp);
            }else{
                $data_other[] = $tmp; 
            }
        }
        if($user_ticket){
            M('ActivityUserTicket')->where(array('guid' => $user_ticket['guid']))->save(array('is_del' => '1','status' ,  'updated_at' => time()));
            M('MsgContent')->where(array('ticket_guid' => $user_ticket['guid']))->delete();
        }else{
            $res4 = M('ActivityUserinfoOther')->addAll($data_other);
        }
        // 保存票务信息
        $ticket_guid = $params['ticket'];
        $logic = D('Order', 'Logic');
        $data_ticket = array(
            'activity_guid' => $activity_guid,
            'userinfo_guid' => $data_info['guid'],
            'user_guid'     => $user_guid,
            'payment_type'  => isset($ext['payment_type']) ? $ext['payment_type'] : 1,
        );
        $data_ticket = array_merge($data_ticket, $ext);
        $order_id = $logic->create_order_by_ticket($ticket_guid, $data_ticket);

        //事务判断
        if($res && $res1 && $res2 && $res3 && $res4 && !empty($order_id)){
            M()->commit();
        }else{
            M()->rollback();
            return array(false,false,false);
        }

        //时间轴-参加活动-报名
        $activity_info = D('Activity')->field('name')->where(array('guid' => $activity_guid))->find();
        $goods = D('Goods')->find_one(array('ticket_guid' => $ticket_guid));
        $pay = false;
        if($goods && $goods['price'] > 0 && $order_id){
            $pay = $order_id;
        }
        return array($data_info['guid'], $new, $pay);
    }
}
