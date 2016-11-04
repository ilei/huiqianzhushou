<?php
namespace Signin\Controller;
use Org\Api\YmSMS;
use Think\Image;

/**
 * 签到管理
 * ct: 2015-07-09 17:09 by ylx
 * ut: 2015-08-12 17:00 by zyz
 */

class SigninController extends BaseUserController{

    private $ACTIVITY_ARTICLE; // 文章
    private $ACTIVITY_VOTE;    // 投票
    private $ACTIVITY_DISCUSS; // 讨论
    private $ACTIVITY_SIGNUP;  // 报名
    private $ACTIVITY_QUESTIONNAIRE; //问卷

    public function __construct()
    {
        parent::__construct();
        $activity_type = C('ACTIVITY_TYPE');
        $this->ACTIVITY_ARTICLE = isset($activity_type['ACTIVITY_ARTICLE']) ? $activity_type['ACTIVITY_ARTICLE'] : 1;
        $this->ACTIVITY_VOTE = isset($activity_type['ACTIVITY_VOTE']) ? $activity_type['ACTIVITY_VOTE'] : 2;
        $this->ACTIVITY_DISCUSS = isset($activity_type['ACTIVITY_DISCUSS']) ? $activity_type['ACTIVITY_DISCUSS'] : 3;
        $this->ACTIVITY_SIGNUP = isset($activity_type['ACTIVITY_SIGNUP']) ? $activity_type['ACTIVITY_SIGNUP'] : 4;
        $this->ACTIVITY_QUESTIONNAIRE = isset($activity_type['ACTIVITY_QUESTIONNAIRE']) ? $activity_type['ACTIVITY_QUESTIONNAIRE'] : 5;
    }

    /**
     * 签到页面
     * ct: 2015-07-09 17:09 by ylx
     */
    public function signin()
    {   
        header('Access-Control-Allow-Origin:*');
        $aid = I('get.aid');
        if(empty($aid)){
            $this->error(L('_NOT_DEFINE_ACTIVITY_'));
        }

        // 活动详情
        $activity_info = D('Activity')->where(array('guid' => $aid))->find();
        if(empty($activity_info)){
            $this->error(L('_NOT_DEFINE_ACTIVITY_'));
        }
        if ($activity_info['status']!=1 || $activity_info['is_verify']!=1) {
            $this->error(L('_ACTIVITY_OVER_'));
        }
        $this->assign('activity_info', $activity_info);

        // 获取报名表单
        $this->_get_signup_form($aid);

        $this->assign('aid', $aid);
        $this->assign('meta_title', L('_ONLINE_CHECK_IN_'));
        $this->display();
    }

    /**
     * 组装报名表单
     * CT: 2015-04-03 14:09 by ylx
     */
    private function _get_signup_form($aid)
    {
        // 增加新用户的表单
        //signup表和到了activity表，build和option沒signup
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
     * 签到统计页面
     * ct: 2015-07-09 17:09 by ylx
     */
    public function signin_chart()
    {
        $aid = I('get.aid');
        if(empty($aid)){
            $this->error(L('_NOT_DEFINE_ACTIVITY_'));
        }

        $activity_info = D('Activity')->where(array('guid' => $aid))->find();
        $this->assign('activity_info', $activity_info);



        // 获取票务相关
        $tickets = M('ActivityAttrTicket')->where(array('activity_guid' => $aid, 'is_del' => '0', 'is_for_sale' => '1'))->getField('guid, num, name', true);
        $this->assign('tickets', $tickets);

        // 获取用户列表
        $this->_get_signup_userlist($aid, 'signin_chart');

        // 签到统计
        // 签到状况统计
        $status_statistic_result = M('ActivityUserTicket')
            ->field('status, count(status) as sum')
            ->where(array('activity_guid' => $aid, 'is_del' => '0', 'status' => array('in', array(2, 3, 4))))
            ->group('status')
            ->select();
        $status_statistic = array( 'title' => array(L('_NOT_CHECK_IN_'),L('_ALREADY_CHECK_IN_')), 'data' => array() );
        $i=0;

        foreach($status_statistic_result as $s){
            if($s['status'] == 2){
                $status_statistic['data'][$i]['name'] = L('_NOT_CHECK_IN_');
                $status_statistic['data'][$i]['value'] = $s['sum'];
            } else if ($s['status'] == 3) {
                if(!empty($status_statistic['data'][$i-1]['value'])){
                    $i = $i-1;
                }
                $status_statistic['data'][$i]['name'] = L('_NOT_CHECK_IN_');
                $status_statistic['data'][$i]['value'] += $s['sum'];
            } else if($s['status'] == 4) {
                $status_statistic['data'][$i]['name'] = L('_ALREADY_CHECK_IN_');
                $status_statistic['data'][$i]['value'] = $s['sum'];
            }
            $i++;

        }
        $status_statistic['total'] = $status_statistic['data'][0]['value']+$status_statistic['data'][1]['value'];
        $this->assign('status_statistic', $status_statistic);

        // 签到方式统计
        $type_statistic_result = M('ActivityUserTicket')
            ->field('signin_status, count(signin_status) as sum')
            ->where(array('activity_guid' => $aid, 'is_del' => '0', 'status' => 4, 'signin_status' => array('gt', 0)))
            ->group('signin_status')
            ->select();
        $this->assign('type_statistic', $type_statistic_result);

        $this->assign('aid', $aid);
        $this->assign('meta_title', L('_CHECK_STATISTICS_'));
        $this->display();
    }

    /**
     * AJAX签到
     * CT: 2015-08-12 17:14 BY zyz
     */
    public function ajax_signin()
    {
        if(IS_AJAX) {
            $user_ticket_guid = I('post.user_ticket_guid');
            $model = M('ActivityUserTicket');

            // 获取票务信息
            $ticket = M('ActivityUserTicket')
                ->field('guid, ticket_guid, ticket_verify_time, activity_guid, mobile, user_guid')
                ->where(array('guid' => $user_ticket_guid, 'status' => array('in', array(2, 3, 4)), 'is_del' => 0))
                ->find();
            if(empty($ticket)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_CHECK_FAILED_PLEASE_RETRY_')));
            }

            // 判断验证次数
            if(strlen($ticket['ticket_guid']) == 32){ // 走票务设置
                $ticket_verify_num = M('ActivityAttrTicket')->where(array('guid' => $ticket['ticket_guid']))->getField('verify_num');
            } else { // 走参与人数， 刚默认为可难10次
                $ticket_verify_num = C('ACTIVITY_TICKET_DEFAULT_VERIFY_TIME', null, 10);
            }
            if($ticket['ticket_verify_time'] >= $ticket_verify_num) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_TICKET_WAS_INVALID_')));
            }

            // 进行签到
            $res = $model->where(array('guid' => $user_ticket_guid))->find();
            if($res['signin_status'] != 3){
                $signin_type = I('post.signin_type');
            }else{
                $signin_type = 3;
            }
            $auth = $this->kookeg_auth_data();
            $guid = $auth['guid'];
            $username = $auth['username'];
            $data = array(
                'ticket_verify_time' => array('exp', 'ticket_verify_time+1'),
                'status'             => 4,
                'signin_status'      => $signin_type,
                'updated_at'         => time(),
                'username_guid'      => $guid,
                'username'           => $username
            );
            $result = $model->where(array('guid' => $user_ticket_guid))->save($data);
            M('MsgContent')->where(array('ticket_guid' => $user_ticket_guid))->save(array('status' => 4));
            // 返回结果
            if($result) {
                $this->ajaxResponse(array('status' => 'ok', 'msg' => L('_CHECK_SUCCESS_')));
            } else {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_CHECK_FAILED_PLEASE_RETRY_')));
            }
        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_PARAMETER_ERROR_')));
        }
    }

    /**
     * AJAX检查签到人员
     * CT: 2015-08-12 17:14 BY zyz
     */
    public function ajax_signin_check_user()
    {
        if(IS_AJAX) {
            $aid = I('post.aid');
            $value = I('post.value'); // 当手动签到时为mobile, 当二维码扫描时, 为ticket_code
            $signin_type = I('post.signin_type');
            if(empty($aid) || empty($value)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_QR_CODE_ERROR_')));
            }
            // 检查票务是否存在
            $field = $signin_type==1 ? 'ticket_code' : 'mobile';
            $ticket = M('ActivityUserTicket')
                ->where(array('activity_guid' => $aid,
                              'status' => array('in', array(2,3,4)), 'is_del' => 0, $field => array('like', '%'.$value)
                ))
                ->select();
                // echo M('ActivityUserTicket')->getLastSQL();
                // var_dump($ticket);
            if(empty($ticket)){
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_ONDEFINE_TICKET_')));
            }
            $userinfo= array();
            $from= array();
            // 获取用户信息
            $i = 0;
            foreach ($ticket as $key => $ti) {
            
            $userinfo[$key] = D('ActivityUserinfo')
                ->where(array('activity_guid' => $aid, 'user_guid' => $ti['user_guid'], 'is_del' => '0'))
                ->find();
            if(empty($userinfo)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_SYSTEM_ERROR_')));
            }
            
            
            // 来源
            $from = C('ACTIVITY_SIGNUP_FROM');
            $userinfo[$key]['from'] = $from[$userinfo[$i]['type']];

            // 其它信息
            $other = M('ActivityUserinfoOther')
                ->where(array('userinfo_guid' => $userinfo[$i]['guid'], 'is_del' => '0'))
                ->select();
            foreach($other as $other_k => $o) {
                if($o['ym_type'] == 'company') $userinfo[$i]['company'] = $o['value'];
                else if($o['ym_type'] == 'position') $userinfo[$i]['position'] = $o['value'];
                $vals = explode('_____', $o['value']);
                if(count($vals) <= 1) {
                    $v_str = $o['value'];
                } else {
                    $v_str = implode(', ', $vals);
                }
                $other[$other_k]['value'] = $v_str;
            }
            $userinfo[$key]['other'] = $other;

            // 票务相关
            $ticket_status = C('ACTIVITY_TICKET_STATUS');
            $ticket_status_tag = C('ACTIVITY_TICKET_STATUS_TAG');
            $ti['ticket_status'] = $ticket_status[$ti['status']];
            $ti['ticket_status_tag'] = $ticket_status_tag[$ti['status']];
            $ti['user_ticket_guid'] = $ti['guid'];
            $userinfo[$key]['ticket'] = $ti;

            // 判断验证次数
            if(strlen($ti['ticket_guid']) == 32){ // 走票务设置
                $ticket_verify_num = M('ActivityAttrTicket')->where(array('guid' => $ti['ticket_guid']))->getField('verify_num');
            } else { // 走参与人数， 刚默认为可验证10次
                $ticket_verify_num = C('ACTIVITY_TICKET_DEFAULT_VERIFY_TIME', null, 10);
            }
            if($ti['ticket_verify_time'] >= $ticket_verify_num) {
                $this->ajaxResponse(array('status' => 'ok', 'data' => $userinfo, 'msg' => L('_TICKET_WAS_INVALID_')));
            }
            
            // 返回信息
            // var_dump($userinfo);
            // 
            $i=$i+1;
            }
            $this->ajaxResponse(array('status' => 'ok', 'data' => $userinfo));
        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_PARAMETER_ERROR_')));
        }
    }

    /**
     * 报名用户详情
     */
    // public function signup_userdetail() {

    //     $userinfo_guid = I('get.uid');
    //     $info = D('ActivitySignupUserinfo')->where(array('guid' => $userinfo_guid, 'is_del' => '0'))->find();
    //     $other = M('ActivitySignupUserinfoOther')
    //         ->where(array('signup_userinfo_guid' => $userinfo_guid, 'is_del' => '0'))
    //         ->getField('build_guid, guid, value');
    //     $this->assign('info', $info);
    //     $this->assign('other', $other);

    //     // 组装form
    //     $user_guid = $info['user_guid'];
    //     $activity_guid = $info['activity_guid'];
    //     $activity_info = D('Activity')->find_one(array('guid' => $activity_guid));
    //     $signup_info = D('ActivitySignup')->find_one(array('activity_guid' => $activity_guid));

    //     //判断是否走票务
    //     $total_ticket = M('ActivityAttrTicket')->field('SUM(num) as total')
    //         ->where(array('activity_guid' => $activity_guid, 'is_del' => '0', 'is_for_sale' => '1'))
    //         ->find();
    //     if(intval($total_ticket['total']) > 0) { // 如果票务已经被设置， 走票务
    //         $tickets = M('ActivityAttrTicket')
    //             ->where(array('activity_guid' => $activity_guid, 'is_del' => '0', 'is_for_sale' => '1'))
    //             ->getField('guid, num, name', true);
    //         $this->assign('tickets', $tickets);

    //         // 当前用户所选的票务
    //         $user_ticket = M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'user_guid' => $info['user_guid']))->find();
    //         $this->assign('user_ticket', $user_ticket);
    //     }

    //     // 提交报名
    //     if(IS_POST) {
    //         $params = I('post.');
    //         if(empty($params) || empty($params['info']['real_name']) || empty($params['info']['mobile'])) {
    //             $this->error('保存失败，请稍后重试。');
    //         }

    //         $time = time();
    //         // 保存userinfo
    //         $info = $params['info'];
    //         $data_info = array_merge($info, array('updated_at' => $time));
    //         $model_userinfo = D('ActivitySignupUserinfo');
    //         list($check, $r) = $model_userinfo->update(array('guid' => $userinfo_guid), $data_info);
    //         if(!$check) {
    //             $this->error($r);
    //         }
    //         if(!$r) {
    //             $this->error('保存失败，请稍后重试.');
    //         }

    //         // 保存其它信息
    //         $other = $params['other'];
    //         if(!empty($other)){
    //             foreach($other as $o) {
    //                 $data_other = array(
    //                     'value' => is_array($o['value']) ? implode('_____', $o['value']) : (isset($o['value']) ? $o['value'] : ''),
    //                     'updated_at' => $time
    //                 );
    //                 M('ActivitySignupUserinfoOther')->where(array('guid' => $o['other_info_guid']))->save($data_other);
    //             }
    //         }

    //         // 保存票务信息
    //         $ticket_guid = I('post.ticket');
    //         $data_ticket = array(
    //             'mobile' => $params['info']['mobile'],
    //             'ticket_guid' => !empty($ticket_guid) ? I('post.ticket') : 'nolimit',
    //             'ticket_name' => !empty($ticket_guid) ? $tickets[I('post.ticket')]['name'] : 'nolimit',
    //             'updated_at' => $time
    //         );
    //         M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'user_guid' => $user_guid))->save($data_ticket);

    //         //时间轴-参加活动-报名
    //         $this->success('修改成功');exit();
    //     }

    //     $build_info   = D('ActivitySignupFormBuild')->where(array('signup_guid' => $signup_info['guid']))->order('id asc')->select();
    //     $option_info  = D('ActivitySignupFormOption')->where(array('signup_guid' => $signup_info['guid']))->field('guid,build_guid,value')->select();
    //     foreach($option_info as $o) {
    //         $format_option_info[$o['build_guid']][] = $o;
    //     }

    //     $this->assign('activity_info', $activity_info);
    //     $this->assign('signup_info', $signup_info);
    //     $this->assign('build_info', $build_info);
    //     $this->assign('option_info', $format_option_info);

    //     $this->assign('meta_title', '报名人员详情');
    //     $this->display();
    // }

    /**
     * 后台手动添加报名人员
     * CT： 2015-08-12 17:14 BY zyz
     */
    public function ajax_signup_add_user() {
        // 提交报名
        if(IS_POST) {
            $aid = I('get.aid');
            if(empty($aid) || strlen($aid) != 32) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_ADD_FAILED_')));
            }

//            $activity_id = M('Activity')->where('guid = "'.$aid.'"')->getField('id');
            // $signup_info = D('ActivitySignup')->find_one(array('activity_guid' => $aid));

            $params = I('post.');
            if(empty($params)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_ADD_FAILED_')));
            }

            $time = time();
            $user_guid = 'add_by_org_'.create_guid();
            // 保存userinfo
            $info = $params['info'];
            $data_info = array(
                'guid'          => create_guid(),
                'activity_guid' => $aid,
                // 'signup_guid'   => $signup_info['guid'],
                'user_guid'     => $user_guid,
                'type'          => '4',
                'created_at'    => $time,
                'updated_at'    => $time
            );
            $data_info = array_merge($data_info, $info);
            $model_userinfo = D('ActivityUserinfo');
            list($check, $r) = $model_userinfo->insert($data_info);
            if(!$check) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => $r));
            }
            if(!$r) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_ADD_FAILED_')));
            }

            // 保存其它信息
            $other = $params['other'];
            $data_other = array();
            foreach($other as $o) {
                $data_other[] = array(
                    'guid'                 => create_guid(),
                    'userinfo_guid' => $data_info['guid'],
                    'activity_guid'        => $aid,
                    'build_guid'           => $o['build_guid'],
                    'ym_type'              => $o['ym_type'],
                    'key'                  => $o['key'],
                    'value'                => is_array($o['value']) ? implode('_____', $o['value']) : (isset($o['value']) ? $o['value'] : ''),
                    'created_at'           => $time,
                    'updated_at'           => $time
                );
            }
            M('ActivityUserinfoOther')->addAll($data_other);

            // 保存票务信息
            $data_ticket = array(
                'guid'          => create_guid(),
                'activity_guid' => $aid,
                'user_guid'     => $user_guid,
                'userinfo_guid' => $data_info['guid'],
                'mobile'        => $info['mobile'],
                'ticket_guid'   => I('post.ticket', 'nolimit'),
                'ticket_name'   => I('post.ticket') != '' ? M('ActivityAttrTicket')->where(array('guid' => I('post.ticket')))->getField('name') : 'nolimit',
                'created_at'    => $time,
                'updated_at'    => $time,
				'userinfo_guid' => $data_info['guid'],
            );
            $auth = $this->kookeg_auth_data();
            $guid = $auth['guid'];
            $username = $auth['username'];
            // 若为签到页添加,则直接自动签到成功
            $is_signin = I('get.signin');
            if($is_signin == 'true'){
                $data_ticket['username'] = $username;
                $data_ticket['username_guid'] = $guid;
                $data_ticket['status'] = 4;
                $data_ticket['signin_status'] = 3;
                $data_ticket['ticket_code'] = generate_ticket_code(substr($aid, 0, 4));
                $data_ticket['ticket_verify_time'] = 1;
            }
            M('ActivityUserTicket')->add($data_ticket);

            $this->ajaxResponse(array('status' => 'ok', 'msg' => '增加成功.', 'data' => array('mobile' => $info['mobile']) ));
        }
    }

    /**
     * 检查用户是否重复报名
     *
     */
    public function ajax_check_signup_user() {
        $params = I('post.');
        $aid = $params['aid'];
        $kv = $params['info'];
        $value = current($kv);
        $key = key($kv);

        switch($key) {
            case 'mobile':
                $result = M('ActivityUserinfo')->where(array($key=>$value, 'activity_guid'=>$aid,'is_del'=>0))->find();
                echo empty($result) ? 'true' : 'false';exit();
                break;
            default:
                echo 'true';exit();
                break;
        }
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
                $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_PARAMETER_ERROR_')));
            }
            // 用户列表
            list($show, $list, $is_last_page) = $this->_get_signup_userlist($aid, $action);

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
//        $where = "i.activity_guid='$aid' and i.is_del=0 and t.is_del=0";
        $where = "t.activity_guid='$aid' and t.is_del=0";
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
//        $from = $filters['f'];
//        if(isset($from)) {
//            if($from != 'all') {
//                $where .= " and i.type='$from'";
//            }
//        }

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
                    $where .= " and t.status in(2,3,4)";
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

            // 其它信息
           $other = M('ActivityUserinfoOther')->where(array('activity_guid' => $aid,'userinfo_guid' => $l['guid'], 'is_del' => '0'))->order('id asc')->select();
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
            $list[$k]['category'] =2;
        }


        $this->assign('other', $other);
        $this->assign('user_list', $list);
//        $this->assign('user_count', D('ActivityUserinfo')->alias('i')->join('ym_activity_user_ticket t on t.activity_guid = i.activity_guid and t.user_guid = i.user_guid')->get_count($where));
        $this->assign('page', $show);
        return array($show, $list, $is_last_page);
    }



    /**
     * 报名活动名单导出Excel
     * CT：2015.02.06 09:55 by ylx
     */
    public function signup_export() {
        $info_guids = I('post.ck');
        $aid = I('get.aid');
        $act = I('post.batch_op', 'export');

        if(empty($aid) || empty($act)) {
            $this->error('非法操作, 请重试.');
        }
        // 获到表格大标题
        $activity_name = M('Activity')->where(array('guid' => $aid))->getField('name');
//        $main_title = '活动人员列表：'.$activity_name;

        // 获取表格头
        $form_build = M('ActivityForm')->where(array('activity_guid' => $aid, 'is_del' => '0'))
            ->order('sort desc')->getField('name', true);
        array_unshift($form_build, '序号');
        array_push($form_build, '签到状况');

        // 获取表格内容
        $where_userinfo = array('i.activity_guid' => $aid, 'i.is_del' => '0');
        //判断$info_guids为空时，是导出全部数据
        if(empty($info_guids)) {
            $userinfo_guid = D('ActivityUserTicket')->field('user_guid')->where(array('activity_guid' => $aid,'status' => array('gt',1),'is_del' => 0))->select();
            foreach($userinfo_guid as $k=>$v){
                $info_guids[] = $v['user_guid'];
            }
        }
        $where_userinfo['i.user_guid'] = array('in',$info_guids);
        $user_info = M('ActivityUserinfo')->alias('i')
            ->where($where_userinfo)
            ->select();

        $status = D('ActivityUserTicket')->field('userinfo_guid,status')->where(array('activity_guid' => $aid,'status' => array('gt',1),'is_del' => 0))->select();


        foreach($status as $k=>$v){
            $userinfo_status[$v['userinfo_guid']]['status'] = $v['status'];
        }
        foreach($user_info as $k=>$v){
            $user_info_guids[] = $v['guid'];
            $user_info[$k]['status'] = $userinfo_status[$v['guid']]['status'];
        }


        if(!empty($info_guids)) {
            $user_info_other = M('ActivityUserinfoOther')
                ->field('value,userinfo_guid')
                ->where(array('activity_guid' => $aid, 'userinfo_guid' => array('in', $user_info_guids), 'is_del' => '0'))
                ->order('id asc')
                ->select();
        }else{
            $user_info_other = M('ActivityUserinfoOther')
                ->field('value,userinfo_guid')
                ->where(array('activity_guid' => $aid, 'is_del' => '0'))
                ->order('id asc')
                ->select();

        }

        $data = array();
        $i = 1;
        foreach($user_info as $k => $info) {
            $data[$info['guid']][] = $i;
            $data[$info['guid']][] = $info['real_name'];
            $data[$info['guid']][] = $info['mobile'];
            $i++;
        }
        foreach ($user_info_other as $k => $other) {
            $data[$other['userinfo_guid']][] = str_replace("\r\n","",$other['value']);
        }

        foreach($user_info as $k => $info) {
            switch($info['status']) {
                case 0:
                    $status = '未发送电子票';
                    break;
                case 1:
                    $status = '电子票发送失败';
                    break;
                case 2:
                    $status = '电子票已发送';
                    break;
                case 3:
                    $status = '电子票已查看';
                    if($info['check_source'] == 1){
                        $status .= '(sms)';
                    } else if($info['check_source'] == 2) {
                        $status .= '(email)';
                    } else {
                        $status .= '(未知来源)';
                    }
                    break;
                case 4:
                    $status = '已签到';
                    $signin_status = array(
                        1 => '扫码签到',
                        2 => '手动签到',
                        3 => '现场报名'
                    );
                    $status .= '('.$signin_status[$info['signin_status']].')';
                    break;
                default:
                    $status = '';
                    break;
            }
                $data[$info['guid']][] = $status;
        }

        $del = ',';
        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'macintosh')){
            $del = ';';
        }

        $activity_name = iconv('utf-8', 'GB2312//TRANSLIT', $activity_name);
        $str  = '活动名称:';
        $str  = iconv('utf-8', 'gb2312', $str) . $activity_name . "\n\n";
        foreach($form_build as $value_name){
            $value_name = iconv('utf-8', 'gb2312', $value_name);
            $str .= "{$value_name}{$del}";
        }
        $str  = trim($str,$del);
        $str .= "\n";
        foreach($data as $key => $value){
            foreach($value as $k => $v){
                $v = iconv('utf-8', 'gb2312', $v);
                $str .= (string)$v.$del;
            }
            $str = trim($str,$del);
            $str .= "\n";
        }
        $str .= "\n\n".iconv('utf-8', 'gb2312', '总计:') . count($user_info);
        $filename = time().'.csv';
        export_csv($filename, $str);

//        return D('Excel')->export($main_title, $form_build, $data, date('YmdHis'), array(array('总人数: ', count($data))));
    }
}
