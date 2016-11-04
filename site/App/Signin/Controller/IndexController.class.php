<?php
namespace Signin\Controller;

/**
 * 签到首页
 * Class IndexController
 * @package Signin\Controller
 * ct: 2015-07-09 17:09 by ylx
 */
class IndexController extends BaseUserController
{
    /**
     * 签到首页 - 活动列表、
     * ct: 2015-07-09 17:09 by ylx
     */
    public function index()
    {
        $auth = $this->get_auth_session();
        $num_per_page =  C('NUM_PER_PAGE', null, 10);
        $model_activity = M('Activity');
        $user_guid = $auth['user_guid'];
        $user_info = D('User')->find_one(array('guid'=>$user_guid));
        $user_detail = D('UserAttrInfo')->where(array('user_guid' => $user_guid))->find();
        $where = array('a.user_guid' => $user_guid, 'a.is_del' => 0, 'a.status' => 1,'a.is_verify'=>1); 
        
        $list = $model_activity->alias('a')
            ->field('*, a.guid as aid')
            ->where($where)
            ->order('a.start_time DESC')
            ->page(I('get.p', '1'),$num_per_page )
            ->select();
        // 使用page类,实现分类
        $count = $model_activity->alias('a')->where($where)->count();// 查询满足要求的总记录数
        $page  = new \Think\Page($count, $num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show();// 分页显示输出
        $this->assign('user_info',$user_info);
        $this->assign('user_detail',$user_detail);
        $this->assign('page',$show);
        $this->assign('list', $list);
        $this->assign('meta_title', L('_HOME_'));
        $this->display();
    }
    //历史记录
    public function oldlist()
    {
        $auth = $this->get_auth_session();
        // var_dump($auth);
        $num_per_page =  C('NUM_PER_PAGE', null, 10);
        $model_activity = M('Activity');
        $user_guid = $auth['user_guid'];
        $user_info = D('User')->find_one(array('guid'=>$user_guid));
        $user_detail = D('UserAttrInfo')->where(array('user_guid' => $user_guid))->find();
        if ($auth['type'] ==1) {
            $where = "a.user_guid='$user_guid' and a.is_del=0 and a.status >1";
        }else{
            $this->error(L('_INSUFFICIENT_PERMISSIONS_')); 
        }
        
        $list = $model_activity->alias('a')
            ->field('*, a.guid as aid')
            ->where($where)
            ->order('a.start_time DESC')
            ->page(I('get.p', '1'),$num_per_page )
            ->select();
            // echo $model_activity->getLastSQL();die;
        // 使用page类,实现分类
        $count = $model_activity->alias('a')->where($where)->count();// 查询满足要求的总记录数
        $page  = new \Think\Page($count, $num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $page->show();// 分页显示输出
        $this->assign('user_info',$user_info);
        $this->assign('user_detail',$user_detail);
        $this->assign('page',$show);
        $this->assign('list', $list);
        $this->assign('meta_title', L('_HISTORICAL_EVENTS_'));
        $this->display();
    }

    /**
     * 某一活动的报名人员列表
     * ct: 2015-07-09 17:09 by ylx
     */
    public function user_list()
    {

        $aid = I('get.aid');
        if(empty($aid)) {
            $this->error(L('_NO_ACTIVITY_'));
        }
        $activity_info = D('Activity')->where(array('guid' => $aid,'is_del'=>0))->find();
        if(empty($activity_info)) {
            $this->error(L('_NO_ACTIVITY_'));
        }
        $this->assign('activity_info', $activity_info);

        // 获取新用户表单
        $this->_get_signup_form($aid);
        // 用户列表
        $this->_get_signup_userlist($aid);

        $this->assign('subject_info', D('ActivitySubject')->find_one(array('guid' => $activity_info['subject_guid'])));
    //     $this->assign('meta_title', '报名表');
    //     $this->display();
    // }
        $user_guid = $this->user_info['user_guid'];
        $user_info = D('User')->find_one(array('guid'=>$user_guid,'is_del'=>0));
        $user_detail = D('UserAttrInfo')->where(array('user_guid' => $user_guid))->find();
        $this->assign('user_info', $user_info);  
        $this->assign('user_detail',$user_detail);
        $this->assign('meta_title', L('_PERSONNEL_LIST_'));
        $this->display();
    }

     /**
     * 组装报名表单
     * CT: 2015-04-03 14:09 by ylx
     */
    private function _get_signup_form($aid)
    {
        // 增加新用户的表单
        // $signup_info = D('ActivitySignup')->find_one(array('activity_guid' => $aid));
        $build_info   = D('ActivityForm')->where(array('activity_guid' => $aid))->order('id asc')->select();
        $option_info  = D('ActivityFormOption')->where(array('activity_guid' => $aid))->field('guid,build_guid,value')->select();
        foreach($option_info as $o) {
            $format_option_info[$o['build_guid']][] = $o;
        }
        // 获取票务相关
        $tickets = M('ActivityAttrTicket')->where(array('activity_guid' => $aid, 'is_del' => '0', 'is_for_sale' => '1'))->getField('guid, num, name', true);
        $this->assign('tickets_filter', $tickets);
        foreach($tickets as $k => $t) {
            $user_width_this_ticket = M('ActivityUserTicket')->field('guid')->where(array('ticket_guid' => $t['guid'], 'status' => '2', 'is_del' => '0'))->count();
            if($user_width_this_ticket >= $t['num']) {
                unset($tickets[$k]);
            }
        }
        $this->assign('tickets', $tickets);
        $this->assign('build_info', $build_info);
        $this->assign('option_info', $format_option_info);
    }

    /**
     * ajax 加载下一页报名用户
     * CT： 2015-03-09 14:15 by ylx
     */
    public function ajax_signup_user_next_page() {
        if(IS_AJAX) {
            $aid = I('get.aid');
            $action = I('get.action', '');
            if(empty($aid)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_PARAM_ERROR_')));
            }
            // 用户列表
            list($show, $list, $is_last_page) = $this->_get_signup_userlist($aid, $action);
            // $content = $this->fetch('Index:user_list');

            if(empty($list)) {
                $this->ajaxResponse(array('status' => 'nomore', 'msg' => L('_NO_MORE_')));
            }

            $this->ajaxResponse(array('status' => 'ok', 'msg' => L('_LAOD_SUCCESS_'), 'data' => $list, 'is_last_page' => $is_last_page));
        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_ILLEGAL_OPERATION_')));
        }

    }

    /**
     * 获取报名用户列表
     * @param $aid 活动GUID
     * @param string $action 调用方action名称
     * @return array
     * CT: 2015-03-09 16:30 BY YLX
     */
    private function _get_signup_userlist($aid, $action = '') {
        $where = "t.activity_guid='$aid' and t.is_del= 0";

        $filters = json_decode(htmlspecialchars_decode(I('post.filters')), true);

        // 搜索关键字 只支持姓名和电话
//        $keyword = urldecode(I('get.keyword'));
        $keyword = $filters['keyword'];
        if(!empty($keyword)) { // 搜索姓名和电话
            $where .= " and (t.real_name like '%$keyword%' or t.mobile like '%$keyword%')";
        }

        // 票务类型过滤
        $ticket_type = $filters['t'];
        if(!empty($ticket_type)) {
            if($ticket_type != 'all'){
                $where .= " and t.ticket_guid='$ticket_type'";
            }
        }

        // 人员来源过滤
        $from = $filters['f'];
        if(isset($from)) {
            if($from != 'all') {
                $where .= " and i.type='$from'";
            }
        }

        // 签到状态过滤
        $signin_status = $filters['s'];
        if(!empty($signin_status)) {
            switch($signin_status) {
                case 'yes': // 已签到
                    $where .= " and t.status=4";
                    break;
                case 'no': // 未签到
                    $where .= " and (t.status=3 or t.status=2)";
                    break;
                case 'u0': // 未发送电子票
                case 'u1': // 电子发送失败
                case 'u2': // 已发送
                case 'u3': // 已查看
                case 'u4': // 已签到
                    $status = substr($signin_status, 1);
                    $where .= " and t.status='$status'";
                    break;
                case 'i1': // 扫码签到
                case 'i2': // 手工签到
                case 'i3': // 现场报名
                    $status = substr($signin_status, 1);
                    $where .= " and t.signin_status='$status'";
                    break;
                case 'all':
                default:
                    break;
            }
        } else {
            switch($action){
                case 'signin_chart':
                    $where .= " and t.status>=2";
                    break;
                default:
                    break;
            }
        }

//        $list = M('ActivityUserinfo')->alias('i')
//            ->join('ym_activity_user_ticket t on t.activity_guid = i.activity_guid and t.user_guid = i.user_guid')
//            ->field('*, t.guid as tid, i.guid as guid')
//            ->where($where)
//            ->order('i.created_at DESC')
//            ->page(I('get.p', '1'), C('NUM_PER_PAGE', null, 10))
//            ->select();
////             echo M("ActivityUserinfo")->getLastSQL();die;
//        // var_dump($list);
//        // 使用page类,实现分类
//        $count      = M('ActivityUserinfo')->alias('i')->join('ym_activity_user_ticket t on t.activity_guid = i.activity_guid and t.user_guid = i.user_guid')
//                                                ->where($where)->count();// 查询满足要求的总记录数

        //优化sql
        $list = M('ActivityUserTicket')->alias('t')
            ->field('t.guid as tid, t.userinfo_guid as guid,user_guid,activity_guid,real_name,mobile,ticket_guid,ticket_name,status,signin_status')
            ->where($where)
            ->order('t.created_at DESC')
//            ->page(I('get.p', '1'), C('NUM_PER_PAGE', null, 10))
            ->page(I('get.p', '1'), C('NUM_PER_PAGE', null, 10))
            ->select();


        // 使用page类,实现分类
//        $count      = M('ActivityUserinfo')->alias('i')
//            ->join('ym_activity_user_ticket t on t.activity_guid = i.activity_guid and t.user_guid = i.user_guid')
//            ->where($where)->count();// 查询满足要求的总记录数
        $count = M('ActivityUserTicket')->alias('t')
            ->where($where)
            ->count();

        $Page       = new \Think\Page($count, C('NUM_PER_PAGE', null, 10));// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出

        $total_pages = $Page->totalPages;
        $is_last_page = false;
        if(I('get.p', 1) == $total_pages || empty($total_pages)){
            $is_last_page = true;
        }
        $this->assign('is_last_page', $is_last_page);

        foreach($list as $k => $l){
            // 来源
            $from = C('ACTIVITY_SIGNUP_FROM');
            $list[$k]['from'] = $from[$l['type']];

            // 获取邮箱
            $email = M('ActivityUserinfoOther')
                ->where(array('activity_guid' => $aid, 'signup_userinfo_guid' => $l['guid'], 'ym_type' => 'email'))
                ->getField('value');

            if(is_valid_email($email)) {
                $list[$k]['email'] = $email;
            } else {
                $list[$k]['email'] = '';
            }

            // 其它信息
            // $taichukuan='';
           $other = M('ActivityUserinfoOther')->where(array('activity_guid' => $aid,'userinfo_guid' => $l['guid'],'is_del' => '0'))->order('id asc')->select();
             // $other = M('ActivitySignupUserinfoOther')->where(array('signup_userinfo_guid' => $l['guid'], 'is_del' => '0'))->order('id asc')->select();
           // foreach($other as $other_k => $o) {
            
           //     $vals = explode('_____', $o['value']);
           //     if(count($vals) <= 1) {
           //         $v_str = $o['value'];
           //     } else {
           //         $v_str = implode(', ', $vals);
           //     }
           //     $other[$other_k]['value'] = $v_str;
              
           // }
           $list[$k]['other'] = $other;

            // 票务相关
            $ticket_status = C('ACTIVITY_TICKET_STATUS');
            $ticket_signin_status = C('ACTIVITY_TICKET_SIGNIN_STATUS');
            $ticket_status_tag = C('ACTIVITY_TICKET_STATUS_TAG');
            $ticket['ticket_status'] = isset($ticket_signin_status[$l['signin_status']]) ? $ticket_signin_status[$l['signin_status']] : $ticket_status[$l['status']];
            $ticket['ticket_status_tag'] = $ticket_status_tag[$l['status']];
            $ticket['ticket_name'] = $l['ticket_name'];
            $ticket['ticket_guid'] = $l['ticket_guid'];
            $list[$k]['ticket'] = $ticket;
            $list[$k]['category'] =1;
            
        }
        
        $this->assign('other', $other);
        $this->assign('user_list', $list);
        $this->assign('user_count', D('ActivityUserinfo')->alias('i')->join('ym_activity_user_ticket t on t.activity_guid = i.activity_guid and t.user_guid = i.user_guid')->get_count($where));
        $this->assign('page', $show);
        return array($show, $list, $is_last_page);
    }
    
    /**
     * 报名用户详情
     */
    public function signup_userdetail() {

        $userinfo_guid = I('get.uid');
        $info = D('ActivityUserinfo')->where(array('guid' => $userinfo_guid, 'is_del' => '0'))->find();
        $other = M('ActivityUserinfoOther')
            ->where(array('signup_userinfo_guid' => $userinfo_guid, 'is_del' => '0'))
            ->getField('build_guid, guid, value');
        $this->assign('info', $info);
        $this->assign('other', $other);

        // 组装form
        $user_guid = $info['user_guid'];
        $activity_guid = $info['activity_guid'];
        $activity_info = D('Activity')->find_one(array('guid' => $activity_guid));
        // $signup_info = D('ActivitySignup')->find_one(array('activity_guid' => $activity_guid));

        //判断是否走票务
        $total_ticket = M('ActivityAttrTicket')->field('SUM(num) as total')
            ->where(array('activity_guid' => $activity_guid, 'is_del' => '0', 'is_for_sale' => '1'))
            ->find();
        if(intval($total_ticket['total']) > 0) { // 如果票务已经被设置， 走票务
            $tickets = M('ActivityAttrTicket')
                ->where(array('activity_guid' => $activity_guid, 'is_del' => '0', 'is_for_sale' => '1'))
                ->getField('guid, num, name', true);
            $this->assign('tickets', $tickets);

            // 当前用户所选的票务
            $user_ticket = M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'user_guid' => $info['user_guid']))->find();
            $this->assign('user_ticket', $user_ticket);
        }

        // 提交报名
        if(IS_POST) {
            $params = I('post.');
            if(empty($params) || empty($params['info']['real_name']) || empty($params['info']['mobile'])) {
                $this->error(L('_SAVE_FAILED_'));
            }

            $time = time();
            // 保存userinfo
            $info = $params['info'];
            $data_info = array_merge($info, array('updated_at' => $time));
            $model_userinfo = D('ActivitySignupUserinfo');
            list($check, $r) = $model_userinfo->update(array('guid' => $userinfo_guid), $data_info);
            if(!$check) {
                $this->error($r);
            }
            if(!$r) {
                $this->error(L('_SAVE_FAILED_'));
            }

            // 保存其它信息
            $other = $params['other'];
            if(!empty($other)){
                foreach($other as $o) {
                    $data_other = array(
                        'value' => is_array($o['value']) ? implode('_____', $o['value']) : (isset($o['value']) ? $o['value'] : ''),
                        'updated_at' => $time
                    );
                    M('ActivitySignupUserinfoOther')->where(array('guid' => $o['other_info_guid']))->save($data_other);
                }
            }

            // 保存票务信息
            $ticket_guid = I('post.ticket');
            $data_ticket = array(
                'mobile' => $params['info']['mobile'],
                'ticket_guid' => !empty($ticket_guid) ? I('post.ticket') : 'nolimit',
                'ticket_name' => !empty($ticket_guid) ? $tickets[I('post.ticket')]['name'] : 'nolimit',
                'updated_at' => $time
            );
            M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'user_guid' => $user_guid))->save($data_ticket);

            //时间轴-参加活动-报名
            $this->success(L('_UPDATE_SUCCESS_'));exit();
        }

        $build_info   = D('ActivityForm')->where(array('activity_guid' => $activity_guid))->order('id asc')->select();
        $option_info  = D('ActivityFormOption')->where(array('activity_guid' => $activity_guid))->field('guid,build_guid,value')->select();
        foreach($option_info as $o) {
            $format_option_info[$o['build_guid']][] = $o;
        }
        $org_guid = $this->user_info['org_guid'];
        $org_info = D('Org')->find_one(array('guid'=>$org_guid));
        $this->assign('org_info', $org_info);

        $this->assign('activity_info', $activity_info);
        $this->assign('signup_info', $signup_info);
        $this->assign('build_info', $build_info);
        $this->assign('option_info', $format_option_info);

        $this->assign('meta_title', L('_PERSONNEL_DETAIL_'));
        $this->display();
    }

    
    /**
     * 检查签到用户是否存在
     * ct: 2015-07-09 17:09 by ylx
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
                          $field => $value,
            ))->find();
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
            ->where(array('signup_userinfo_guid' => $userinfo['guid'], 'is_del' => '0'))
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

        // 返回信息
        $this->output_data($userinfo);
    }

    /**
     * 进行签到
     * ct: 2015-07-09 17:09 by ylx
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

        // 返回结果
        if($result) {
            $this->output_data();
        } else {
            $this->output_error('10011');
        }
    }

    /**
     * 退出登录
     * ct: 2015-07-09 17:09 by ylx
     */
    public function logout()
    {
        $this->check_request_method('post');
        $guid = $this->user_info['guid'];
        if (empty($guid)) {
            $this->output_error('10003'); // 参数错误
        }
        $res = D('UserDevice')->logoutAll($guid);
        if ($res < 1) {
            $this->output_error('10009'); // 未找到相关数据
        }
        // 清除redis缓存
        $token = $this->_request_params['Token'];
        S($token.':user_device', null);
        S($token.':user_info', null);
        $this->output_data();
    }

    /**
     * 重新打开APP时重新登录
     * ct: 2015-07-09 17:09 by ylx
     */
    public function relogin()
    {
        $this->check_request_method('post');
        $user_info = $this->user_info;
        $user_guid = $user_info['guid'];
        $token = $this->_request_params['Token'];

        // 更新token时间
        $time = time();
        $new_token = md5($token.$time);
        $token_num = rand(10,99);
        $data = array(
            'token'         => $new_token,
            'token_num'     => $token_num
        );
        $where = array('user_guid' => $user_guid, 'token' => $token);
        $model_device = D('UserDevice');
        $check_data = $model_device->where($where)->create($data);
        if(!$check_data) {
            $this->output_error('10023');
        }
        if($model_device->save()){
            //清除Redis缓存, 生成新的
            S($token.':user_device', null);
            $token_info = D('UserDevice')->getTokenInfo(array('user_guid' => $user_guid, 'token' => $new_token));
            S($token.':user_device', $token_info, C('TOKEN_EXPIRE'));    //设置Redis缓存
            // 返回JSON数组
            $data = array('guid'      => $user_info['guid'],
                          'org_guid'     => $user_info['org_guid'],
                          'is_active' => $user_info['is_active'],
                          'token'     => $new_token,
                          'token_num' => $token_num
            );
            header('Token:'.$new_token);
            $this->output_data($data);
        } else {
            $this->output_error('10023');
        }
    }
}
