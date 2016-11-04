<?php
namespace Home\Controller;

/**
 * 签到用户系统基础操作
 **/

class SignupController extends BaseController{

    /**
     * @param $activity_guid //
     * @param $params
     * @param int $user_from
     * @param array $ext
     * @param array $user_ticket
     */

    public function edit($activity_guid, $params, $user_from = 2, $ext = array(), $user_ticket = array()){

        if(!$activity_guid && !$params && !$user_ticket){
            return array(false,false,false);
        }
        $user_guid = $user_ticket['userinfo_guid'];
        $user_model = M('ActivityUserinfo');
        $ticket_model = M('ActivityUserTicket');
        $userinfo_other_model = M('ActivityUserinfoOther');
        $user = $user_model->where(array('guid' => $user_guid,'activity_guid' => $activity_guid, 'is_del' => '0'))->find();
        $ticket = $ticket_model->where(array('guid' => $user_ticket['guid'],'activity_guid' => $activity_guid, 'is_del' => '0'))->find();

        $time = time();
        //将修改前信息记录
        $data_record['guid'] = create_guid();
        $data_record['created_at'] = $time;
        $data_record['updated_at'] = $time;
        $data_record['userinfo_guid'] = $user_guid;
        $data_record['ticket_guid'] = $user_ticket['guid'];
        $data_record['userinfo_updated_at'] = $time;
        $data_record['userinfo_value'] = json_encode($user);
        $data_record['ticket_value'] = json_encode($user_ticket);
        M('ActivityUserinfoUpdateRecord')->data($data_record)->add();


        //开启事务
        M()->startTrans();
        //组装数据
        //userinfo  ticket
        $userinfo_data['real_name'] = $params['info']['real_name'];
        $userinfo_data['mobile'] = $params['info']['mobile'];
        $userinfo_data['updated_at'] = time();

        $user_res = $user_model->where(array('guid' => $user_guid))->data($userinfo_data)->save();
        $ticket_res = $ticket_model->where(array('guid' => $user_ticket['guid']))->data($userinfo_data)->save();

        //userinfo_other
        foreach($params['other'] as $k => $v){
            $userinfo_other_data['value'] = $v['value'];
            $userinfo_other_data['key'] = $v['key'];
            $userinfo_other_data['updated_at'] = time();

            $userinfo_other_res[] = $userinfo_other_model->where(array('guid' => $v['guid']))->data($userinfo_other_data)->save();
        }

        if($user_res && $ticket_res){
            $new = true;
            M()->commit();
            return array($user_guid, $new);
        }else{
            $new = false;
            M()->rollback();
            return array(false,false,false);
        }

    }

    public function add_signup(){
        header('Access-Control-Allow-Origin:*');
        $activity_guid = I('get.guid');
        $user_ticket_status = I('get.status');
        if(!$activity_guid){
            $this->error(L('_PARAM_ERROR_'));
        }
        $user_guid = I('get.user_guid');
        if($user_guid){
            $user_ticket = M('ActivityUserTicket')->where(array('guid' => $user_guid))->find();
            $info  = M('ActivityUserinfo')->where(array('guid' => $user_ticket['userinfo_guid'], 'is_del' => '0'))->find();
            $other = M('ActivityUserinfoOther')->where(array('userinfo_guid' => $info['guid'], 'is_del' => '0'))->select();
            if (empty($info)) {
                $this->error('用户尚未报名，请先报名');
            }
            $other = kookeg_array_column($other, null, 'build_guid');
            $this->assign('user_ticket', $user_ticket);
            $this->assign('info', $info);
            $this->assign('other', $other);
            $activity_guid = $user_ticket['activity_guid'];
        }
        $auth = $this->kookeg_auth_data();
        $activity_info = D('Activity')->where(array('guid' => trim($activity_guid)))->find();  
        if($activity_info['status'] != 1 || empty($activity_info)) {
            $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
        }

        // 检查报名时
        if (!$this->private_check_signup_time($activity_info)) {
            $this->error(L('_ACT_SIGNUP_NOT_START_'));
        }

        // 检查报名人数
        list($user_can_signup, $tickets) = $this->private_cacul_signup_number($activity_guid, $activity_info);
        if(empty($tickets)) {
            $this->error(L('_ACT_NOT_TICKET_'));
        }
        if ($user_can_signup == false) {
            $this->error(L('_ACT_SIGNUP_NOT_START_OR_OVER_'));
        }
        $build_info  = D('ActivityForm')->where(array('activity_guid' => $activity_info['guid']))->order('sort desc,id')->select();
        $option_info = D('ActivityFormOption')->where(array('activity_guid' => $activity_info['guid']))->field('guid,build_guid,value')->select();
        foreach ($option_info as $o) {
            $format_option_info[$o['build_guid']][] = $o;
        }
        $this->assign('tickets', $tickets);
        $this->assign('activity_info', $activity_info);
        $this->assign('build_info', $build_info);
        $this->assign('option_info', $format_option_info);
        $this->assign('user_ticket_status', intval($user_ticket_status));
        $this->show('signup');
    }

    public function post_signup(){
        header('Access-Control-Allow-Origin:*');
        if (IS_POST) {
            $activity_guid = I('get.guid');
            $user_ticket_guid     = I('post.user_ticket_guid');
            $user_ticket   = M('ActivityUserTicket')->where(array('guid' => $user_ticket_guid))->find();
            $params = I('post.');
            if(empty($params['ticket'])) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_TICKET_NOT_EMPTY_')));
            }
            if (!$activity_guid || empty($params) || empty($params['info']['real_name']) || empty($params['info']['mobile'])) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            }

            $user = M('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'mobile' => $params['info']['mobile'],'is_del' => '0'))->find();
            if($user && !$user_ticket_guid){
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_IS_SIGNUPED_')));
            }
            // 当报名时, 判断是否有余票
            $ticket_guid  = I('post.ticket');
            $check_total  = M('ActivityAttrTicket')->where(array('guid' => $ticket_guid))->getField('num');
            $check_signup = M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'ticket_guid' => $ticket_guid))->count();
            if (!($user_ticket_guid && $user_ticket['ticket_guid'] == $ticket_guid) && $check_signup >= $check_total) {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_TICKET_IS_SALE_OFF_')));
            }
            $user_from = I('post.user_from') ? I('post.user_from') : 2;
            $user_ticket_status = I('post.user_ticket_status');
            $ext = array('user_ticket_status' => $user_ticket_status); 
            if($user_ticket_status == 4){
                $ext['signin_status'] = 3; 
                $ext['payment_type']  = 3;
            }
            if(!empty($user_ticket['ticket_guid'])){
                $res = $this->edit($activity_guid, $params, $user_from, $ext, $user_ticket);
            }else{
                $res = D('Signup', 'Logic')->signup($activity_guid, $params, $user_from, $ext, $user_ticket);
            }
            list($success, $new, $pay) = $res;
            if(!$success){
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_SIGNUP_FAILED_')));
            }else{
                if($pay){

                }
                $this->ajax_response(array('status' => C('ajax_success'), 'new' => $new));
            }

        }

    }

    private function private_check_signup_time($activity_info)
    {
        // 判断报名是否开始
        $time = time();
        $ticket = M('ActivityAttrTicket')->where(array('activity_guid' => $activity_info['guid']))->select();
        $start  = min(kookeg_array_column($ticket, 'start_time', 'id'));      
        $end    = max(kookeg_array_column($ticket, 'end_time', 'id'));      
        if(!$start || !$end){
            $start = $activity_info['start_time'];
            $end   = $activity_info['end_time'];
        }
        if($time < $start || $time > $end){
            $signup_status['status'] = false;
            if ($time < $start) {
                $signup_status['time_type'] = 'start';
            } else {
                $signup_status['time_type'] = 'end';
            }
        } else {
            $signup_status['status'] = true;
            return $signup_status;
        }
    }


    /**
     * 取得票务信息 & 计算报名人数
     * @param $activity_guid
     * @param $activity_info
     * @return array
     **/

    private function private_cacul_signup_number($activity_guid, $activity_info)
    {
        //判断报名人数
        // 总票数
        $time = time();
        $condition = array(
            'activity_guid' => $activity_guid, 
            'is_del' => '0', 
            'is_for_sale' => '1',
            'start_time'  => array('ELT', $time),
            'end_time'    => array('EGT', $time),
        );
        $total_ticket     = M('ActivityAttrTicket')->field('SUM(num) as total')
            ->where($condition)
            ->find();
        $total_ticket_num = intval($total_ticket['total']);
        // 已报名人数
        $user_count = M('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->count();
        if ($total_ticket_num > 0) { // 如果票务已经被设置， 走票务
            $total_num = $total_ticket_num;

            if ($user_count >= $total_num) {
                return array(false, null);
            }

            $tickets = M('ActivityAttrTicket')
                ->where($condition)
                ->getField('guid, num, name, price', true);
            foreach ($tickets as $k => $t) {
                $user_width_this_ticket = M('ActivityUserTicket')->field('guid')
                    ->where(array('ticket_guid' => $t['guid'], 'is_del' => '0'))
                    ->count();
                if ($user_width_this_ticket >= $t['num']) {
                    unset($tickets[$k]);
                }
            }
            if (empty($tickets)) {
                return array(false, null);
            }
            return array(true, $tickets);
        }
        return array(false, null);
    }
}
