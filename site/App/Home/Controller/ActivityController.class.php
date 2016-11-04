<?php
namespace Home\Controller;

use Org\Api\YmSMS;
use Think\Image;

class ActivityController extends BaseHomeController
{

    public function index()
    {
        S('activity::tab', 'alist'); // 激活 活动列表 标签

        $num_per_page = C('NUM_PER_PAGE', null, 15); // 每页显示数量, 从配置文件中获取
        $session_auth = $this->kookeg_auth_data();
        //活动列表
        $model_activity = D('Activity');
        $condition      = 'a.user_guid="' . $session_auth['guid'] . '" and a.is_del=0';//array('a.guid' => $session_auth['guid'], 'a.is_del' => 0);

        // 判断是否是主题下的活动列表
        if (!empty($sid)) {
            $condition .= ' and  a.subject_guid="' . $sid . '"';
        }

        // 搜索活动名称
        $keyword = urldecode(I('get.k'));
        if (!empty($keyword)) {
            $condition .= " and a.name like '%$keyword%'";
        }

        // 过滤活动状态
        $filter_status = I('get.s', null);
        if (isset($filter_status) && $filter_status != 'all') {
            $condition .= " and a.status=$filter_status";
        }

        $list = $model_activity->alias('a')
            ->join('ym_activity_subject s on a.subject_guid=s.guid', 'left')
            ->field('a.*, s.name as subject_name')
            ->where($condition)
            ->order('a.updated_at DESC, a.start_time DESC')
            ->page(I('get.p', '1'), $num_per_page)
            ->select();

        // 使用page类,实现分类
        $params      = I('get.');
        $params['k'] = urldecode(I('get.k'));
        $count       = $model_activity->alias('a')->where($condition)->count();// 查询满足要求的总记录数
        $Page        = new \Think\Page($count, $num_per_page, $params);// 实例化分页类 传入总记录数和每页显示的记录数
        $show        = $Page->show();// 分页显示输出

        $time = time();
        foreach ($list as $k => $v) {
            // 若为报名活动，则获取报名地址
            $list[$k]['full_address'] = D('Activity')->getSignupFullAddress($v['guid']);

            // 判断活动状态
            if ($v['status'] == 0) { //未发布
                $list[$k]['state'] = '0';
            } else if ($v['status'] == 3) { // 已关闭
                $list[$k]['state'] = '4';
            } else if ($v['status'] == 2) { // 已结束
                $list[$k]['state'] = '3';
            } else if (empty($v['end_time'])) { // 若时间未设置, 则为进行中, 只见于文章活动
                $list[$k]['state'] = '2';
            } else if ($v['start_time'] > $time) { //未开始
                $list[$k]['state'] = '1';
            } else if ($v['start_time'] < $time && $v['end_time'] > $time) { //进行中
                $list[$k]['state'] = '2';
            } else if ($v['end_time'] < $time) { //已结束
                if ($v['status'] != 2) {
                    // 更新活动状态
                    $model_activity->where(array('guid' => $v['guid']))->save(array('status' => '2', 'updated_at' => $time));
                    $list[$k]['state'] = '3';
                }
            }
        }

        // 统计
        $num_condition = array('user_guid' => $session_auth['guid'], 'is_del' => 0);
        if (!empty($sid)) {
            $num_condition['subject_guid'] = $sid;
        }
        $num_condition['status'] = array('gt', 0);
        $this->assign('total_num', $model_activity->getConditionNum($num_condition));
        $num_condition['status'] = 0;
        $this->assign('publish_num', $model_activity->getConditionNum($num_condition));

        //渲染到页面
        $this->assign('page', $show);// 赋值分页输出
        $this->assign('list', $list);
        $this->assign('meta_title', '活动列表');
        $this->display();
    }

    /**
     *  文章内容页
     *
     *  CT 2014-11-03 10:20 by  RTH
     *  UT 2014-11-21 10:20 by  ylx
     */
    public function view()
    {
        //获取界面上传过来的guid
        $activity_guid = I('get.guid');
        if (empty($activity_guid)) {
            $this->error('活动不存在.');
        }

        $activity_info = D('Activity')->find_one(array('guid' => $activity_guid, 'is_del' => '0'));
        if (empty($activity_info)) {
            $this->error('活动不存在.');
        }

        $subject_info = D('ActivitySubject')->find_one(array('guid' => $activity_info['subject_guid']));
        $this->assign('subject_info', $subject_info);

        // 活动状态
        $time = time();
        if ($activity_info['status'] == 0) { //未发布
            $status = '未发布';
        } else if ($activity_info['status'] == 3) { // 已关闭
            $status = '已关闭';
        } else if ($activity_info['status'] == 2) { // 已结束
            $status = '已结束';
        } else if (empty($activity_info['end_time'])) { //// 若时间未设置, 则为进行中, 只见于文章活动
            $status = '进行中';
        } else if ($activity_info['start_time'] > $time) { //未开始
            $status = '未开始';
        } else if ($activity_info['start_time'] < $time && $activity_info['end_time'] > $time) { //进行中
            $status = '进行中';
        } else if ($activity_info['end_time'] < $time) { //已结束
            if ($activity_info['status'] != 2) {
                // 更新活动状态
                D('Activity')->set_field(array('guid' => $activity_guid), array('status' => '2', 'updated_at' => $time));
                $status = '已结束';
            }
        }
        $this->assign('status', $status);
        $this->assign('meta_title', '活动详情');

        $activity_guid = $activity_info['guid'];
        $form          = M('ActivityForm')
            ->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))
            ->select();
        //报名人数
        $user_count = M('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))
            ->count();

        // 承办机构
        $this->assign('undertaker', M('ActivityAttrUndertaker')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->order('id asc')->select());
        // 活动流程
        $this->assign('flow', M('ActivityAttrFlow')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->order('id asc')->select());

        // 检查票务是否有在售的
        $this->assign('is_ticket_forsale', M('ActivityAttrTicket')->where(array('activity_guid' => $activity_guid, 'is_del' => 0, 'is_for_sale' => 1))->find());

        $this->assign('user_count', $user_count);
        $this->assign('activity_info', $activity_info);
        $this->assign('form', $form);
        $this->display();
    }

    /**
     * 报表用户列表
     */
    public function signup_userinfo()
    {
        $aid = I('get.aid');
        if (empty($aid)) {
            $this->error('活动不存在.');
        }
        $activity_info = D('Activity')->where(array('guid' => $aid))->find();
        if (empty($activity_info)) {
            $this->error('活动不存在.');
        }
        $this->assign('activity_info', $activity_info);

        // 获取新用户表单
        $this->_get_signup_form($aid);

        // 是否显示发送邮箱
        $this->assign('is_send_mail', M('ActivityForm')->where(array('activity_guid' => $aid, 'ym_type' => 'email', 'is_required' => '1'))->find());

        // 获取社团余额
        $auth = $this->kookeg_auth_data();

        $balance = M('UserAccount')->field('balance')->where(array('account_guid' => $auth['guid'], 'status' => 1))->find();
        $this->assign('message_nums', yuan_to_fen($balance['balance'], false));

        //$org_info = M('Org')->field('num_sms, num_email')->where(array('guid' => $auth['guid']))->find();
        //$this->assign('org_info', $org_info);

        // 用户列表
        $this->_get_signup_userlist($aid);

        $this->assign('subject_info', D('ActivitySubject')->find_one(array('guid' => $activity_info['subject_guid'])));
        $this->assign('meta_title', '报名表');
        $this->display();
    }

    /**
     * 报表用户列表
     */
    public function elf_signup_userinfo()
    {
        $aid = I('get.aid');
        if (empty($aid)) {
            $this->error('活动不存在.');
        }
        $activity_info = D('Activity')->where(array('guid' => $aid))->find();
        if (empty($activity_info)) {
            $this->error('活动不存在.');
        }
        $this->assign('activity_info', $activity_info);

        // 获取新用户表单
        $this->_get_signup_form($aid);

        // 是否显示发送邮箱
        $this->assign('is_send_mail', M('ActivityForm')->where(array('activity_guid' => $aid, 'ym_type' => 'email', 'is_required' => '1'))->find());

        // 获取社团余额
        $auth = $this->kookeg_auth_data();

        $balance = M('UserAccount')->field('balance')->where(array('account_guid' => $auth['guid'], 'status' => 1))->find();
        $this->assign('message_nums', yuan_to_fen($balance['balance'], false));

        //$org_info = M('Org')->field('num_sms, num_email')->where(array('guid' => $auth['guid']))->find();
        //$this->assign('org_info', $org_info);

        // 用户列表
        $this->_get_signup_userlist($aid);

        $this->assign('subject_info', D('ActivitySubject')->find_one(array('guid' => $activity_info['subject_guid'])));
        $this->assign('meta_title', '报名表');
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
        $this->assign('tickets_filter', $tickets);
        foreach ($tickets as $k => $t) {
            $user_width_this_ticket = M('ActivityUserTicket')->field('guid')->where(array('ticket_guid' => $t['guid'], 'status' => '2', 'is_del' => '0'))->count();
            if ($user_width_this_ticket >= $t['num']) {
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
    public function ajax_signup_user_next_page()
    {
        if (IS_AJAX) {
            $aid    = I('get.aid');
            $action = I('get.action', '');
            if (empty($aid)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '参数错误，请稍后重试。'));
            }
            // 用户列表
            list($show, $list, $is_last_page) = $this->_get_signup_userlist($aid, $action);

            if (empty($list)) {
                $this->ajaxResponse(array('status' => 'nomore', 'msg' => '没有更多数据了。'));
            }

            $this->ajaxResponse(array('status' => 'ok', 'msg' => '加载成功。', 'data' => $list, 'is_last_page' => $is_last_page));
        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => '非法操作.'));
        }

    }

    /**
     * 获取报名用户列表
     * @param $aid 活动GUID
     * @param string $action 调用方action名称
     * @return array
     * CT: 2015-03-09 16:30 BY YLX
     */
    private function _get_signup_userlist($aid, $action = '')
    {
        $where = "i.activity_guid='$aid' and i.is_del=0";

        $filters = json_decode(htmlspecialchars_decode(I('post.filters')), true);

        // 搜索关键字 只支持姓名和电话
//        $keyword = urldecode(I('get.keyword'));
        $keyword = $filters['keyword'];
        if (!empty($keyword)) { // 搜索姓名和电话
            $where .= " and (i.real_name like '%$keyword%' or i.mobile like '%$keyword%')";
        }

        // 票务类型过滤
        $ticket_type = $filters['t'];
        if (!empty($ticket_type)) {
            if ($ticket_type != 'all') {
                $where .= " and t.ticket_guid='$ticket_type'";
            }
        }

        // 人员来源过滤
        $from = $filters['f'];
        if (isset($from)) {
            if ($from != 'all') {
                $where .= " and i.type='$from'";
            }
        }

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
            switch ($action) {
                case 'signin_chart':
                    $where .= " and t.status>=2";
                    break;
                default:
                    break;
            }
        }

        $list = M('ActivityUserinfo')->alias('i')
            ->join('ym_activity_user_ticket t on t.userinfo_guid = i.guid')
            ->field('*, t.guid as tid, i.guid as guid')
            ->where($where)
            ->order('i.created_at DESC')
            ->page(I('get.p', '1'), C('NUM_PER_PAGE', null, 10))
            ->select();

        // 使用page类,实现分类
        $count = M('ActivityUserinfo')->alias('i')->join('ym_activity_user_ticket t on t.activity_guid = i.activity_guid and t.user_guid = i.user_guid')
            ->where($where)->count();// 查询满足要求的总记录数
        $Page  = new \Think\Page($count, C('NUM_PER_PAGE', null, 10));// 实例化分页类 传入总记录数和每页显示的记录数
        $show  = $Page->show();// 分页显示输出

        $total_pages  = $Page->totalPages;
        $is_last_page = false;
        if (I('get.p', 1) == $total_pages || empty($total_pages)) {
            $is_last_page = true;
        }
        $this->assign('is_last_page', $is_last_page);

        foreach ($list as $k => $l) {
            // 来源
            $from             = C('ACTIVITY_SIGNUP_FROM');
            $list[$k]['from'] = $from[$l['type']];

            // 获取邮箱
            $email = M('ActivityUserinfoOther')
                ->where(array('activity_guid' => $aid, 'signup_userinfo_guid' => $l['guid'], 'ym_type' => 'email'))
                ->getField('value');

            if (is_valid_email($email)) {
                $list[$k]['email'] = $email;
            } else {
                $list[$k]['email'] = '';
            }

            // 其它信息
//            $other = M('ActivityUserinfoOther')->where(array('signup_userinfo_guid' => $l['guid'], 'is_del' => '0'))->order('id asc')->select();
//            foreach($other as $other_k => $o) {
//                $vals = explode('_____', $o['value']);
//                if(count($vals) <= 1) {
//                    $v_str = $o['value'];
//                } else {
//                    $v_str = implode(', ', $vals);
//                }
//                $other[$other_k]['value'] = $v_str;
//            }
//            $list[$k]['other'] = $other;

            // 票务相关
            $ticket_status               = C('ACTIVITY_TICKET_STATUS');
            $ticket_signin_status        = C('ACTIVITY_TICKET_SIGNIN_STATUS');
            $ticket_status_tag           = C('ACTIVITY_TICKET_STATUS_TAG');
            $ticket['ticket_status']     = isset($ticket_signin_status[$l['signin_status']]) ? $ticket_signin_status[$l['signin_status']] : $ticket_status[$l['status']];
            $ticket['ticket_status_tag'] = $ticket_status_tag[$l['status']];
            $ticket['ticket_name']       = $l['ticket_name'];
            $ticket['ticket_guid']       = $l['ticket_guid'];
            $list[$k]['ticket']          = $ticket;
        }

        $this->assign('user_list', $list);
        $this->assign('user_count', D('ActivityUserinfo')->alias('i')->join('ym_activity_user_ticket t on t.activity_guid = i.activity_guid and t.user_guid = i.user_guid')->get_count($where));
        $this->assign('page', $show);
        return array($show, $list, $is_last_page);
    }

    /**
     * 后台手动添加报名人员
     * CT： 2015.02.09 09:50 by ylx
     */
    public function ajax_signup_add_user()
    {
        // 提交报名
        if (IS_POST) {
            $aid = I('get.aid');
            if (empty($aid) || strlen($aid) != 32) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '增加失败, 请稍后重试.'));
            }

            $params = I('post.');
            if (empty($params)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '增加失败, 请稍后重试.'));
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
                $this->ajaxResponse(array('status' => 'ko', 'msg' => $r));
            }
            if (!$r) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '增加失败, 请稍后重试.'));
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

            $this->ajaxResponse(array('status' => 'ok', 'msg' => '增加成功.', 'data' => array('mobile' => $info['mobile'])));
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
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '请选择要操作的用户.'));
            }

            if (empty($aid) || empty($act)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '非法操作, 请重试.'));
            }

            switch ($act) {
                case 'batch_delete':
                    $user_guids = M('ActivityUserinfo')->where(array('guid' => array('in', $info_guids)))->getField('user_guid', true);
                    $info       = D('ActivityUserinfo')->where(array('guid' => array('in', $info_guids)))->delete();
                    if ($info) {
                        M('ActivityUserinfoOther')->where(array('signup_userinfo_guid' => array('in', $info_guids)))->delete();
                        M('ActivityUserTicket')->where(array('activity_guid' => $aid, 'user_guid' => array('in', $user_guids)))->delete(); // 删除用户票务信息
                        $this->ajaxResponse(array('status' => 'ok', 'msg' => '删除成功.'));
                    }
                    break;
                default:
                    $this->ajaxResponse(array('status' => 'ko', 'msg' => '非法操作, 请重试.'));
                    break;
            }
        }

    }

    /**
     * 报名活动名单导出Excel
     * CT：2015.02.06 09:55 by ylx
     */
    public function signup_export()
    {
        $info_guids = I('post.ck');
        $aid        = I('get.aid');
        $act        = I('post.batch_op', 'export');

        if (empty($info_guids)) {
//            $this->ajaxResponse(array('status' => 'ko', 'msg' => '请选择要操作的用户.'));
            $this->error('请选择要操作的用户.');
        }

        if (empty($aid) || empty($act)) {
//            $this->ajaxResponse(array('status' => 'ko', 'msg' => '非法操作, 请重试.'));
            $this->error('非法操作, 请重试.');
        }
        // 获到表格大标题
        $activity_name = D('Activity')->where(array('guid' => $aid))->getField('name');
        $main_title    = '活动人员列表：' . $activity_name;

        // 获取表格头
        $form_build = M('ActivityForm')->where(array('activity_guid' => $aid, 'is_del' => '0'))
            ->order('id asc')->getField('name', true);
        array_unshift($form_build, '序号');

		$where = array('i.activity_guid' => $aid, 'i.guid' => array('in', $info_guids), 'i.is_del' => '0');
        $user_info = M('ActivityUserinfo')->alias('i')
            ->join('ym_activity_user_ticket t on t.userinfo_guid = i.guid')
            ->field('*, t.guid as tid, i.guid as guid')
            ->where($where)
            ->select();
        $user_info_other = M('ActivityUserinfoOther')
            ->where(array('activity_guid' => $aid, 'userinfo_guid' => array('in', $info_guids), 'is_del' => '0'))
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
            $data[$other['userinfo_guid']][] = $other['value'];
        }
        return D('Excel')->export($main_title, $form_build, $data, date('YmdHis'), array(array('总人数: ', count($data))));
    }

    /**
     * 报名用户详情
     */
    public function signup_userdetail()
    {
        $userinfo_guid = I('get.uid');
        $info          = D('ActivityUserinfo')->where(array('guid' => $userinfo_guid, 'is_del' => '0'))->find();
        $other         = M('ActivityUserinfoOther')
            ->where(array('userinfo_guid' => $userinfo_guid, 'is_del' => '0'))
            ->getField('build_guid, guid, value');
        $this->assign('info', $info);
        $this->assign('other', $other);

        // 组装form
        $user_guid     = $info['user_guid'];
        $activity_guid = $info['activity_guid'];
        $activity_info = D('Activity')->find_one(array('guid' => $activity_guid));

        //判断是否走票务
        $total_ticket = M('ActivityAttrTicket')->field('SUM(num) as total')
            ->where(array('activity_guid' => $activity_guid, 'is_del' => '0', 'is_for_sale' => '1'))
            ->find();
        if (intval($total_ticket['total']) > 0) { // 如果票务已经被设置， 走票务
            $tickets = M('ActivityAttrTicket')
                ->where(array('activity_guid' => $activity_guid, 'is_del' => '0', 'is_for_sale' => '1'))
                ->getField('guid, num, name', true);
            $this->assign('tickets', $tickets);

            // 当前用户所选的票务
            $user_ticket = M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'user_guid' => $info['user_guid']))->find();
            $this->assign('user_ticket', $user_ticket);
        }

        // 提交报名
        if (IS_POST) {
            $params = I('post.');
            if (empty($params) || empty($params['info']['real_name']) || empty($params['info']['mobile'])) {
                $this->error('保存失败，请稍后重试。');
            }

            $time = time();
            // 保存userinfo
            $info           = $params['info'];
            $data_info      = array_merge($info, array('updated_at' => $time));
            $model_userinfo = D('ActivityUserinfo');
            list($check, $r) = $model_userinfo->update(array('guid' => $userinfo_guid), $data_info);
            if (!$check) {
                $this->error($r);
            }
            if (!$r) {
                $this->error('保存失败，请稍后重试.');
            }

            // 保存其它信息
            $other = $params['other'];
            if (!empty($other)) {
                foreach ($other as $o) {
                    $data_other = array(
                        'value'      => is_array($o['value']) ? implode('_____', $o['value']) : (isset($o['value']) ? $o['value'] : ''),
                        'updated_at' => $time
                    );
                    M('ActivityUserinfoOther')->where(array('guid' => $o['other_info_guid']))->save($data_other);
                }
            }

            // 保存票务信息
            $ticket_guid = I('post.ticket');
            $data_ticket = array(
                'mobile'      => $params['info']['mobile'],
                'ticket_guid' => !empty($ticket_guid) ? I('post.ticket') : 'nolimit',
                'ticket_name' => !empty($ticket_guid) ? $tickets[I('post.ticket')]['name'] : 'nolimit',
                'updated_at'  => $time
            );
            M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'user_guid' => $user_guid))->save($data_ticket);

            //时间轴-参加活动-报名
            $this->success('修改成功');
            exit();
        }

        $build_info  = D('ActivityForm')->where(array('activity_guid' => $activity_info['guid']))->order('id asc')->select();
        $option_info = D('ActivityFormOption')->where(array('activity_guid' => $activity_info['guid']))->field('guid,build_guid,value')->select();
        foreach ($option_info as $o) {
            $format_option_info[$o['build_guid']][] = $o;
        }

        $this->assign('activity_info', $activity_info);
        $this->assign('build_info', $build_info);
        $this->assign('option_info', $format_option_info);

        $this->assign('meta_title', '报名人员详情');
        $this->display();
    }

    /**
     * 删除报名人员
     */
    public function signup_userinfo_delete()
    {
        $userinfo_guid = I('get.uid');
        $aid           = I('get.aid');
        $userinfo      = M('ActivityUserinfo')->where(array('guid' => $userinfo_guid))->find();
        M('ActivityUserinfo')->where(array('guid' => $userinfo_guid))->delete(); // 删除用户信息
        M('ActivityUserinfoOther')->where(array('signup_userinfo_guid' => $userinfo_guid))->delete(); // 删除用户其它信息
        M('ActivityUserTicket')->where(array('activity_guid' => $aid, 'user_guid' => $userinfo['user_guid']))->delete(); // 删除用户票务信息
        $this->redirect('Activity/signup_userinfo', array('aid' => $aid));
    }

    /**
     * 电子票连接二维码
     * @return bool
     * CT： 2015-04-30 17:31 by ylx
     */
    public function qrcode_ticket()
    {
        $content = I('get.turl');
        $tguid   = I('get.tguid');
        $qr_path = '/org/qrcode/activity/ticket';
        $qr_name = $tguid . '.png';
        return qrcode($content, UPLOAD_PATH . $qr_path, $qr_name);
        die();
    }

    /**
     * 电子票重新发送
     * ct: 2015-07-01 17:32 by ylx
     */
    public function ajax_resend_ticket()
    {
        if (IS_AJAX) {
            $params        = I('post.');
            $aguid         = $params['activity_guid'];
            $userinfo_guid = $params['userinfo_guid'];
            $activity_name = $params['activity_name'];
            $auth          = $this->kookeg_auth_data();

            $userinfo = M('ActivityUserinfo')->field('guid, mobile, real_name, user_guid')
                ->where(array('guid' => $userinfo_guid))
                ->find();
            if (empty($userinfo)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '发送失败, 请刷新后重试。4'));
            }

            $this->views = $this->view;
            $send        = array(
                'aguid'         => $aguid,
                'activity_name' => $activity_name,
                'auth'          => $auth,
                'obj'           => serialize($this)
            );

            // 发送 邮件
            $send_way = array();
            if (isset($params['ck_resend_email'])) {
                $send_way[] = 'email';
            }
            // 发送 短信
            if (isset($params['ck_resend_mobile'])) {
                $send_way[] = 'sms';
            }
            if (empty($send_way)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '发送失败，请刷新页面后重试。'));
            }

            $send['send_way'] = $send_way;
            if (!empty($params['val_resend_mobile'])) {
                $userinfo['mobile'] = $params['val_resend_mobile'];
            }
            if (!empty($params['val_resend_email'])) {
                $userinfo['email'] = $params['val_resend_email'];
            }

            /*******************处理账户余额 start**************************/

            $logic = D('Pay', 'Logic');
            if (in_array('sms', $send_way)) {
                //短信
                $logic->afterSendTicket($auth['guid'], 10, C('sms_good_guid'), 1);
            }
            if (in_array('email', $send_way)) {
                $logic->afterSendTicket($auth['guid'], 10, C('email_good_guid'), 1);
            }
            /*******************处理账户余额 end**************************/

            if ($logic->errors) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '发送失败，请稍后重试'));
            }
            if ($logic->balance == 0) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '发送失败，余额不足，请充值'));
            }
            //vendor('YmPush.TicketInfo');
            //\TicketInfo::sendTicket(json_encode($userinfo), $send);
			send_ticket($userinfo, $send);
            $this->ajaxResponse(array('status' => 'ok', 'msg' => '发送完毕，请刷新页面查看发送状态。'));
        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => '非法请求。'));
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
            $aguid      = $params['activity_guid'];
            $info_guids = $params['ck'];
            $send_type  = $params['send_type'];
            $send_way   = $params['send_way'];
//            $send_content = $params['send_content'];
//            $send_sign    = $params['send_sign'];
            $activity_name = $params['activity_name'];
//            $time = time();

            $target = I('post.target', null); // 发送目标类型， null为选择发送， all全部发送， other未发送人员
            // 判断是否为全部发送
            if (!empty($target)) { // 发送全部
                $type = I('post.type', null);
                if (is_null($type)) {
                    $this->ajaxResponse(array('status' => 'ko', 'msg' => '发送失败, 请刷新后重试.1'));
                } else {
                    if ($target == 'all') { // 给全部人员发送电子票
                        $send_way[] = $type;
                        $send_type  = 'ticket';
                        // 获取所有已购买电子票的人员GUID
                        //$user_guids = M('ActivityUserTicket')->where(array('activity_guid' => $aguid, 'is_del' => 0, 'status' => array('lt', 2)))->getField('user_guid', true);
                        $user_guids = M('ActivityUserTicket')->where(array('activity_guid' => $aguid, 'is_del' => 0))->getField('user_guid', true);
                         //  $this->ajaxResponse(array('status' => 'ko', 'msg' => '暂无未发送人员，所有人员均已发送。'));
                        if (empty($user_guids)) {
                        }
                        $where = array('activity_guid' => $aguid, 'is_del' => 0, 'user_guid' => array('IN', $user_guids));
                    } else if ($target == 'other') { // 给未发送的人员发送电子票
                        $send_way[] = $type;
                        $send_type  = 'ticket';
                        // 获取未发送电子票的人员GUID
                        $user_guids = M('ActivityUserTicket')->where(array('activity_guid' => $aguid, 'is_del' => 0, 'status' => array('lt', 2)))
                            ->getField('user_guid', true);
                        if (empty($user_guids)) {
                            $this->ajaxResponse(array('status' => 'ko', 'msg' => '暂无未发送人员，所有人员均已发送。'));
                        }
                        $where = array('activity_guid' => $aguid, 'is_del' => 0, 'user_guid' => array('IN', $user_guids));
                    } else {
                        $this->ajaxResponse(array('status' => 'ko', 'msg' => '发送失败, 请刷新后重试.2'));
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
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '发送失败, 请刷新后重试。4'));
            }

            $auth = $this->kookeg_auth_data();

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
                        $this->ajaxResponse(array('status' => 'ko', 'msg' => '发送失败,' . implode(',', $logic->errors)));
                    }
                    if ($logic->balance == 0) {
                        $this->ajaxResponse(array('status' => 'ko', 'msg' => '发送失败，余额不足，请充值'));
                    }
                    //vendor('YmPush.TicketInfo');
                    //\TicketInfo::setList('meetelf', 'ticket', $userinfo, $send, 1);
					send_list_ticket($userinfo, $send);

                    /*******************处理账户余额 end**************************/
                    $is_send = true;
                    break;
                default:
                    $this->ajaxResponse(array('status' => 'ko', 'msg' => '提交错误，请刷新页面后重试。3'));
                    break;
            }

            if ($is_send == true) {
                $this->ajaxResponse(array('status' => 'ok', 'msg' => '发送完毕，请刷新页面查看发送状态。'));
            } else {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '非法访问。'));
            }

        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => '对不起，您访问的页面不存在。'));
        }
    }

    /**
     *  活动内容编辑页
     *
     * CT 2014-11-03 10:20 by RTH
     * UT 2014-11-21 10:20 by ylx
     */
    public function edit()
    {
        $activity_guid = I('get.guid');
        if (empty($activity_guid)) {
            $this->error('活动不存在.');
        }

        $activity_info = D('Activity')->find_one(array('guid' => $activity_guid));
        if (empty($activity_guid)) {
            $this->error('活动不存在.');
        }

        if ($activity_info['status'] != '0') {
            $this->error('活动正在进行中或已结束, 无法编辑.');
        }

        header('Cache-control: private, must-revalidate');
        S('activity::is_new', false); // 标记为编辑活动
        $subject_info = D('ActivitySubject')->find_one(array('guid' => $activity_info['subject_guid']));
        // 获取主题列表
//        $this->assign('subject_list', $this->_get_subject_list($activity_info['guid']));

        //获取session('auth')里面的登录信息
        $session_auth = $this->kookeg_auth_data();
        $build_info   = D('ActivityForm')->where(array('activity_guid' => $activity_guid))->order('id asc')->select();
        $option_info  = D('ActivityFormOption')->where(array('activity_guid' => $activity_guid))->field('guid,build_guid,value')->select();
        foreach ($option_info as $o) {
            $format_option_info[$o['build_guid']][] = $o;
        }
        // 承办机构
        $undertakers_info = M('ActivityAttrUndertaker')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->order('id asc')->select();
        // 活动流程
        $flow_info = M('ActivityAttrFlow')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->order('id asc')->select();
        // 票务
        $ticket_info = M('ActivityAttrTicket')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->order('id asc')->select();
        foreach ($ticket_info as $k => $t) {
            $user_width_this_ticket      = M('ActivityUserTicket')->field('guid')->where(array('ticket_guid' => $t['guid'], 'status' => '2', 'is_del' => '0'))->count();
            $ticket_info[$k]['num_used'] = $user_width_this_ticket;
        }
        if (IS_POST) {
            //承办机构
            $undertakers = I('post.op_undertaker');
            S('signup_edit::undertakers', $undertakers);
            //活动流程
            $flow = I('post.op_flow');
            if ($flow) {
                foreach ($flow as $k => $v) {
                    $flow[$k]['start_time'] = strtotime($v['start_time']);
                    $flow[$k]['end_time']   = strtotime($v['end_time']);
                }
            }
            S('signup_edit::flows', $flow);
            //票务
            $tickets = I('post.op_ticket');
            $old     = $tickets['old'];
            if ($tickets['new']) {
                $total = count($old);
                foreach ($tickets['new'] as $k => $t) {
                    $old[$total] = array(
                        'guid'        => create_guid(),
                        'name'        => $t['name'],
                        'num'         => $t['num'],
                        'verify_num'  => $t['verify_num'],
                        'is_for_sale' => $t['is_for_sale'],
						'price'       => validate_data($t, 'price', 0),
                        'num_used'    => 0,
                    );
                    $total++;
                }
            }
            S('signup_edit::old_tickets', $old);
            //表单
            $time = time();
            list($data_build, $data_options) = $this->_format_form_data($activity_guid);
            if ($data_options) {
                foreach ($data_options as $o) {
                    $tmp[$o['build_guid']][] = $o;
                }
                S('signup_edit::form_options', $tmp);
            }
            S('signup_edit::form_build', $data_build);

            //后台验证文章内容
            if (I('post.content') == "") {
                $this->error('内容不能为空');
            }
            //***************存储活动表数据******************
            list($_not_allowed_publish, $data_activity) = $this->_add_activity($activity_guid);

            // ************** 编辑 承办机构 **************
            M('ActivityAttrUndertaker')->where(array('activity_guid' => $activity_guid))->delete();
            if (!empty($undertakers)) {
                $data_undertakers = array();
                foreach ($undertakers as $k => $u) {
                    $data_undertakers[] = array(
                        'guid'          => create_guid(),
                        'activity_guid' => $activity_guid,
                        'type'          => $u['type'],
                        'name'          => $u['name'],
                        'created_at'    => $time,
                        'updated_at'    => $time
                    );
                }
                M('ActivityAttrUndertaker')->addAll($data_undertakers);
            }

            // ************** 编辑 活动流程 **************
            M('ActivityAttrFlow')->where(array('activity_guid' => $activity_guid))->delete();
            if (!empty($flow)) {
                $data_flow = array();
                foreach ($flow as $k => $f) {
                    $data_flow[] = array(
                        'guid'          => create_guid(),
                        'activity_guid' => $activity_guid,
                        'title'         => $f['title'],
                        'content'       => $f['content'],
                        'start_time'    => !empty($f['start_time']) ? $f['start_time'] : '',
                        'end_time'      => !empty($f['end_time']) ? $f['end_time'] : '',
                        'created_at'    => $time,
                        'updated_at'    => $time
                    );
                }
                $r = M('ActivityAttrFlow')->addAll($data_flow);
            }

            // ************** 编辑 票务 **************
            $tickets = I('post.op_ticket');
            if (!empty($tickets)) {
                $model_ticket = M('ActivityAttrTicket');
                $time         = time();
				$logic = D('Goods', 'Logic');
                if (!empty($tickets['old'])) {
                    foreach ($tickets['old'] as $t) {
                        $t['updated_at'] = $time;
                        $model_ticket->where(array('guid' => $t['guid']))->save($t);
						$logic->update_goods($t, array('ticket_guid' => $t['guid']));
                    }
                }
        		$user_guid = $this->kookeg_auth_data('guid');
                if (!empty($tickets['new'])) {
                    foreach ($tickets['new'] as $k => $t) {
						$tmp_guid = create_guid();
                        $data_ticket = array(
                            'guid'          => $tmp_guid,
                            'activity_guid' => $activity_guid,
                            'name'          => $t['name'],
                            'num'           => $t['num'],
                            'verify_num'    => $t['verify_num'],
                            'is_for_sale'   => $t['is_for_sale'],//isset($t['is_for_sale']) ? '1' : '0',
						    'price'         => validate_data($t, 'price', 0),
                            'created_at'    => $time,
                            'updated_at'    => $time
						);
						$goods[] = array(
							'name'        => $t['name'],
							'seller_guid' => $user_guid,
							'target_guid' => $activity_guid,	
							'price'       => validate_data($t, 'price', 0),
							'storage'     => intval($t['num']),
							'ticket_guid' => $tmp_guid,
						);
                        $model_ticket->add($data_ticket);
                    }
					$logic->add_arr($goods);
                }
            }

            //************** 删除旧表名表单 *************
            $activity_guid = $activity_info['guid'];
            D('ActivityForm')->phy_delete(array('activity_guid' => $activity_guid));
            D('ActivityFormOption')->phy_delete(array('activity_guid' => $activity_guid));

            if (!empty($data_build)) {
                foreach ($data_build as $db) {
                    M('ActivityForm')->add($db);
                }
            }
            if (!empty($data_options)) {
                M('ActivityFormOption')->addAll($data_options);
            }

            // 判断当日发布活动数量是否超出限制
            $this->_reset_edit_cache();
            if ($_not_allowed_publish == true) {
                $this->error('活动保存成功，但发布失败,活动发布数量超出限制', U('Activity/view', array('guid' => $activity_guid)));
            } else {
                $this->success('活动保存成功', U('Activity/view', array('guid' => $activity_guid)));
            }
            exit();
        }

        //地区
        $this->assign('area_1', D('Area')->find_all('deep=1', 'id, name'));
        $this->assign('area_2', D('Area')->find_all(array('parent_id' => $activity_info['areaid_1']), 'id, name'));
//        $this->assign('group_list', M('OrgGroup')->where('is_del = 0 AND guid = "' . $session_auth['guid'] . '"')->select());


        $undertakers_info = ($tmp = S('signup_edit::undertakers')) ? $tmp : $undertakers_info;
        $flow_info        = ($flows = S('signup_edit::flows')) ? $flows : $flow_info;
        $ticket_info      = ($tickets = S('signup_edit::old_tickets')) ? $tickets : $ticket_info;
        $build_info       = ($build = S('signup_edit::form_build')) ? $build : $build_info;
        $option_info      = ($format = S('signup_edit::form_options')) ? $format : $format_option_info;

        $this->_reset_edit_cache();
        $this->assign('subject_info', $subject_info);
        $this->assign('activity_info', $activity_info);
        $this->assign('meta_title', '编辑活动');
        $this->assign('session_auth', $this->kookeg_auth_data());
        $this->assign('build_info', $build_info);
        $this->assign('option_info', $option_info);
        $this->assign('undertaker', $undertakers_info);
        $this->assign('flow', $flow_info);
        $this->assign('ticket', $ticket_info);
        $this->display();
    }


    /**
     * 关闭活动
     * CT 2015.02.12 15:37 by ylx
     */
    public function close()
    {
        $aid = I('get.guid');
        if (empty($aid)) {
            $this->error('活动不存在.');
        }
        $activity_info = D('Activity')->find_one(array('guid' => $aid));
        if (empty($activity_info)) {
            $this->error('活动不存在.');
            exit();
        }
        if ($activity_info['status'] != '1') {
            $this->error('活动未发布或已结束, 无法关闭.');
            exit();
        }

        //关闭活动
        $res = D('Activity')->where(array('guid' => $aid))->save(array('status' => '3', 'updated_at' => time()));
        if (empty($res)) {
            $this->error('活动关闭失败, 请稍后重试');
            exit();
        }
        $this->success('操作成功！');
    }


    /**
     * 删除活动
     * CT 2014-11-03 10:20 by  RTH
     * UT 2014-11-21 10:20 by ylx
     */
    public function activity_del()
    {
        $activity_guid = I('get.guid');
        if (empty($activity_guid)) {
            $this->error('活动不存在.');
        }
        $activity_info = D('Activity')->find_one(array('guid' => $activity_guid));
        if (empty($activity_info)) {
            $this->error('活动不存在.');
            exit();
        }
        if ($activity_info['status'] != '0') {
            $this->error('活动已发布, 无法删除.');
            exit();
        }

        //删除活动
        $res = D('Activity')->phy_delete(array('guid' => $activity_guid));//->save($data);
        if (empty($res)) {
            $this->error('活动删除失败, 请稍后重试');
            exit();
        }

        if (I('get.return') == 'back') {
            $return = '';
        } else {
            $return = U('Activity/index');
        }
        $this->success('活动删除成功', $return);
    }

    /**
     *  检查活动是否超过等级配置
     * @param String type
     * @return bool true则数量超出，不能发布；false则可以继续发布
     *
     *  CT 2014-12-16 09:31 by QXL
     */
    public function check_activity_num($type = '')
    {
		return false;
        $auth       = $this->kookeg_auth_data();
        $vip_config = $this->get_vip_info();
        $today      = strtotime(today);
        $where      = array('created_at' => array('GT', $today), 'user_guid' => $auth['guid']);
        if ($type == 'published') {
            $where['status'] = '1';
        }
        $count = count(D('Activity')->where($where)->select());

        if ($type == 'published') {
            return $count >= $vip_config['NUM_ACTIVITY_PUBLISH_PER_DAY'];
        }

        return $count >= $vip_config['NUM_ACTIVITY_PER_DAY'];
    }

    /**
     * 新增或编辑活动
     * @param $type
     * @param null $activity_guid
     * @return array
     */
    private function _add_activity($activity_guid = null)
    {
        // 若活动不为文章活动则开始时间和结束时间为必填
        $start_time = I('post.startTime', null);
        $end_time   = I('post.endTime', null);
        if (empty($start_time) || empty($end_time)) {
            $this->error('活动开始时间和结束时间不能为空。');
        }

        // 获取创建者GUID
        $user_guid = $this->kookeg_auth_data('guid');
        // 判断报名的开始时间和结束时间
        $start = I('post.start');
        if (empty($start)) {
            if (I('post.status') == '1') {
                $start = $this->time;
            } else {
                $start = '';
            }
        } else {
            $start = strtotime($start);
        }
        $end = I('post.end');
        if (empty($end)) {
            $end = strtotime(I('post.endTime')) - 3600;
        } else {
            $end = strtotime($end);
        }
        // 参会为数设置
        $num_person = trim(I('post.num_person'));
        // 组装活动数据
        $sguid         = I('post.subject_guid');
        $data_activity = array(
            'user_guid'     => $user_guid,
            'subject_guid'  => empty($sguid) ? null : $sguid,
            'name'          => trim(I('post.name')),
            'content'       => I('post.content'),
            'status'        => I('post.status'),
            'is_verify'     => (I('post.status') == 1) ? 2 : 0,
            'poster'        => I('post.poster', null),
            'is_public'     => I('post.is_public', 0),
            'start_time'    => !empty($start_time) ? strtotime(I('post.startTime')) : null,
            'end_time'      => !empty($end_time) ? strtotime(I('post.endTime')) : null,
            'start'         => $start,
            'end'           => $end,
            'areaid_1'      => I('post.areaid_1'),
            'areaid_1_name' => get_area(I('post.areaid_1')),
            'areaid_2'      => I('post.areaid_2'),
            'areaid_2_name' => get_area(I('post.areaid_2')),
            'address'       => trim(I('post.address')),
            'lng'           => trim(I('post.lng')),
            'lat'           => trim(I('post.lat')),
            'keyword'       => trim(I('post.keyword')),
            'num_person'    => (empty($num_person) || $num_person == '0' || !is_numeric($num_person)) ? 0 : $num_person,
            'updated_at'    => $this->time
        );


        $is_new = S('activity::is_new');
        if ($is_new) {
            $data_activity['guid']       = create_guid();
            $data_activity['created_at'] = $this->time;
        }

        // 判断是否发布
        if ($data_activity['status'] == '1') {
            //判断发布数是否超过配置
            if ($this->check_activity_num('published')) {
                $data_activity['status'] = '0';
                $not_allow_publish       = true;
            } else {
                $data_activity['published_at'] = $this->time;
                $not_allow_publish             = false;
            }
        }
        $model_activity = D('Activity');

        // 保存活动
        if ($is_new) {
            list($check, $r) = D('Activity')->insert($data_activity);
        } else {
            $condition = array('guid' => $activity_guid);
            list($check, $r) = $model_activity->update($condition, $data_activity);
        }

        if (!$check) {
            $this->error($r);
            exit();
        }
        //保存错误跳转到活动添加页面
        if (!$r) {
            $this->error('活动保存失败, 请稍后重试.');
            exit();
        }
        return array($not_allow_publish, $data_activity);
    }

    /**
     * 获取主题列表
     * @param $guid 社团guid
     * ct: 2015-05-26 09:32 by ylx
     */
    private function _get_subject_list($guid = null)
    {
        if (empty($guid)) {
            $guid = $this->kookeg_auth_data()['guid'];
        }
        $condition = array('guid' => $guid, 'is_del' => 0);
        return D('ActivitySubject')->find_all($condition, 'guid, name', '', 'CONVERT(name USING gbk)');
    }

    /**
     * 添加活动
     * CT: *
     * ut: 2015-05-25 11:00 by ylx
     */
    public function add()
    {
        header('Cache-control: private, must-revalidate');
        S('activity::is_new', true); // 标记为创建新活动

        //判断创建数是否超过配置
        if ($this->check_activity_num()) {
            $this->error('今天已经不能新建更多活动了', U('Activity/index'));
        }

        $_not_allow_publish = false;  // 发布数量是否超出限制

        //获取session('auth')里面的登录信息
        $session_auth = $this->kookeg_auth_data();
        $this->assign('session_auth', $session_auth);
        $user_guid = $session_auth['guid'];
        // 获取主题列表
//        $this->assign('subject_list', $this->_get_subject_list($user_guid));

        // 相关保存操作 --- start
        // 获取一级地区
        $area_1 = D('Area')->find_all('deep=1', 'id, name');
        $this->assign('area_1', $area_1);

        if (IS_POST) {
            //表单缓存
            $base = array(
                'img'    => I('post.poster', ''),
                'area_1' => I('post.areaid_1'),
                'area_2' => I('post.areaid_2')
            );
            S('activity::activity_add::base', $base);
            if ($undertakers = I('post.op_undertaker')) {
                S('activity::activity_add::undertakers', $undertakers);
            }
            if ($flow = I('post.op_flow')) {
                S('activity::activity_add::flows', $flow);
            }
            $tickets = I('post.op_ticket');
            if ($tickets && isset($tickets['new']) && $tickets['new']) {
                S('activity::activity_add::tickets', $tickets['new']);
            }
            if ($items = I('post.items')) {
                S('activity::activity_add::items', $items);
            }

            $check_name = I('post.name');
            if ($tmp = censor_words($check_name)) {
                $this->error("活动添加失败, 包含敏感词[{$tmp}].");
            }
            //后台验证文章内容
            if (I('post.content') == "") {
                $this->error('内容不能为空');
            }

            //***************存储活动表数据******************
            list($_not_allow_publish, $data_activity) = $this->_add_activity($this->ACTIVITY_SIGNUP, $user_guid);

            // ************** 增加 承办机构 **************
            $undertakers = I('post.op_undertaker');
            if (!empty($undertakers)) {
                $data_undertakers = array();
                foreach ($undertakers as $k => $u) {
                    $data_undertakers[] = array(
                        'guid'          => create_guid(),
                        'activity_guid' => $data_activity['guid'],
                        'type'          => $u['type'],
                        'name'          => $u['name'],
                        'created_at'    => $this->time,
                        'updated_at'    => $this->time
                    );
                }
                M('ActivityAttrUndertaker')->addAll($data_undertakers);
            }

            // ************** 增加 活动流程 **************
            $flow = I('post.op_flow');
            if (!empty($flow)) {
                $data_flow = array();
                foreach ($flow as $k => $f) {
                    $data_flow[] = array(
                        'guid'          => create_guid(),
                        'activity_guid' => $data_activity['guid'],
                        'title'         => $f['title'],
                        'content'       => $f['content'],
                        'start_time'    => !empty($f['start_time']) ? strtotime($f['start_time']) : '',
                        'end_time'      => !empty($f['end_time']) ? strtotime($f['end_time']) : '',
                        'created_at'    => $this->time,
                        'updated_at'    => $this->time
                    );
                }
                M('ActivityAttrFlow')->addAll($data_flow);
            }

            // ************** 增加 票务 **************
            $tickets = I('post.op_ticket');
            if (!empty($tickets)) {
                $model_ticket = M('ActivityAttrTicket');
                $this->time   = time();
                if (!empty($tickets['new'])) {
                    $data_ticket = $goods = array();
                    foreach ($tickets['new'] as $k => $t) {
						$tmp_guid = create_guid();
                        $data_ticket[] = array(
                            'guid'          => $tmp_guid,
                            'activity_guid' => $data_activity['guid'],
                            'name'          => $t['name'],
                            'num'           => $t['num'],
                            'verify_num'    => $t['verify_num'],
                            'is_for_sale'   => $t['is_for_sale'],//isset($t['is_for_sale']) ? '1' : '0',
                            'created_at'    => $this->time,
							'price'         => validate_data($t, 'price', 0),
                            'updated_at'    => $this->time
                        );

						$goods[] = array(
							'name'        => $t['name'],
							'seller_guid' => $user_guid,
							'target_guid' => $data_activity['guid'],	
							'price'       => validate_data($t, 'price', 0),
							'storage'     => intval($t['num']),
							'ticket_guid' => $tmp_guid,
						);
                    }
                    $r = $model_ticket->addAll($data_ticket, array(), true);
					if($r){
						$logic = D('Goods', 'Logic');
						$logic->add_arr($goods);
					}
                }
            }

            //************** 创建表名表单 *************
            $data_build   = array();
            $data_build[] = array( // 姓名
                'guid'          => create_guid(),
                'activity_guid' => $data_activity['guid'],
                'name'          => I('post.real_name'),
                'note'          => I('post.real_name_note'),
                'ym_type'       => 'real_name',
                'html_type'     => 'text',
                'is_required'   => 1,
                'is_info'       => 1,
                'created_at'    => $this->time,
                'updated_at'    => $this->time
            );
            $data_build[] = array( //手机
                'guid'          => create_guid(),
                'activity_guid' => $data_activity['guid'],
                'name'          => I('post.mobile'),
                'note'          => I('post.mobile_note'),
                'ym_type'       => 'mobile',
                'html_type'     => 'text',
                'is_required'   => 1,
                'is_info'       => 1,
                'created_at'    => $this->time,
                'updated_at'    => $this->time
            );
            $items        = I('post.items');
            if (!empty($items)) { //其它
                $data_options = array();
                foreach ($items as $i) {
                    if (isset($i['name'])) {
                        $buid_guid    = create_guid();
                        $data_build[] = array(
                            'guid'          => $buid_guid,
                            'activity_guid' => $data_activity['guid'],
                            'name'          => $i['name'],
                            'note'          => $i['note'],
                            'ym_type'       => $i['ym_type'],
                            'html_type'     => $i['html_type'],
                            'is_info'       => 0,
                            'is_required'   => isset($i['is_required']) ? $i['is_required'] : 0,
                            'created_at'    => $this->time,
                            'updated_at'    => $this->time
                        );
                        if (!empty($i['options'])) {
                            foreach ($i['options'] as $o) {
                                $data_options[] = array(
                                    'guid'          => create_guid(),
                                    'activity_guid' => $data_activity['guid'],
                                    'build_guid'    => $buid_guid,
                                    'value'         => $o,
                                    'created_at'    => $this->time,
                                    'updated_at'    => $this->time
                                );
                            }
                        }
                    }
                }
            }
            if (!empty($data_build)) {
                foreach ($data_build as $db) {
                    M('ActivityForm')->add($db);
                }
            }
            if (!empty($data_options)) {
                M('ActivityFormOption')->addAll($data_options);
            }


            $add_again = I('post.add_again');
            //如果成功并且不继续添加跳转到主题列表页面
            if (empty($add_again)) {
                if ($_not_allow_publish) {
                    $this->error('保存成功，但发布失败,活动发布数量超出限制', U('Activity/view', array('guid' => $data_activity['guid'])));
                } else {
                    $this->_reset_add_cache();
                    $this->success('活动创建成功', U('Activity/view', array('guid' => $data_activity['guid'])));
                }
            } else {
                if ($_not_allow_publish) {
                    $this->error('保存成功，但发布失败, 活动发布数量超出限制，您可以继续创建活动。', U('Activity/add'));
                } else {
                    $this->_reset_add_cache();
                    $this->success('活动创建成功，继续创建', U('Activity/add'));
                }
            }
            exit();
        }

        $this->_set_form_cache();
        // 相关保存操作 --- end

        $this->assign('meta_title', '新增活动');
        $this->display('edit');
    }

    /**
     * 检查用户是否重复报名
     */
    public function ajax_check_signup_user()
    {
        $params = I('post.');
        $aid    = $params['aid'];
        $kv     = $params['info'];
        $value  = current($kv);
        $key    = key($kv);

        switch ($key) {
            case 'mobile':
                $result = M('ActivityUserinfo')->where(array($key => $value, 'activity_guid' => $aid))->find();
                echo empty($result) ? 'true' : 'false';
                exit();
                break;
            default:
                echo 'true';
                exit();
                break;
        }
    }

    /**
     * 票务列表
     * CT: 2015-03-05 10:25 BY YLX
     */
    public function ticket()
    {
        $activity_guid = I('get.guid');
        if (empty($activity_guid)) {
            $this->error('活动不存在。');
        }
        $activity_info = D('Activity')->find_one(array('guid' => $activity_guid, 'is_del' => '0'));
        if (empty($activity_info)) {
            $this->error('活动不存在.');
        }

        if (IS_POST) {
            $tickets      = I('post.op_ticket');
            $model_ticket = M('ActivityAttrTicket');
			$session_auth = $this->kookeg_auth_data();
            $time         = time();
			$logic = D('Goods', 'Logic');
            if (!empty($tickets['old'])) {
                foreach ($tickets['old'] as $t) {
                    $t['updated_at'] = $time;
                    $model_ticket->where(array('guid' => $t['guid']))->save($t);
					$logic->update_goods($t, array('ticket_guid' => $t['guid']));
                }
            }
            if (!empty($tickets['new'])) {
//                $data_ticket = array();
                foreach ($tickets['new'] as $k => $t) {
					$tmp_guid = create_guid();
                    $data_ticket = array(
                        'guid'          => $tmp_guid,
                        'activity_guid' => $activity_guid,
                        'name'          => $t['name'],
                        'num'           => $t['num'],
                        'verify_num'    => $t['verify_num'],
                        'is_for_sale'   => $t['is_for_sale'],//isset($t['is_for_sale']) ? '1' : '0',
						'price'         => validate_data($t, 'price', 0),
                        'created_at'    => $time,
                        'updated_at'    => $time
					);
					$goods[] = array(
						'name'        => $t['name'],
						'seller_guid' => $session_auth['guid'],
						'target_guid' => $activity_guid,	
						'price'       => validate_data($t, 'price', 0),
						'storage'     => intval($t['num']),
						'ticket_guid' => $tmp_guid,
					);

                    $model_ticket->add($data_ticket);
                }
				$logic->add_arr($goods);
            }
        }

        $this->assign('activity_info', $activity_info);
        $this->assign('subject_info', D('ActivitySubject')->find_one(array('guid' => $activity_info['subject_guid'])));

        // 票务
        $ticket = M('ActivityAttrTicket')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->order('id asc')->select();
        foreach ($ticket as $k => $t) {
            $user_width_this_ticket = M('ActivityUserTicket')->field('guid')->where(array('ticket_guid' => $t['guid'], 'status' => '2', 'is_del' => '0'))->count();
            $ticket[$k]['num_used'] = $user_width_this_ticket;
        }

        $this->assign('ticket', $ticket);
        $this->assign('meta_title', '票务设置');
        $this->display();
    }

    /**
     * 删除票务
     * CT: 2015-03-26 16:28 BY YLX
     */
    public function ajax_delete_ticket()
    {
        if (IS_AJAX) {
            $tid = I('post.tid');
            if (empty($tid)) $this->ajaxResponse(array('status' => 'ko', 'msg' => '删除失败，请稍后重试。'));

            $result = M('ActivityAttrTicket')->where(array('guid' => $tid))->delete();
            if ($result) {
                $logic = D('Goods', 'Logic');
                $logic->update_goods(array('status' => 0), array('ticket_guid' => $tid));
                $this->ajaxResponse(array('status' => 'ok'));
            } else {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '删除失败，请稍后重试。'));
            }
        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => '删除失败，请稍后重试。'));
        }
    }

    /**
     * 检查票务名称在当前活动下是否统一
     * CT: 2015-08-13 16:28 BY YLX
     */
    public function ajax_check_ticket_name()
    {
        if (IS_AJAX) {
            $aid = I('post.aid');
            $name = I('post.name');

            $result = M('ActivityAttrTicket')->where(array('activity_guid' => $aid, 'name' => $name))->getField('id');
            if ($result) {
                echo 'true'; exit();
            } else {
                echo 'false'; exit();
            }
        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => '删除失败，请稍后重试。'));
        }
    }

    /**
     * 选择发布后, 发送消息通知
     * @param $group_guid
     * @param $activity_name
     *
     * @return boolen
     * CT: 2014-11-24 17:07 by ylx
     */
    private function _send_notice($group_guid, $activity_name, $activity_guid)
    {
        $session_auth = $this->kookeg_auth_data();

        $content = '通知: 新活动 "' . $activity_name . '"';
        $time    = time();

        // 通过SDK发送消息
        $msg = array(
            'from_id'     => $session_auth['guid'],
            'from_name'   => $session_auth['org_name'],
            'from_iconID' => $session_auth['org_logo'],
            'to_id'       => '',
            'to_name'     => '',
            'to_iconID'   => '',
            'content'     => htmlspecialchars_decode($content),
            'send_time'   => $time,
            'msg_type'    => '11101',
            'type'        => '11006',
            'is_read'     => 0
        );

        /**********************wangleiming modify start*****************/
        $box = array(
            'guid'   => $session_auth['guid'],
            'group_guid' => $group_guid,
        );
        $this->pushMessageList($box, $msg);
        /***********************wangleiming modify end*****************/

        $data_box = array(
            'guid'               => create_guid(),
            'name'               => '活动通知消息-' . $activity_guid,
            'group_guid'         => $group_guid,
            'user_guid'           => $session_auth['guid'],
            'status'             => '1',
            'content'            => $content,
            'is_activity_notice' => '1',
            'created_at'         => $time,
            'updated_at'         => $time
        );
        D('OrgGroupMsgBox')->insert($data_box);

        //保存聊天记录
        $data_msg = array(
            'guid'           => create_guid(),
            'org_group_guid' => $group_guid,
            'msg_box_guid'   => $data_box['guid'],
            'from_guid'      => $session_auth['guid'],
            'from_name'      => $session_auth['org_name'],
            'from_photo'     => $session_auth['org_logo'],
            'to_guid'        => $activity_guid,
            'to_name'        => '',
            'to_photo'       => '',
            'content'        => $content,
            'sdk_msg_id'     => '1',
            'sent_time'      => $time,
            'created_at'     => $time,
            'updated_at'     => $time,
            'type'           => '3',

        );
        D('OrgMsg')->insert($data_msg);

        return true;
    }

    /**
     * 报名活动 - 其它设置页
     * CT: 2015-03-25 10:24 BY YLX
     */
    public function signup_setting()
    {
        $activity_guid = I('get.guid');
        if (empty($activity_guid)) {
            $this->error('活动不存在。');
        }
        $activity_info = D('Activity')->find_one(array('guid' => $activity_guid, 'is_del' => '0'));
        if (empty($activity_info)) {
            $this->error('活动不存在.');
        }

        if (IS_POST) {
            $show_front_list = I('post.show_front_list', 0);
            $result          = D('Activity')->where(array('guid' => $activity_guid))->save(array('show_front_list' => $show_front_list));
            if ($result) {
                $this->success('基本设置更新成功。');
                exit();
            }
        }

        $this->assign('activity_info', $activity_info);
        $this->assign('subject_info', D('ActivitySubject')->find_one(array('guid' => $activity_info['subject_guid'])));
        $this->assign('meta_title', '其它设置');
        $this->display();
    }

    /**
     * ajax立即发布活动
     * ct: 2015-05-27 16:06 by ylx
     */
    public function ajax_publish_activity()
    {
        if (IS_AJAX) {

            //判断发布数是否超过配置
            if ($this->check_activity_num('published')) {
//                $vip_config = $this->get_vip_info();
//                $publish_limit_num = $vip_config['NUM_ACTIVITY_PUBLISH_PER_DAY'];
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '发布失败, 发布次数超出每日是发布限制.'));
            }

            $aid = I('post.aid');
            if (strlen($aid) != 32) {
                $this->ajaxResponse(array('status' => 'ok', 'msg' => '参数错误，请刷新后重试。'));
            }

            $model_activity = D('Activity');
            $time           = time();
            $info           = $model_activity->find_one(array('guid' => $aid));
            if ($info['start_time'] > $time) { //未开始
                $result = $model_activity->where(array('guid' => $aid))->save(array('status' => '1', 'updated_at' => $time, 'published_at' => $time));
                $status = '未开始';
            } else if ($info['start_time'] < $time && $info['end_time'] > $time) { //进行中
                $result = $model_activity->where(array('guid' => $aid))->save(array('status' => '1', 'updated_at' => $time, 'published_at' => $time));
                $status = '进行中';
            } else if ($info['end_time'] < $time) { //已结束
                // 更新活动状态
                $result = $model_activity->where(array('guid' => $aid))->save(array('status' => '2', 'updated_at' => $time, 'published_at' => $time));
                $status = '已结束';
            }

            if ($result) {
                $this->_send_notice($info['org_group_guid'], $info['name'], $aid);
                $this->ajaxResponse(array('status' => 'ok', 'msg' => '发布成功。', 'activity_status' => $status));
            } else {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '发布失败，请重试。'));
            }
        }
    }

    /**
     * 活动预览
     * ct: 2015-05-27 17:24 by ylx
     */
    public function activity_preview()
    {
        $aid  = I('get.aid');
        $info = D('Activity')->find_one(array('guid' => $aid));

        $this->assign('info', $info);
        $this->display();
    }

    /**
     * 把活动从该主题下删除
     * ct: 2015-05-28 16:09 by ylx
     */
    public function ajax_delete_activity_from_subject()
    {
        if (IS_AJAX) {
            $aid = I('post.aid');
            $sid = I('post.sid');
            if (empty($aid) || empty($sid)) {
                $this->ajaxResponse(array('status' => 'ok', 'msg' => '参数错误，请刷新后重试。'));
            }

            $result = D('Activity')->where(array('guid' => $aid))->setField('subject_guid', null);
            if ($result) {
                $this->ajaxResponse(array('status' => 'ok', 'msg' => '移除成功。'));
            } else {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '移除失败，请重试。'));
            }
        }
    }

    /**
     * 缓存表单数据
     * CT 2015-04-28 by wangleiming
     **/
    private function _set_form_cache()
    {
        if ($undertakers = S('activity::activity_add::undertakers')) {
            $this->assign('undertakers', $undertakers);
            S('activity::activity_add::undertakers', null);
        }
        if ($flow = S('activity::activity_add::flows')) {
            $this->assign('flows', $flow);
            S('activity::activity_add::flows', null);
        }
        if ($tickets = S('activity::activity_add::tickets')) {
            $this->assign('tickets', $tickets);
            S('activity::activity_add::tickets', null);
        }
        if ($items = S('activity::activity_add::items')) {
            $this->assign('items', $items);
            S('activity::activity_add::items', null);
        }
        if ($base = S('activity::activity_add::base')) {
            $this->assign('base', $base);
            if (isset($base['area_2']) && $base['area_2']) {
                $this->assign('area_2', D('Area')->find_all(array('parent_id' => $base['area_1']), 'id, name'));
            }
            S('activity::activity_add::base', null);
        }
    }

    private function _format_form_data($activity_guid)
    {

        //************** 创建表名表单 *************
        $data_build   = array();
        $data_build[] = array( // 姓名
            'guid'          => create_guid(),
            'activity_guid' => $activity_guid,
            'name'          => I('post.real_name'),
            'note'          => I('post.real_name_note'),
            'ym_type'       => 'real_name',
            'html_type'     => 'text',
            'is_required'   => 1,
            'is_info'       => 1,
            'created_at'    => $this->time,
            'updated_at'    => $this->time
        );
        $data_build[] = array( //手机
            'guid'          => create_guid(),
            'activity_guid' => $activity_guid,
            'name'          => I('post.mobile'),
            'note'          => I('post.mobile_note'),
            'ym_type'       => 'mobile',
            'html_type'     => 'text',
            'is_required'   => 1,
            'is_info'       => 1,
            'created_at'    => $this->time,
            'updated_at'    => $this->time
        );
        $items        = I('post.items');
        if (!empty($items)) { //其它
            $data_options = array();
            foreach ($items as $i) {
                if (isset($i['name'])) {
                    $buid_guid    = create_guid();
                    $data_build[] = array(
                        'guid'          => $buid_guid,
                        'activity_guid' => $activity_guid,
                        'name'          => $i['name'],
                        'note'          => $i['note'],
                        'ym_type'       => $i['ym_type'],
                        'html_type'     => $i['html_type'],
                        'is_info'       => 0, //$i['is_info'],
                        'is_required'   => isset($i['is_required']) ? $i['is_required'] : 0,
                        'created_at'    => $this->time,
                        'updated_at'    => $this->time
                    );
                    if (!empty($i['options'])) {
                        foreach ($i['options'] as $o) {
                            $data_options[] = array(
                                'guid'          => create_guid(),
                                'activity_guid' => $activity_guid,
                                'build_guid'    => $buid_guid,
                                'value'         => $o,
                                'created_at'    => $this->time,
                                'updated_at'    => $this->time
                            );
                        }
                    }
                }
            }
        }
        return array($data_build, $data_options);
    }

    private function _reset_edit_cache()
    {
        S('signup_edit::undertakers', null);
        S('signup_edit::flows', null);
        S('signup_edit::old_tickets', null);
        S('signup_edit::form_build', null);
        S('signup_edit::form_options', null);
    }

    private function _reset_add_cache()
    {
        S('activity::activity_add::vote', null);
        S('activity::activity_add::undertakers', null);
        S('activity::activity_add::flows', null);
        S('activity::activity_add::tickets', null);
        S('activity::activity_add::items', null);
        S('activity::activity_add::base', null);
        S('activity::activity_add::questions', null);
    }

    /**
     * ajax 检测字符串中是否含有限制词
     *
     * @access public
     * @param  void
     * @return json
     **/

    public function check_banned_words()
    {
        if (IS_AJAX) {
            $name = I('post.name');
            $word = censor_words($name);
            if ($word) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => "标题包含敏感词[{$word}]"));
            }
            $this->ajaxResponse(array('status' => 'ok'));
            exit();
        }
    }

}
