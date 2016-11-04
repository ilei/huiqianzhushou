<?php
namespace Api\Controller\Signin;

use Api\Controller\BaseUserController;

class IndexController extends BaseUserController
{
    /**
     * 活动列表
     * 参数 token
     * CT: 2015-04-15 11:00 by YLX
     */
    public function activity_list()
    {
        $this->check_request_method('get');

        $model_activity = M('Activity');
        $user_info = $this->user_info;
//        $this->output_data($user_info);
        if($user_info['user_guid']){
            $user_guid = $user_info['user_guid'];
        }else{
            $user_guid = $user_info['guid'];
        }
        $where = array('user_guid' => $user_guid, 'is_del' => 0, 'status' => 1, 'is_verify' => 1);
        $list = $model_activity
            ->field('guid, start_time, end_time, name, areaid_1_name ,areaid_2_name ,address')
            ->where($where)
            ->order('start_time ASC')
//            ->page(I('get.p', '1'), C('NUM_PER_PAGE', null, 10))
            ->select();

        foreach($list as $k=>$v){
            $list[$k]['address'] = $v['areaid_1_name'].$v['areaid_2_name'].$v['address'];
        }
        if(empty($list)) {
            $this->output_error('10011', 'no more data');
        }

        // 使用page类,实现分类
//        $count = $model_activity->where($where)->count();// 查询满足要求的总记录数
//        $page  = new \Think\Page($count, C('NUM_PER_PAGE', null, 10));// 实例化分页类 传入总记录数和每页显示的记录数
//        $page->show();// 分页显示输出
//
//        $total_pages = $page->totalPages;
//        $is_last_page = 0;
//        if(I('get.p', 1) == $total_pages){
//            $is_last_page = 1;
//        }

        // 统计用户报名情况
        foreach($list as $k =>$l) {
            $status_statistic_result = M('ActivityUserTicket')
                ->field('status, count(status) as sum')
                ->where(array('activity_guid' => $l['guid'], 'is_del' => '0', 'status' => array('in', array(2, 3, 4))))
                ->group('status')
                ->select();
            // n未签到, n已签到
            $status_statistic = array( 'y' => 0, 'n' => 0, 'total' => 0 );

            if(!empty($status_statistic_result)) {
                foreach ($status_statistic_result as $s) {
                    if ($s['status'] == 2 || $s['status'] == 3) {
                        $status_statistic['n'] += $s['sum'];
                    } else if ($s['status'] == 4) {
                        $status_statistic['y'] += $s['sum'];
                    }
                }
                $status_statistic['total'] = $status_statistic['y'] + $status_statistic['n'];
            }
            $list[$k]['status'] = $status_statistic;
        }

//        $this->output_data(array('list' => $list, 'is_last_page' => $is_last_page));
        $this->output_data(array('list' => $list));
    }

    /**
     * 检查签到用户是否存在
     * CT: 2015-04-15 11:00 by YLX
     */
    public function signin_check_user()
    { 
        $this->check_request_method('post');
        $param = $this->_request_params;

        $aid = $param['aid'];
        $value = $param['value']; // 当手动签到时为mobile, 当二维码扫描时, 为ticket_code
        $signin_type = $param['type'];  // 1扫码, 2手机号
        if(empty($aid) || empty($value) || empty($signin_type)) {
            $this->output_error('10003');
        }

        // 检查票务是否存在
        $field = $signin_type==1 ? 'ticket_code' : 'mobile';
        $ticket = M('ActivityUserTicket')
            ->where(array('activity_guid' => $aid,
                          'status' => array('gt', 1), 'is_del' => 0,
                          $field =>  array('like', '%'.$value),
            ))->select();
        if($field == 'mobile'){
            if(count(array_keys($ticket)) <= 1){
                $ticket = M('ActivityUserTicket')
                    ->where(array('activity_guid' => $aid,
                                  'status' => array('gt',1), 'is_del' => 0,
                                  $field =>  array('like', '%'.$value),
                    ))->find();
            }else{
                $ticket = M('ActivityUserinfo')
                    ->field('real_name,mobile,guid')
                    ->where(array('activity_guid' => $aid,
                                  'is_del' => 0,
                                  $field =>  array('like', '%'.$value),
                    ))->select();

                //新加
                foreach ($ticket as $k=>$v) {
                    $user_info_guids[] = $v['guid'];
                }

                $tickets_info = M('ActivityUserTicket')
                    ->field('guid,userinfo_guid')
                    ->where(array('userinfo_guid' => array('in',$user_info_guids),'status' => array('gt',1),'is_del' => 0))
                    ->select();

                foreach($ticket as $k=>$v){
                    if(empty($tickets_info)){
                        $ticket[$k]['user_ticket_guid'] = '';
                    }else{
                        foreach($tickets_info as $key=>$value){
                            if($v['guid'] == $value['userinfo_guid']){
                                $ticket[$k]['user_ticket_guid'] = $value['guid'];
                            }
                        }
                    }
                }
                //新加
                $data['info'] = array_values($ticket);
                if(!$data['info']){
                    $this->output_error('10038', 'not exist');
                }
                $data['like_status'] = '1';//手机尾号有重复信息状态
                $this->output_data($data);
            }
        }else{
            $ticket = M('ActivityUserTicket')
                ->where(array('activity_guid' => $aid,
                              'status' => array('gt',1), 'is_del' => 0,
                              $field =>  array('like', '%'.$value),
                ))->find();
        }
        if(empty($ticket)){
            $this->output_error('10038', 'not exist');
        }

        // 判断验证次数
        if(strlen($ticket['ticket_guid']) == 32){ // 走票务设置
            $ticket_verify_num = M('ActivityAttrTicket')->where(array('guid' => $ticket['ticket_guid']))->getField('verify_num');
        } else { // 走参与人数， 刚默认为可验证10次
            $ticket_verify_num = C('ACTIVITY_TICKET_DEFAULT_VERIFY_TIME', null, 10);
        }
        if($ticket['ticket_verify_time'] >= $ticket_verify_num) {
            $this->output_error('10039');
        }

        // 获取用户信息
        $userinfo = D('ActivityUserinfo')
            ->field('guid, real_name, mobile, type, user_guid')
            ->where(array('activity_guid' => $aid, 'user_guid' => $ticket['user_guid'], 'is_del' => '0'))
            ->find();
        if(empty($userinfo)) {
            $this->output_error('10038', 'not exist');
        }

        // 来源
        $from = C('ACTIVITY_SIGNUP_FROM');
        $userinfo['from'] = $from[$userinfo['type']];

        // 其它信息
        $other = M('ActivityUserinfoOther')
            ->field('ym_type, key, value')
            ->where(array('userinfo_guid' => $userinfo['guid'], 'is_del' => '0'))
            ->select();
        foreach($other as $other_k => $o) {
            if($o['ym_type'] == 'company') {
                $userinfo['company'] = $o['value'];
            } else if($o['ym_type'] == 'position') {
                $userinfo['position'] = $o['value'];
            }
            $vals = explode('_____', $o['value']);
            if(count($vals) <= 1) {
                $v_str = $o['value'];
            } else {
                $v_str = implode(', ', $vals);
            }
            $other[$other_k]['value'] = $v_str;
            unset($other[$other_k]['ym_type']);
        }
        $userinfo['other'] = $other;

        // 票务相关
        $ticket_status = C('ACTIVITY_TICKET_STATUS');
        $userinfo['ticket_name'] = $ticket['ticket_name'];
        $userinfo['ticket_status'] = $ticket_status[$ticket['status']];
        $userinfo['user_ticket_guid'] = $ticket['guid'];

        unset($userinfo['guid']);
        unset($userinfo['user_guid']);
        unset($userinfo['type']);

        $userinfo['like_status'] = '0';//正常流程返回数据信息
        // 返回信息
        $this->output_data($userinfo);
    }

    /**
     * 进行签到
     */
    public function signin()
    {
        $this->check_request_method('put');

        $param = $this->_request_params;
        $user_ticket_guid = $param['user_ticket_guid'];
        $model = M('ActivityUserTicket');

        // 获取票务信息
        $ticket = M('ActivityUserTicket')
            ->field('guid, ticket_guid, ticket_verify_time, activity_guid, mobile, user_guid')
            ->where(array('guid' => $user_ticket_guid, 'status' => array('in', array(2, 3, 4)), 'is_del' => 0))
            ->find();
        if(empty($ticket)) {
            $this->output_error('10011');
        }

        // 判断验证次数
        if(strlen($ticket['ticket_guid']) == 32){ // 走票务设置
            $ticket_verify_num = M('ActivityAttrTicket')->where(array('guid' => $ticket['ticket_guid']))->getField('verify_num');
        } else { // 走参与人数， 刚默认为可难10次
            $ticket_verify_num = C('ACTIVITY_TICKET_DEFAULT_VERIFY_TIME', null, 10);
        }
        if($ticket['ticket_verify_time'] >= $ticket_verify_num) {
            $this->output_error('10039');
        }

        // 进行签到
        $signin_type = $param['type']; // 1扫码, 2手机号
        $data = array(
            'ticket_verify_time' => array('exp', 'ticket_verify_time+1'),
            'status'             => 4,
            'signin_status'      => $signin_type,
            'updated_at'         => time()
        );
        $result = $model->where(array('guid' => $user_ticket_guid))->save($data);
        M('MsgContent')->where(array('ticket_guid' => $user_ticket_guid))->save(array('status' => 4));

        // 返回结果
        if($result) {
            $this->output_data();
        } else {
            $this->output_error('10011');
        }
    }

    /**
     * 退出登录
     * CT: 2015-04-15 11:00 by YLX
     */
    public function logout()
    {
        $this->check_request_method('post');
        $guid = $this->user_info['guid'];
        $token = $this->_request_params['Token'];
        if (empty($guid)) {
            $this->output_error('10003'); // 参数错误
        }
//        $res = D('UserDevice')->logoutAll($guid);
        $res = D('UserDevice')->where(array('user_guid'=>$guid, 'token'=>$token))
        ->delete();
        if ($res < 1) {
            $this->output_error('10009'); // 未找到相关数据
        }
        // 清除redis缓存
//        $token = $this->_request_params['Token'];
//        S($token.':user_device', null);
//        S($token.':user_info', null);
        $this->output_data();
    }

    /**
     * 重新打开APP时重新登录
     * CT: 2015-04-15 11:00 by YLX
     */
    public function relogin()
    {
        $this->check_request_method('post');
        $user_info = $this->user_info;
//        if($this->user_info['user_guid']){
        $user_guid       = $this->user_info['guid'];
//        }else{
//            $user_guid       = $this->user_info['user_guid'];
//        }
//        $user_guid = $user_info['guid'];
        $token = $this->_request_params['Token'];

        // 更新token时间
        $time = time();
//        $new_token = md5($token.$time);
        $token_num = rand(10,99);
//        $data = array(
//            'token'         => $new_token,
//            'token_num'     => $token_num
//        );
        $where = array('user_guid' => $user_guid, 'token' => $token);
        $model_device = D('UserDevice');
        $res = $model_device->where($where)->find();
//        $this->output_error($model_device->getLastSql());
//        if(!$check_data) {
//            $this->output_error('10023','this1');
//        }

        if($res){
            // 返回JSON数组
            $data = array('guid'      => $user_info['guid'],
                          'user_guid'     => $user_info['user_guid'],
                          'is_active' => $user_info['is_active'],
                          'token'     => $token,
                          'token_num' => $token_num
            );
            header('Token:'.$token);
            $this->output_data($data);
        } else {
            $this->output_error('10023','this2');
        }
    }

    /**
     * 意见反馈
     * CT: 2015-08-30 by rth
     */
    public function opinion_app(){
        $this->check_request_method('post');
        $user_info = $this->user_info;
        if(!$this->_request_params){
            $this->output_error('10003','no request data');
        }
        $content = $this->_request_params['content'];
        if(!$user_info['user_guid']){
            $user_guid = $user_info['guid'];
        }else{
            $user_guid = $user_info['user_guid'];
        }

        $user_info = D('User')->where(array('guid' => $user_guid))->find();

        $time = time();
        $data['guid'] = create_guid();
        $data['user_guid'] = $user_guid;
        $data['mobile'] = $user_info['mobile'];
        $data['email'] = $user_info['email'];
        $data['content'] = html_entity_decode($content);
        $data['created_at'] = $time;
        $data['updated_at'] = $time;

        $res = D('Opinion')->data($data)->add();
        if($res){
            $this->output_data();
        }else{
            $this->output_error('10011');
        }
    }

    /**
     * 检查用户token
     * CT: 2014-11-13 10:20 by YLX
     */
    public function app_check_token()
    {
        $this->_request_params = $params = I($this->_method . '.');
        $headers               = get_request_headers();
        $token                 = $headers['Token'];
        $token_num             = $headers['Tokennum'];
        if (!isset($token)) {
            $token = $params['Token'];
        }
        if (!isset($token_num)) {
            $token_num = $params['Tokennum'];
        }
        if (!isset($token) || !isset($token_num)) {
            $this->output_error('10023', 'token not exist.');
        }

        $this->_request_params['Token']    = $token;
        $this->_request_params['Tokennum'] = $token_num;

        $token_expire = C('TOKEN_EXPIRE');//token时限
        // 检查token信息是否生成
        $token_info = D('UserDevice')->where(array('token' => $token, 'status' => 1))->find();
        if (empty($token_info)) {
            $this->output_error('10023', 'please relogin.1');
        }

        // 检查token是否超时, 限制时间为15天
        if (time() > $token_info['last_login'] + $token_expire) {
            $this->output_error('10023', 'please relogin.2');
        }

        $user_info = M('SigninUser')->where(array('guid' => $token_info['user_guid']))->find();
        if (!$user_info) {
            $user_info = M('User')->where(array('guid' => $token_info['user_guid']))->find();
        }

        //更新token_num
        $new_token_num      = $token_info['token_num'] + 1;
        $data['token_num']  = $new_token_num;
        $data['updated_at'] = time();
        D('UserDevice')->where(array('guid' => $token_info['guid']))->data($data)->save();

        //用户不存在
        if (!$user_info) {
            $this->output_error('10023', 'please relogin.3');
        } else {
//            $this->output_data($user_info);
            $this->user_info = $user_info;
//            return true;
            $this->output_data();
        }
    }
}
