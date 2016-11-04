<?php
namespace Home\Controller;
//use       Think\Image;
use Controls\Control\PagerControl;
use Controls\Model\PagerControlModel;
use Think\Upload;

/**
 *
 *
 *
 **/

class RegistrationController extends BaseController{

    public function __construct(){
        parent::__construct();
        layout('layout_new');
        $this->css[] = 'common/css/DateTimePicker.css';
        $this->css[] = 'meetelf/css/home.registration.load-css.css';
        $this->title = '参会人员管理';
    }

    /**
     * 报表用户列表
     */
    public function signup_userinfo()
    {
        $this->css[] = 'meetelf/css/create-activities.css';
        $this->main  = '/Public/meetelf/home/js/build/signup_userinfo.js';
        //用户信息
        $auth = $this->get_auth_session();

        $aid = I('get.aid');
        if (empty($aid)) {
            $this->error('活动不存在.');
        }
        $activity_info = D('Activity')->where(array('guid' => $aid,'user_guid' => $auth['guid']))->find();
        if (empty($activity_info)) {
            $this->error('活动不存在.');
        }
        if ($activity_info['is_verify'] != 1) {
            $this->error('活动没有通过审核.');
        }
        $this->assign('activity_info', $activity_info);
        if(!empty($activity_info)){
            $activity_info['activity_is_verify'] = $activity_info['is_verify'];
        }
        $this->assign('act', $activity_info);

        // 获取新用户表单
        $this->_get_signup_form($aid);

        // 是否显示发送邮箱
        $this->assign('is_send_mail', M('ActivityForm')->where(array('activity_guid' => $aid, 'ym_type' => 'email', 'is_required' => '1'))->find());

        // 获取社团余额

        $balance = M('UserAccount')->field('balance')->where(array('account_guid' => $auth['guid'], 'status' => 1))->find();
        $this->assign('message_nums', yuan_to_fen($balance['balance'], false));

        //$org_info = M('Org')->field('num_sms, num_email')->where(array('guid' => $auth['guid']))->find();
        //$this->assign('org_info', $org_info);

        // 用户列表
        $this->_get_signup_userlist($aid);

        $this->assign('subject_info', D('ActivitySubject')->find_one(array('guid' => $activity_info['subject_guid'])));
        $this->show();
    }

    /**
     * 组装报名表单
     * CT: 2015-04-03 14:09 by ylx
     */
    private function _get_signup_form($aid)
    {
        // 增加新用户的表单
        $build_info  = D('ActivityForm')->where(array('activity_guid' => $aid))->order('id asc')->select();
        $option_info = D('ActivityFormOption')->where(array('activity_guid' => $aid))->field('guid,build_guid,value')->select();
        foreach ($option_info as $o) {
            $format_option_info[$o['build_guid']][] = $o;
        }
        // 获取票务相关
        $tickets = M('ActivityAttrTicket')->where(array('activity_guid' => $aid, 'is_del' => '0', 'is_for_sale' => '1'))->getField('guid, num, name', true);
        $tickets_ticket = M('ActivityAttrTicket')->where(array('activity_guid' => $aid, 'is_del' => '0'))->getField('guid, num, name', true);
        $this->assign('tickets_filter', $tickets);
        $this->assign('tickets_filter_ticket', $tickets_ticket);
        foreach ($tickets as $k => $t) {
            $user_width_this_ticket = M('ActivityUserTicket')->field('guid')->where(array('ticket_guid' => $t['guid'], 'status' => '2', 'is_del' => '0'))->count();
            if ($user_width_this_ticket >= $t['num']) {
                unset($tickets[$k]);
            }
        }
        $this->assign('tickets', $tickets);
        $this->assign('build_info', $build_info);
        $this->assign('_modal_add_signup_user_js', $this->fetch('_modal_add_signup_user_js'));
        $this->assign('option_info', $format_option_info);
    }

    /**
     * 获取报名用户列表
     * @param $aid 活动GUID
     * @param string $action 调用方action名称
     * @return array
     * CT: 2015-03-09 16:30 BY YLX
     */
    private function _get_signup_userlist($aid, $action = '', $ajax='')
    {
        $where = "t.activity_guid='$aid' and t.is_del=0";

//        var_dump(I('post.'),I('get.'));die;
        if(empty($ajax)){
            $filters = json_decode(htmlspecialchars_decode(I('post.filters')), true);
        }else{
            $filters = I('post.filters');
        }

        // 票务类型过滤
        $ticket_type = $filters['t'];
        $ajax_ticket_name = '全部';
        if (!empty($ticket_type)) {
            if ($ticket_type != 'all') {
                $ajax_ticket_name = D('ActivityAttrTicket')->where(array('guid' => $ticket_type))->find()['name'];
                $where .= " and t.ticket_guid='$ticket_type'";
            }
        }

//        // 人员来源过滤
//        $from = $filters['f'];
//        if (isset($from)) {
//            if ($from != 'all') {
//                $where .= " and i.type='$from'";
//            }
//        }

        // 签到状态过滤
        $signin_status = $filters['s'];
        if (!empty($signin_status)) {
            switch ($signin_status) {
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
                case 'u5': // 已签到
                    $status = substr($signin_status, 1);
                    $where .= " and t.status='$status'";
                    break;
                case 'i1': // 扫码签到
                case 'i2': // 手动签到
                case 'i3': // 现场报名
                    $status = substr($signin_status, 1);
                    $where .= " and t.signin_status='$status' and t.status = '4'";
                    break;
                case 'all':
                default:
                    break;
            }
        } else {
            switch ($action) {
                case 'signin_chart':
                    $where .= " and t.status in(2,3,4,5)";
                    break;
                default:
                    break;
            }
        }

        // 搜索关键字 只支持姓名和电话
//        $keyword = urldecode(I('get.keyword'));
        $keyword = $filters['keyword'];
        if (!empty($keyword)) { // 搜索姓名和电话
            $where .= " and (t.real_name like '%$keyword%' or t.mobile like '%$keyword%')";
        }

        //分页数
        if(empty($filters['p'])){
            $filters['p'] = '1';
        }
        if(empty($filters['i'])){
            $filters['i'] = '10';
        }
        //list数据集合
//        $list = M('ActivityUserinfo')->alias('i')
//            ->join('ym_activity_user_ticket t on t.userinfo_guid = i.guid')
//            ->field('*, t.guid as tid, i.guid as guid')
//            ->where($where)
//            ->order('i.created_at DESC')
////            ->page(I('get.p', '1'), C('NUM_PER_PAGE', null, 10))
//            ->page($filters['p'], $filters['i'])
//            ->select();
        $list = M('ActivityUserTicket')->alias('t')
            ->field('t.guid as tid, t.userinfo_guid as guid,user_guid,activity_guid,real_name,mobile,ticket_guid,ticket_name,status,signin_status')
            ->where($where)
            ->order('t.created_at DESC')
//            ->page(I('get.p', '1'), C('NUM_PER_PAGE', null, 10))
            ->page($filters['p'], $filters['i'])
            ->select();

//        echo M('ActivityUserTicket')->getLastSql();die;

        foreach($list as $k => $v){
            $list[$k]['email'] = D('ActivityUserinfo')->field('email')->where(array('guid' => $v['guid']))->find()['email'];
        }

        switch ($signin_status) {
            case 'yes': // 已签到
                $ajax_ticket_status = "已签到";
                break;
            case 'no': // 未签到
                $ajax_ticket_status = "未签到";
                break;
            case 'u0': // 未发送电子票
                $ajax_ticket_status = "未发送";
                break;
            case 'u1': // 电子发送失败
                $ajax_ticket_status = "发送失败";
                break;
            case 'u2': // 已发送
                $ajax_ticket_status = "已发送";
                break;
            case 'u3': // 已查看
                $ajax_ticket_status = "已查看";
                break;
            case 'u5': //正在发送
                $ajax_ticket_status = "正在发送";
                break;
            case 'i1': // 扫码签到
                $ajax_ticket_status = "扫码签到";
                break;
            case 'i2': // 手动签到
                $ajax_ticket_status = "手动签到";
                break;
            case 'i3': // 现场报名
                $ajax_ticket_status = "现场报名";
                break;
            case 'all':
                $ajax_ticket_status = "全部";
                break;
            default:
                break;
        }

//        // 使用page类,实现分类
//        $count = M('ActivityUserinfo')
//            ->alias('i')
//            ->join('ym_activity_user_ticket t on t.userinfo_guid = i.guid')
//            ->where($where)
//            ->count();
        $count = M('ActivityUserTicket')->alias('t')
//            ->alias('i')
//            ->join('ym_activity_user_ticket t on t.userinfo_guid = i.guid')
            ->where($where)
            ->count();

        //返回用户全部数据
        if(count($list) == 0){
            $surplus_count = 0;
        }else if(count($list) <= $filters['i']){
            $surplus_count = count($list);
        }

        $this->assign('surplus_count',$surplus_count);

        $i = 0;
        foreach ($list as $k => $l) {

            if($filters['p'] == 1){
                $i++;
                $list[$k]['number'] = $i;
            }else{
                $i++;
                $list[$k]['number'] = (intval($filters['p']) - 1) * intval($filters['i']) + $i;
            }


            // 票务相关
            $ticket_status               = C('ACTIVITY_TICKET_STATUS');
            $ticket_signin_status        = C('ACTIVITY_TICKET_SIGNIN_STATUS');
            $ticket_status_tag           = C('ACTIVITY_TICKET_STATUS_TAG');
            $ticket['ticket_status']     = isset($ticket_signin_status[$l['signin_status']]) ? $ticket_signin_status[$l['signin_status']] : $ticket_status[$l['status']];
            $ticket['ticket_status_tag'] = $ticket_status_tag[$l['status']];
            $ticket['ticket_name']       = $l['ticket_name'];
            $ticket['status']            = $l['status'];
            $ticket['signin_status']     = $l['signin_status'];
            $ticket['tid']       = $l['tid'];
            $list[$k]['ticket']          = $ticket;

        }

        if(!empty($ajax)){
            $model = new PagerControlModel($filters['p'],$count,$filters['i']);
            $pager = new PagerControl($model,PagerControl::$Enum_First_Prev_Number_Next_Last);
            $pager = $pager->fetch();
            $return_data['pager'] = $pager;
            $this->assign('user_list',$list);
            $return_data['ajax_ticket_status'] = $ajax_ticket_status;
            $return_data['ajax_ticket_name'] = $ajax_ticket_name;
            // 是否显示发送邮箱
            $this->assign('is_send_mail', M('ActivityForm')->where(array('activity_guid' => $aid, 'ym_type' => 'email', 'is_required' => '1'))->find());
            $return_data['count'] = count($list);
            $return_data['user_count'] = $count;
            $return_data['data'] = $this->fetch('_user_list_tbody');
            layout(false);
            $this->ajaxReturn($return_data,'json');
        }else{
            $model = new PagerControlModel($filters['p'],$count,$filters['i']);
            $pager = new PagerControl($model,PagerControl::$Enum_First_Prev_Number_Next_Last);
            $this->assign('pager',$pager->fetch());
        }

        $this->assign('user_list', $list);
//        $this->assign('pages', $filters['p']);
//        $this->assign('numbers', $filters['i']);
        $this->assign('count', $count);
        $this->assign('user_list_tbody', $this->fetch('_user_list_tbody'));
//        $this->assign('user_count', count($list));
        return array($list);
    }

    /**
     * 报名活动名单导出Excel  全部数据
     * CT：2015.02.06 09:55 by ylx
     */
    public function signup_export()
    {

        $derive_type = I('get.derive_type');
        $aid        = I('get.aid');
        if(empty($derive_type) || empty($aid)){
            $this->error('数据错误请重新操作');
        }

        if($derive_type == 'all'){

//            $dal = D('ActivityUserinfo');
            $dal = D('ActivityUserTicket');

            $user_guids = $dal->field('userinfo_guid')->where(array('activity_guid' => $aid,'is_del' => 0))->select();
            foreach($user_guids as $k=>$v){
                $info_guids[] = $v['userinfo_guid'];
            }
        }

        if (empty($info_guids)) {
            $this->error('请选择要操作的用户.');
        }

        if (empty($aid)) {
            $this->error('非法操作, 请重试.');
        }
        // 获到表格大标题
        $activity_name = M('Activity')->where(array('guid' => $aid))->getField('name');
        $main_title    = '活动人员列表：' . $activity_name;

        // 获取表格头
        $form_build = M('ActivityForm')->where(array('activity_guid' => $aid, 'is_del' => '0'))
            ->order('sort desc')->getField('name', true);
        array_unshift($form_build, '序号');
//        $where = array('i.activity_guid' => $aid, 'i.is_del' => '0','i.guid' => array('in',$info_guids));
        $user_info = M('ActivityUserinfo')
            ->field('guid,real_name,mobile,email')
            ->where(array('guid' => array('in',$info_guids)))
//            ->where(array('guid' => array('in',$info_guids)))
            ->select();

        foreach($user_info as $k=>$v){
            $user_info_guids[] = $v['guid'];
        }

        $user_info_other = M('ActivityUserinfoOther')
            ->field('value,userinfo_guid')
//            ->where(array('activity_guid' => $aid, 'is_del' => '0'))
            ->where(array('userinfo_guid' => array('in',$info_guids)))
            ->order('id asc')
            ->select();
        $data            = array();
        $i               = 1;
        foreach ($user_info as $k => $info) {
            $data[$info['guid']][] = $i;
            $data[$info['guid']][] = $info['real_name'];
            $data[$info['guid']][] = $info['mobile'];
            $i++;
        }
        foreach ($user_info_other as $k => $other) {
            $data[$other['userinfo_guid']][] = str_replace("\r\n","",$other['value']);
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
    /**
     * 报名活动名单导出Excel  其他数据
     * CT：2015.02.06 09:55 by ylx
     */
    public function signup_export_other()
    {
        $keyword = urldecode(I('get.keyword'));//关键字搜索 默认空
//        $t = I('get.t');//票务类型 默认all
//        $s = I('get.s');//签到状态 默认all

        $derive_type = I('get.derive_type');
        $aid        = I('get.aid');
        if(empty($derive_type) || empty($aid)){
            $this->error('数据错误请重新操作');
        }

        if($derive_type == 'other'){

            $where['activity_guid'] = $aid;
            $where['is_del'] = 0;
            if (!empty($keyword)) { // 搜索姓名和电话
                $where['_string'] = '(real_name like "%'.$keyword.'%") or (mobile like "%'.$keyword.'%")';
            }
            $dal = D('ActivityUserTicket');

            $user_guids = $dal->field('userinfo_guid')
                ->where($where)
                ->select();
            foreach($user_guids as $k=>$v){
                $info_guids[] = $v['userinfo_guid'];
            }
        }else{
            $export_guids = explode(',',$derive_type);
            $info_guids = $export_guids;
        }
        if (empty($info_guids)) {
            $this->error('请选择要操作的用户.');
        }

        if (empty($aid)) {
            $this->error('非法操作, 请重试.');
        }
        // 获到表格大标题
        $activity_name = M('Activity')->where(array('guid' => $aid))->getField('name');
        $main_title    = '活动人员列表：' . $activity_name;

        // 获取表格头
        $form_build = M('ActivityForm')->where(array('activity_guid' => $aid, 'is_del' => '0'))
            ->order('sort desc')->getField('name', true);
        array_unshift($form_build, '序号');

        $user_info = M('ActivityUserinfo')->alias('i')
            ->where($where)
            ->where(array('guid' => array('in',$info_guids)))
            ->select();

        foreach($user_info as $k=>$v){
            $user_info_guids[] = $v['guid'];
        }

        $user_info_other = M('ActivityUserinfoOther')
            ->where(array('activity_guid' => $aid, 'userinfo_guid' => array('in', $user_info_guids), 'is_del' => '0'))
            ->order('id asc')
            ->select();
        $data            = array();
        $i               = 1;
        foreach ($user_info as $k => $info) {
            $data[$info['guid']][] = $i;
            $data[$info['guid']][] = $info['real_name'];
            $data[$info['guid']][] = $info['mobile'];
            $i++;
        }
        foreach ($user_info_other as $k => $other) {
            $data[$other['userinfo_guid']][] = str_replace("\r\n","",$other['value']);
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
//        var_dump($form_build);die;
//        return D('Excel')->export($main_title, $form_build, $data, date('YmdHis'), array(array('总人数: ', count($data))));
    }

    /**
     * 后台手动添加报名人员
     * CT： 2015.09.18 09:50 by rth
     */
    public function ajax_signup_add_user()
    {
        // 提交报名
        if (IS_POST) {
            $aid = I('get.aid');
            if (empty($aid) || strlen($aid) != 32) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '增加失败, 请稍后重试.'));
            }

            $params = I('post.');
            if (empty($params)) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '增加失败, 请稍后重试.'));
            }

            $time      = time();
            $user_guid = 'add_by_org_' . create_guid();
            // 保存userinfo
            $info           = $params['info'];
            $data_info      = array(
                'guid'          => create_guid(),
                'activity_guid' => $aid,
                'user_guid'     => $user_guid,
                'type'          => '4',
                'created_at'    => $time,
                'updated_at'    => $time
            );
            $data_info      = array_merge($data_info, $info);
            $model_userinfo = D('ActivityUserinfo');
            list($check, $r) = $model_userinfo->insert($data_info);
            if (!$check) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => $r));
            }
            if (!$r) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '增加失败, 请稍后重试.'));
            }

            // 保存其它信息
            $other      = $params['other'];
            $data_other = array();
            foreach ($other as $o) {
                $data_other[] = array(
                    'guid'          => create_guid(),
                    'userinfo_guid' => $data_info['guid'],
                    'activity_guid' => $aid,
                    'build_guid'    => $o['build_guid'],
                    'ym_type'       => $o['ym_type'],
                    'key'           => $o['key'],
                    'value'         => is_array($o['value']) ? implode('_____', $o['value']) : (isset($o['value']) ? $o['value'] : ''),
                    'created_at'    => $time,
                    'updated_at'    => $time
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
                'updated_at'    => $time
            );
            // 若为签到页添加,则直接自动签到成功
            $is_signin = I('get.signin');
            if ($is_signin == 'true') {
                $data_ticket['status']             = 4;
                $data_ticket['signin_status']      = 3;
                $data_ticket['ticket_code']        = generate_ticket_code(substr($aid, 0, 4));
                $data_ticket['ticket_verify_time'] = 1;
            }
            M('ActivityUserTicket')->add($data_ticket);

            $this->ajaxReturn(array('status' => 'ok', 'msg' => '增加成功.', 'data' => array('mobile' => $info['mobile'])));
        }
    }

    /**
     * 报名用户列表相关操作
     * CT: 2015-03-06 17:50 BY YLX
     */
    public function ajax_signup_userlist_batch_op()
    {
        if (IS_POST) {
            $info_guids = I('post.ck');
            $aid        = I('get.aid');
            $act        = I('post.batch_op');

            if (empty($info_guids)) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '请选择要操作的用户.'));
            }

            if (empty($aid) || empty($act)) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '非法操作, 请重试.'));
            }

            switch ($act) {
                case 'batch_delete':
                    $user_guids = M('ActivityUserinfo')->where(array('guid' => array('in', $info_guids)))->getField('user_guid', true);
                    $info       = D('ActivityUserinfo')->where(array('guid' => array('in', $info_guids)))->delete();
                    if ($info) {
                        M('ActivityUserinfoOther')->where(array('signup_userinfo_guid' => array('in', $info_guids)))->delete();
                        M('ActivityUserTicket')->where(array('activity_guid' => $aid, 'user_guid' => array('in', $user_guids)))->delete(); // 删除用户票务信息
                        $this->ajaxReturn(array('status' => 'ok', 'msg' => '删除成功.'));
                    }
                    break;
                default:
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '非法操作, 请重试.'));
                    break;
            }
        }

    }

    /**
     * 发送电子票 短信/邮件
     * CT： 2015-03-11 15:50 by ylx
     */
    public function ajax_send_ticket_a()
    {
        // 提交报名
        if (IS_AJAX) {
            $params     = I('post.data');
            $aguid      = $params['aid'];//z
            $info_guids = $params['data_user_guids'];//
            $send_type  = 'ticket';//z
            $send_way   = $params['send_way'];//发送类型   sms  email
            $activity_name = $params['aname'];//z
            if(empty($aguid) || empty($send_way) || empty($activity_name)){
                $this->ajaxReturn(array('status' => 'ko','msg' => '参数错误，请稍后再试。'));
            }
            $target = $params['target'];// 发送目标类型， null为选择发送， all全部发送， other未发送人员z
            if (!empty($target)) { // 发送全
                if (is_null($send_way)) {
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '发送失败, 请刷新后重试.'));
                } else{
                    if ($target == 'all') { // 给全部人员发送电子票
                        $send_type  = 'ticket';
                        // 获取所有已购买电子票的人员GUID

                        //需改状态位正在发送
                        $res = M('ActivityUserTicket')->where(array('activity_guid' => $aguid, 'is_del' => 0, 'status' => array('lt', 4)))->save(array('status' => 5));
                    } else if ($target == 'other') { // 给未发送的人员发送电子票
                        $send_type  = 'ticket';
                        //需改状态位正在发送
                        $res = M('ActivityUserTicket')->where(array('activity_guid' => $aguid, 'is_del' => 0, 'status' => array('lt', 2)))->save(array('status' => 5));
                    } else {
                        $this->ajaxReturn(array('status' => 'ko', 'msg' => '发送失败, 请刷新后重试.'));
                    }
                }
            } else { // 选择发送
                $target = 'part';
                $where = array('userinfo_guid' => array('in', $info_guids));
                //需改状态位正在发送
                $res = M('ActivityUserTicket')->where($where)->save(array('status' => 5));
                $userinfo = M('ActivityUserTicket')->field('guid')->where($where)->select(); 
            }
            if($target == 'all' || $target == 'other'){
                $userinfo = array('type' => $target);
            }

            $auth = $this->get_auth_session();

             $send_type = 'ticket';
            // 判断发送类别
            switch ($send_type) {
                case 'ticket':  // 发送电子票
                    $this->views = $this->view;
                    $send        = array(
                        'aguid'         => $aguid,
                        'send_way'      => $send_way,
                        'activity_name' => $activity_name,
                        'auth'          => $auth,
                        'target'        => $target,
                        'obj'           => serialize($this)
                    );

                    /*******************处理账户余额 start**************************/

                    $total = count($userinfo);
                    $logic = D('Pay', 'Logic');
                    $ext = array('creater_guid' => $auth['guid'], 'target_guid' => $aguid);
                    if (in_array('sms', $send_way)) {
                        //短信
                        $res = $logic->afterSendTicket($auth['guid'], $total * 10, C('sms_good_guid'), $total, $ext, 'sms');
                    }
                    if (in_array('email', $send_way)) {
                        $logic->afterSendTicket($auth['guid'], $total * 10, C('email_good_guid'), $total, $ext, 'email');
                    }
                    if ($logic->errors) {
                        $this->ajaxReturn(array('status' => 'ko', 'msg' => '发送失败' . implode(',', $logic->errors)));
                    }
                    send_list_ticket($userinfo, $send);
                    /*******************处理账户余额 end**************************/
                    $is_send = true;
                    break;
                default:
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '提交错误，请刷新页面后重试。3'));
                    break;
            }

            if ($is_send == true) {
                $this->ajaxReturn(array('status' => 'ok', 'msg' => '发送完毕，请刷新页面查看发送状态。'));
            } else {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '非法访问。'));
            }

        } else {
            $this->ajaxReturn(array('status' => 'ko', 'msg' => '对不起，您访问的页面不存在。'));
        }
    }


    /**
     * 发送电子票 短信/邮件
     * CT： 2015-03-11 15:50 by ylx
     */
    public function ajax_send_ticket()
    {
        // 提交报名
        if (IS_AJAX) {

            $params     = I('post.');
            $aguid      = $params['aid'];//活动guid
            $info_guids = $params['ck'];//选中要发送的用户guid
//            $send_type  = $params['send_type'];//发送类型
//            $send_content = $params['send_content'];
//            $send_sign    = $params['send_sign'];
            $activity_name = $params['aname'];
//            $time = time();

            if($params['data_type'] == 'all'){
                $target = 'all'; // 发送目标类型， null为选择发送， all全部发送， other未发送人员
            }else if($params['data_type'] == 'other'){
                $target = 'other';
            }
            if($params['send_type'] == 'sms'){
                $type = 'sms';
            }else if($params['send_type'] == 'email'){
                $type = 'email';
            }
            // 判断是否为全部发送
            if (!empty($target)) { // 发送全部
                if (is_null($type)) {
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '发送失败, 请刷新后重试.1'));
                } else {
                    if ($target == 'all') { // 给全部人员发送电子票
                        $send_way[] = $type;
                        $send_type  = 'ticket';
                        // 获取所有已购买电子票的人员GUID
                        $user_guids = M('ActivityUserTicket')->where(array('activity_guid' => $aguid, 'is_del' => 0, 'status' => array('lt', 2)))->getField('user_guid', true);
                        if (empty($user_guids)) {
                            $this->ajaxReturn(array('status' => 'ko', 'msg' => '暂无未发送人员，所有人员均已发送。'));
                        }
                        $where = array('activity_guid' => $aguid, 'is_del' => 0, 'user_guid' => array('IN', $user_guids));
                    } else if ($target == 'other') { // 给未发送的人员发送电子票
                        $send_way[] = $type;
                        $send_type  = 'ticket';
                        // 获取未发送电子票的人员GUID
                        $user_guids = M('ActivityUserTicket')->where(array('activity_guid' => $aguid, 'is_del' => 0, 'status' => array('lt', 2)))
                            ->getField('user_guid', true);
                        if (empty($user_guids)) {
                            $this->ajaxReturn(array('status' => 'ko', 'msg' => '暂无未发送人员，所有人员均已发送。'));
                        }
                        $where = array('activity_guid' => $aguid, 'is_del' => 0, 'user_guid' => array('IN', $user_guids));
                    } else {
                        $this->ajaxReturn(array('status' => 'ko', 'msg' => '发送失败, 请刷新后重试.2'));
                    }
                }
            } else { // 选择发送
                $where = array('guid' => array('in', $info_guids));
            }

            // 获取要发送人员的详情
            $userinfo = M('ActivityUserinfo')->field('guid, mobile, real_name, user_guid')
                ->where($where)
                ->order('created_at DESC')
                ->select();
            if (empty($userinfo)) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '发送失败, 请刷新后重试。4'));
            }

            $auth = $this->get_auth_session();

            // 判断发送类别
            switch ($send_type) {
                case 'ticket':  // 发送电子票
                    $this->views = $this->view;
                    $send        = array(
                        'aguid'         => $aguid,
                        'send_way'      => $send_way,
                        'activity_name' => $activity_name,
                        'auth'          => $auth,
                        'obj'           => serialize($this)
                    );

                    /*******************处理账户余额 start**************************/

                    $total = count($userinfo);
                    $logic = D('Pay', 'Logic');
                    if (in_array('sms', $send_way)) {
                        //短信
                        $res = $logic->afterSendTicket($auth['guid'], $total * 10, C('sms_good_guid'), $total);
                    }
                    if (in_array('email', $send_way)) {
                        $logic->afterSendTicket($auth['guid'], $total * 10, C('email_good_guid'), $total);
                    }
                    if ($logic->errors) {
                        $this->ajaxReturn(array('status' => 'ko', 'msg' => '发送失败,' . implode(',', $logic->errors)));
                    }
                    if ($logic->balance == 0) {
                        $this->ajaxReturn(array('status' => 'ko', 'msg' => '发送失败，余额不足，请充值'));
                    }
                    //vendor('YmPush.TicketInfo');
                    //\TicketInfo::setList('meetelf', 'ticket', $userinfo, $send, 1);
                    send_list_ticket($userinfo, $send);

                    /*******************处理账户余额 end**************************/
                    $is_send = true;
                    break;
                default:
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '提交错误，请刷新页面后重试。3'));
                    break;
            }

            if ($is_send == true) {
                $this->ajaxReturn(array('status' => 'ok', 'msg' => '发送完毕，请刷新页面查看发送状态。'));
            } else {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '非法访问。'));
            }

        } else {
            $this->ajaxReturn(array('status' => 'ko', 'msg' => '对不起，您访问的页面不存在。'));
        }
    }

    /**
     * 报表用户列表筛选
     */
    public function ajax_signup_userinfo()
    {
        $filters= I('post.filters');
        $aid = $filters['aid'];
        if (empty($aid)) {
            $this->error('活动不存在1.');
        }
        $activity_info = D('Activity')->where(array('guid' => $aid))->find();
        if (empty($activity_info)) {
            $this->error('活动不存在2.');
        }
        $this->assign('activity_info', $activity_info);

        $this->_get_signup_userlist($aid,'','1');
        $data = $this->fetch('_user_list_tbody');
        echo $data;
    }

    /**
     * 获取报名者信息
     * CT : 2015-10-14 by RTH
     */
    public function ajax_signin_user_info(){
        $aid = I('post.aid');//活动guid
//        $aid = '62A7622216833B4BADD680B617AB1897';//活动guid
        $uid = I('post.uid');//报名用户guid
//        $uid = '1770c69109a0e101c2858dcd33eaa300';//报名用户guid
        $model_form = M('ActivityForm');
        $model_ticket = M('ActivityUserTicket');
        $model_act_userinfo = M('ActivityUserinfo');
        $model_act_userinfo_other = M('ActivityUserinfoOther');
        //获取活动表单
        $act_form_options = $model_form->field('guid,is_required')->where(array('activity_guid' => $aid))->select();
        //获取报名用户基本信息
        $user_info = $model_act_userinfo
            ->where(array('activity_guid' => $aid,'guid' => $uid,'is_del' => '0'))
            ->find();
        //获取报名用户其他信息
        $user_other = $model_act_userinfo_other
            ->where(array('activity_guid' => $aid,'userinfo_guid' => $uid,'is_del' => '0'))
            ->select();

        //判断该报单项是否为必填
        foreach($user_other as $k => $v){
            foreach($act_form_options as $i => $j){
                if($v['build_guid'] == $j['guid']){
                    $user_other[$k]['is_null'] = $j['is_required'];
                }
            }
        }

        //获取报名用户票务信息
        $user_ticket = $model_ticket
            ->field('ticket_name,status,signin_status')
            ->where(array('activity_guid' => $aid,'userinfo_guid' => $uid,'is_del' => '0'))
            ->find();

        $user_info['other'] = $user_other;
        $this->assign('user_ticket',$user_ticket);
        $this->assign('user_other',$user_other);
        $this->assign('user_info',$user_info);
        $data = $this->fetch('_modal_view_signup_user');

        $this->ajaxReturn($data);
    }

    /*
     * 删除报名用户信息
     * CT： 2015-10-14 by RTH
     */
    public function ajax_del_user_info(){
        $uid = I('post.uid');//参会人员guid
        if(empty($uid)){
            $data['status'] = 'ko';
            $data['msg'] = '参数错误，请重新提交';
            $this->ajaxReturn($data);
        }
        $model_order = M('Order');
        $order_info = $model_order->where(array('buyer_guid' => $uid,'goods_price' => array('GT', 0)))->find();
        if(!empty($order_info)){
            $data['status'] = 'ko';
            $data['msg'] = '付费人员不能删除';
            $this->ajaxReturn($data);
        }

        $model_ticket = M('ActivityUserTicket');
        $model_act_userinfo = M('ActivityUserinfo');
        $model_act_userinfo_other = M('ActivityUserinfoOther');
        $ticket = $model_ticket->where(array('userinfo_guid' => $uid))->find();
        M('MsgContent')->where(array('ticket_guid' => $ticket['guid']))->save(array('status' => 0));
        $res[0] = $model_order->where(array('buyer_guid' => $uid))->data(array('status' => '9', 'updated_at' => time()))->save();
        $res[1] = $model_ticket->where(array('userinfo_guid' => $uid))->data(array('is_del' => '1', 'updated_at' => time()))->save();
        $res[2] = $model_act_userinfo->where(array('guid' => $uid))->data(array('is_del' => '1', 'updated_at' => time()))->save();
        $res[3] = $model_act_userinfo_other->where(array('userinfo_guid' => $uid))->data(array('is_del' => '1', 'updated_at' => time()))->save();

        if(!empty($res[1]) && !empty($res[2])){
            $data['status'] = 'ok';
            $data['msg'] = '参会人员删除成功';
            $this->ajaxReturn($data);
        }else{
            $data['status'] = 'ko';
            $data['msg'] = '参会人员删除失败';
            $this->ajaxReturn($data);
        }
    }

    public function muti_import(){
        $time_dir = date('Y_m_d');
        $guid     = I('get.guid');
        $config = array(
           'maxSize'  => 5*1024*1024,
           'exts'     => array('xls', 'xlsx'),
           'rootPath' => UPLOAD_PATH,
           'savePath' => '/etf/' . $time_dir . '/' . $guid . '/signup_users/',
           'subName'  => '',
           'saveName' => $guid,
           'replace' => true,
        );
        $upload = new Upload($config);//实例化上传类
        // 上传文件
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            var_dump($upload->getError());
        } else {// 上传成功
            $info = $info['file-name'];
            $data = array(
                'file' => $config['rootPath'] . $info['savepath'] . $info['savename'], 
                'guid' => $guid,
            );
        }
        $excel = D('Excel');
        $words = $excel->import_arr($data['file']);
        $activity_guid = $guid;
        if (!$activity_guid){
            return false;
        }
        $res = true;
        $errors = '';
        $mobile = array();
        foreach($words as $key => $value){
            $user = M('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'mobile' => $value[1],'is_del' => '0'))->find();
            if($user){
                continue;
            }
            $ticket_guid = array('01E36A86DF875F06FD5DC9C3EFD3B3CF', '904D1317EF169A82BF3C0E7E237FFF56');
            $ticket_guid = $ticket_guid[$value[5]];
            $user_ticket = M('ActivityUserTicket')->where(array('guid' => $ticket_guid))->find();
            // 当报名时, 判断是否有余票
            $check_total  = M('ActivityAttrTicket')->where(array('guid' => $ticket_guid))->getField('num');
            $check_signup = M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'ticket_guid' => $ticket_guid))->count();
            if ($check_signup >= $check_total) {
                $res = false;
                $errors = $user_ticket['name'] . '数量不足，请修改票务数量。';
                continue;
            }
            $params = array();
            if(!$value[1] || !preg_match('/^1[3584]{1}[0-9]{9}$/', $value[1])){
                $mobile[$key] = $value;
                $res = false;
                continue;
            }
            $user_from = 2;
            $params['ticket'] = $ticket_guid;
            $params['info']['mobile'] = $value[1];
            $params['info']['email'] = $value[2];
            $params['info']['real_name'] = $value[0];
            $params['other']['83A4C26EB414B8F68A29B655950B46EA'] = array(
                'ym_type' => 'email',
                'build_guid' => '83A4C26EB414B8F68A29B655950B46EA',
                'guid'    =>'',
                'value' => $value[2] ? trim($value[2]) : '',
                'key'   => '邮箱',
            );
            $params['other']['E19EB75C2CB61ED01B7B11BF36428B42'] = array(
                'ym_type' => 'company',
                'build_guid' => 'E19EB75C2CB61ED01B7B11BF36428B42',
                'guid'    => '',
                'value' => $value[3] ? trim($value[3]) : '',
                'key'   => '公司',
            );
            $params['other']['ACF9B3EA7B49714403FDD613B86E44FB'] = array(
                'ym_type' => 'position',
                'build_guid' => 'ACF9B3EA7B49714403FDD613B86E44FB',
                'guid'    => '',
                'value' => $value[4] ? trim($value[4]) : '',
                'key'   => '职位',
            );
            $ext['payment_type']  = 3;
            $r = D('Signup', 'Logic')->signup($activity_guid, $params, $user_from, $ext);
        }
        header("Content-type: text/html; charset=utf-8");
        $script = "<script type='text/javascript'>";
        $script .= "document.domain='meetelf1.com';";
        if(!$res){
            if($erros){
                $script .= "alert('{$erros}');"; 
            }
            if($mobile){
                $script .= "alert('";    
                foreach($mobile as $key => $value){
                    $script .= "用户:{$value[0]}的手机号{$value[1]}错误，请修改后重新上传.";
                } 
                $script .= "');";
            } 
        }else{
           $script .= "alert('上传成功');"; 
        }
        $script .= "window.parent.$('#upload-file').modal('hide');";
        $script .= "window.parent.location.reload();";
        $script .= '</script>';
        echo $script;
        die;
    }

}
