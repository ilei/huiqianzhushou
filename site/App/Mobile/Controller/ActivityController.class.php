<?php
namespace Mobile\Controller;

use       Mobile\Controller\BaseController;
use Org\Api\YmChat;
use Org\Api\YmSMS;

class ActivityController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function _show_error($msg = '', $countdown = '')
    {
        $msg = $msg ? $msg : L('_ACTIVITY_NOT_FOUND_');
        parent::_show_error($msg, $countdown);
    }

    /**
     * 活动预览
     * 2015-02-28 16:50 by ylx
     */
    public function preview()
    {
        layout('layout');
        $activity_guid = I('get.aid');
        if (empty($activity_guid)) {
            $this->_show_error();
        }

        $activity_info = D('Activity')->where('guid = "' . $activity_guid . '"')->find();
        // 检查活动状态及是否存在
        if (empty($activity_info)) {
            $this->_show_error();
        }
        $this->title = $activity_info['name'];

        // 存储是否在APP中打开状态
//        $app = I('get.app', null);
//        if (isset($app)) {
//            S('app', $app);
//        } else {
//            S('app', 0);
//        }

        // 判断是否为公开活动, 若为公开则不限制报名次数
        $is_public = $activity_info['is_public'];
        $this->assign('is_public', $is_public);
        $user_guid = 'outside_user_' . create_guid();

        $subject_info = M('ActivitySubject')->where('guid = "' . $activity_info['subject_guid'] . '"')->find();
        $user_info = M('User')->where('guid = "' . $activity_info['user_guid'] . '"')->find();
        $this->assign('user_guid', $user_guid);
        $this->assign('org_name', $user_info['mobile']);
        $this->assign('activity_info', $activity_info);
        $this->assign('meta_title', $activity_info['name']);
        $this->assign('subject_info', $subject_info);
        $this->assign('menu', true);
        session('preview', 1);

        $this->_signup($activity_info, $user_guid);
    }

    /**
     * 活动展示
     *
     * @access public
     * @param  void
     * @return void
     * @author wangleiming
     **/

    public function view()
    {
        layout('layout');
        $activity_guid = I('get.aid');
        // 存储是否在APP中打开状态
//        $app = I('get.app', null);
//        if (isset($app)) {
//            S('app', $app);
//        } else {
//            S('app', 0);
//        }
        if (empty($activity_guid)) {
            $this->_show_error();
        }

        $activity_info = D('Activity')->where(array('guid' => $activity_guid))->find();
        // 检查活动状态及是否存在
        if ($activity_info['status'] == '0' || empty($activity_info)) {
            $this->_show_error();
        }
        if ($activity_info['status'] == '3') {
            $this->_show_error(L('_ACTIVITY_CLOSED_'));
        }
        $this->title = $activity_info['name'];
        $auth = $this->get_auth_session();

        $user_guid = $auth['guid'];
        // 检查活动是否开始
        if ($activity_info['start'] > time()) {
            $this->_show_error(L('_RANGE_ACTIVITY_'), date("Y/m/d H:i:s", $activity_info['start']));
        }

        $subject_info = M('ActivitySubject')->where('guid = "' . $activity_info['subject_guid'] . '"')->find();
        $user_info = M('User')->where('guid = "' . $activity_info['user_guid'] . '"')->find();
        $creator_guid = $user_info['guid'];

        //记录已读
        $browse_data = array(
            'activity_guid' => $activity_guid,
            'user_guid' => $user_guid,
            'creator_guid' => $creator_guid,
            'created_at' => time()
        );
        M('ActivityBrowse')->add($browse_data, array(), true);


        $this->assign('user_guid', $user_guid);
        $this->assign('org_name', $user_info['email']);
        $this->assign('activity_info', $activity_info);
        $this->assign('meta_title', $activity_info['name']);
        $this->assign('subject_info', $subject_info);
        session('preview', 0);
        // 判断是否为公开活动, 若为公开则不限制报名次数
        $this->assign('is_public', $activity_info['is_public']);
        $this->assign('menu', true);

        //设置Cookie用于计算Redirect路径
        setcookie("redirect", U("Mobile/Activity/view", array('guid' => $activity_guid)), null, "/");

        $this->_signup($activity_info, $user_guid);
    }

    /**
     * 报名活动浏览
     * @param $activity_info
     * @param $user_guid
     */
    public function _signup($activity_info, $user_guid)
    {

        $activity_guid = $activity_info['guid'];
        $signup_form = M('ActivityForm')
            ->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))
            ->select();
        //报名人数
        $user_count = M('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->count();
        list($user_can_signup, $tickets) = $this->_caculate_signup_num($activity_guid, $activity_info);
        $this->assign('user_can_signup', $user_can_signup);

        $this->assign('check_signup_time', $this->_check_signup_time($activity_info));



        $is_user_signed = D('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'user_guid' => $user_guid, 'is_del' => '0'))->count();



        $user_signed_count=D('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'user_guid' => $user_guid,))->count();



        // 承办机构
        $under_takers = M('ActivityAttrUndertaker')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->order('id asc')->select();
        $model_view_takers = array();
        foreach ($under_takers as $m) {
            if ($m['type'] == 1) { //主办
                $model_view_takers['主办方'][] = $m['name'];
            }else{
                foreach(explode(',', $m['name']) as $value){
                    if($value){
                        $model_view_takers[$m['type']][] = $value;
                    }
                }
            }
        }
        $this->assign('undertaker', $model_view_takers);
        // 活动流程
        $this->assign('flow', M('ActivityAttrFlow')->where(array('activity_guid' => $activity_guid, 'is_del' => '0'))->order('id asc')->select());

        if ($activity_info['show_front_list'] == 1) {
            $this->_signup_user_list($activity_guid);
        }

        $this->assign('is_user_signed', $is_user_signed);
        $this->assign('user_count', $user_count);
        $this->assign('signup_form', $signup_form);
        $this->assign('user_signed_count',$user_signed_count);
        $this->show('signup_new');
    }

    /**
     * ajax调取已报名用户列表
     * CT：2015-03-25 11:49 by ylx
     */
    public function ajax_signup_user_list()
    {
        $aid = I('get.aid');
        if (empty($aid)) {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => '参数错误，请稍后重试。'));
        }
        // 用户列表
        $list = $this->_signup_user_list($aid);

        if (empty($list)) {
            $this->ajaxResponse(array('status' => 'nomore', 'msg' => '没有更多数据了。'));
        }

        $this->ajaxResponse(array('status' => 'ok', 'msg' => '加载成功。', 'data' => $list));
    }

    /**
     * 获取报名用户列表
     * @param $aid 活动GUID
     * CT：2015-03-25 11:49 by ylx
     */
    private function _signup_user_list($aid)
    {
        $where = "activity_guid='$aid' and is_del=0";
        list($show, $list) = D('ActivityUserinfo')->pagination($where, I('get.p', '1'), C('NUM_PER_PAGE', null, 10));
        $this->assign('user_list', $list);
        $this->assign('user_count', D('ActivityUserinfo')->get_count($where));
        return $list;
    }

    /**
     * 检查用户是否重复报名
     * CT：2015-03-25 11:49 by ylx
     */
    public function ajax_check_signup_user()
    {
        $params = I('post.');
        $aid = $params['aid'];
        $kv = $params['info'];
        $value = current($kv);
        $key = key($kv);

        switch ($key) {
            case 'mobile':
                $signed = M('ActivityUserinfo')->where(array($key => $value, 'activity_guid' => $aid, 'is_del' => '0'))->find();//已报名

                $history_sign_count = M('ActivityUserinfo')->where(array($key => $value, 'activity_guid' => $aid))->count();//获取报名数量

                //已报名信息不为空,历史报名信息>=3 不允许报名
                $result = empty($signed) && ($history_sign_count <= 3);

                echo $result ? 'true' : 'false';
                exit();
                break;
            default:
                echo 'true';
                exit();
                break;
        }
    }

    /**
     * 用户报名
     * CT：2015-03-25 11:49 by ylx
     */
    public function signup_user()
    {
        layout('layout');
        $this->title = L('_ACTIVITY_SIGNUP_TITLE_');
        $activity_guid = I('get.aid');
        if (empty($activity_guid)) {
            $this->_show_error();
        }
        $activity_info = D('Activity')->where('guid = "' . $activity_guid . '"')->find();
        // 检查活动状态及是否存在
        if (session('preview') == 0 && ($activity_info['status'] != 1 || empty($activity_info))) {
            $this->_show_error();
        }
        $auth = $this->get_auth_session();
        $user_guid = $auth['guid'];

        $user_attr_info=D('UserAttrInfo')->where(array('user_guid'=>$user_guid))->find();//获取当前用户的其它信息
        // 判断是否为公开活动, 若为公开则不限制报名次数
        $is_public = $activity_info['is_public'];

        // 检查报名时
        $res = $this->_check_signup_time($activity_info);
        if (!$res['status']) {
            $this->error('报名尚未开始, 请稍候.');
        }
        $is_user_signed = D('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'user_guid' => $user_guid, 'is_del' => '0'))->find();
        if ($is_user_signed) {
            $this->error('您已报名，请勿重复报名', U('Activity/view', array('guid' => $activity_guid, 'token' => I('get.token'))));
        }

        // 检查报名人数
        list($user_can_signup, $tickets) = $this->_caculate_signup_num($activity_guid, $activity_info);
        if (empty($tickets)) {
            $this->error('报名尚未开始或者票已售尽。');
        }
        foreach($tickets as $key => $value){
            if($value['price']){
                if(!$this->get_auth_session()){
                   $this->redirect('Auth/Login'); 
                } 
                break;
            }  
        }
        if ($user_can_signup == false) {
            $this->error('报名尚未开始, 或者人数已满', U('Activity/view', array('guid' => $activity_guid, 'uid' => $user_guid)));
        }

        // 提交报名
        if (IS_POST) {
            $params = I('post.');
            $is_user_signed = D('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'mobile' => $params['info']['mobile'], 'is_del' => 0))->find();
            $signed_count = D('ActivityUserinfo')->where(array('activity_guid' => $activity_guid, 'mobile' => $params['info']['mobile']))->count();

            if ($is_user_signed) {
                $this->error('您已报名，请勿重复报名', U('Activity/view', array('guid' => $activity_guid, 'token' => I('get.token'))));
            }
            if ($signed_count >= 3) {
                $this->error('您已超过报名次数', U('Activity/view', array('guid' => $activity_guid, 'token' => I('get.token'))));
            }

            if (empty($params['ticket'])) {
                $this->error('报名失败，请选择票务后重试。');
            }

            if (empty($params) || empty($params['info']['real_name']) || empty($params['info']['mobile'])) {
                $this->error('报名失败，请稍后重试.');
            }
            list($res, $new, $order_id) = D('Signup', 'Logic')->signup($activity_guid, $params);
            if (!$res) {
                $this->error('报名失败，请稍后重试.');
            }
            if ($order_id) {
                $this->success('正在跳转的支付页面', U('Mobile/Pay/dopay', array('order_id' => $order_id), true, true));
            }
            $msg = '报名成功';
            if ($new) {
                $this->waitSecond = 5;
                $msg .= ',您首次使用本平台,平台将为您自动注册,账号和密码均为您的手机号.';
            }
            $this->success($msg, U('Activity/view', array('guid' => $activity_guid, 'token' => I('get.token'))));
            exit();
        }

        $build_info = D('ActivityForm')->where(array('activity_guid' => $activity_info['guid']))->order('sort desc,id')->select();
        $option_info = D('ActivityFormOption')->where(array('activity_guid' => $activity_info['guid']))->field('guid,build_guid,value')->select();


        foreach ($option_info as $o) {
            $format_option_info[$o['build_guid']][] = $o;
        }
        $this->assign('activity_info', $activity_info);
        $this->assign('build_info', $build_info);
        $this->assign('option_info', $format_option_info);
        $this->assign('meta_title', '填写报名表');
        $this->assign('userinfo', $auth);
        $this->assign('user_attr_info',$user_attr_info);

        //设置RedirectCookie
        setcookie("redirect", U("Mobile/Activity/userinfo", array('guid' => $activity_guid)), null, "/");
        $this->show('signup_user');
    }

    /**
     * 检查报名时间
     * @param $activity_info
     * @return bool
     */
    private function _check_signup_time($activity_info)
    {
        // 判断报名是否开始
        $time = time();
        $ticket = M('ActivityAttrTicket')->where(array('activity_guid' => $activity_info['guid']))->select();
        $start = min(array_columns($ticket, 'start_time', 'id'));
        $end = max(array_columns($ticket, 'end_time', 'id'));
        if (!$start || !$end) {
            $start = $activity_info['start_time'];
            $end = $activity_info['end_time'];
        }
        if ($time < $start || $time > $end) {
            $signup_status['status'] = false;
            if ($time < $start) {
                $signup_status['time_type'] = 'start';
            } else {
                $signup_status['time_type'] = 'end';
            }
            return $signup_status;
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
     * CT: 2015-03-30 16:00 by ylx
     */
    private function _caculate_signup_num($activity_guid, $activity_info)
    {
        //判断报名人数
        // 总票数
        $time = time();
        $condition = array(
            'activity_guid' => $activity_guid,
            'is_del' => '0',
            'is_for_sale' => '1',
            'start_time' => array('ELT', $time),
            'end_time' => array('EGT', $time),
        );
        $total_ticket = M('ActivityAttrTicket')->field('SUM(num) as total')
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

            // 当报名时, 判断是否有余票
            if (IS_POST) {
                $ticket_guid = I('post.ticket');
                $check_total = M('ActivityAttrTicket')->where(array('guid' => $ticket_guid))->getField('num');
                $check_signup = M('ActivityUserTicket')->where(array('activity_guid' => $activity_guid, 'ticket_guid' => $ticket_guid))->count();
                if ($check_signup >= $check_total) {
                    $this->error('该票已经售尽, 请重新选择票务.');
                }
            }
            $time = time();

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

            $this->assign('tickets', $tickets);
            return array(true, $tickets);
        } else { // 如果票务没被设置， 走 参与人数
            $total_num = $activity_info['num_person'];
            if ($total_num > 0 && $user_count >= $total_num) {
                return array(false, null);
            }
            return array(true, null);
        }
    }

    /**
     * 社团内用户检查自己的报名信息
     */
    public function signup_userinfo()
    {
        layout('layout');


        $aid = I('get.aid');
        //js加载
        $this->main = '/Public/mobile/js/view.js';
        if (empty($aid)) {
            $this->error('活动未找到，请稍后重试。');
        }

        $auth = $this->get_auth_session();


        $user_guid = $auth['guid'];
        $info = M('ActivityUserinfo')->where(array('activity_guid' => $aid, 'user_guid' => $user_guid, 'is_del' => '0'))->find();
        $other = M('ActivityUserinfoOther')->where(array('userinfo_guid' => $info['guid'], 'is_del' => '0'))->select();

        //检查需要支付的订单
        $order=M('Order')
            ->where(array(
            'target_guid'=>$aid,
            'buyer_guid'=>$info['guid'],
            'status'=>array('IN',array(0,2))
        ))->find();



        if (empty($info)) {
            $this->error('用户尚未报名，请先报名。', U('Activity/view', array('guid' => $aid)), 1);
        }

        //确定是否需要重新支付
        if(!empty($order)){
            $this->assign('order_guid',$order['guid']);
            $this->assign('needRepay',true);
        }else{
            $this->assign('needRepay',false);

        }
        $this->assign('activity_info', D('Activity')->where(array('guid' => $aid,'is_del'=>0))->find());
        $this->assign('ticket_info', M('ActivityUserTicket')->where(array('activity_guid' => $aid, 'user_guid' => $user_guid,'is_del'=>0))->find());
        $this->assign('info', $info);
        $this->assign('other', $other);
        $this->assign('meta_title', '报名人员详情');
        $this->show('signup_userinfo_new');
    }

    /**
     * 用户取消报名, 目前只支持平台用户退出
     * CT： 2015-03-01 15:00 by ylx
     */
    public function signup_cancel()
    {
        $aid = I('get.aid'); // activity guid
        $iid = I('get.iid'); // ActivityUserinfo guid
        if (empty($aid) || empty($iid)) {
            $this->error('参数错误，请稍后重试。');
        }

        $userinfo = M('ActivityUserinfo')->where(array('guid' => $iid))->find();
        $user_guid = $userinfo['user_guid'];

        $where = array('activity_guid' => $aid, 'user_guid' => $user_guid);
        $check_ticket_status = M('ActivityUserTicket')->where($where)->getField('status');
        if ($check_ticket_status >= 2) {
            $this->error('您的电子票已发送，无法取消报名。');
        }

        //收费票不能取消报名
        $order=M('Order')->where(
            array('buyer_guid'=>$iid,
                  'target_guid'=>$aid,
                  'status'=>array('IN',array(1)),//支付成功
            ))
            ->find();
        if($order['total_price']>0){
            $this->error('收费票，无法取消报名。');
        }



        $result = M('ActivityUserinfo')->where(array('guid' => $iid))->save(array('is_del' => '1')); // 删除用户报名信息
        if ($result) {
            M('ActivityUserinfoOther')->where(array('userinfo_guid' => $iid))->save(array('is_del' => '1', 'updated_at' => time())); //删除用户报名额外信息
            M('ActivityUserTicket')->where($where)->save(array('is_del' => '1', 'updated_at' => time())); //删除用户票务信息
            M('MsgContent')->where($where)->delete();
            M('Order')->where(array('target_guid' => $aid, 'buyer_guid' => $iid))->save(array('status' => 3, 'updated_at' => time()));
            $this->success('成功取消报名', U('Activity/view', array('guid' => $aid, 'token' => I('get.token'))));
        } else {
            $this->error('取消失败，请稍后重试');
        }

    }

    /**
     * 电子票连接
     * CT: 2015-03-12 18:05 BY YLX
     */
    public function ticket()
    {
        $aid = I('get.aid'); //活动guid
        $iid = I('get.iid'); //userinfo guid
        if (empty($aid) || empty($iid)) {
            $this->_show_error('参数错误，请稍后重试。');
        }

        $signup_userinfo = M('ActivityUserinfo')->where(array('guid' => $iid,'is_del'=>0))->find();
        $ticket_info = M('ActivityUserTicket')->where(array('user_guid' => $signup_userinfo['user_guid'], 'activity_guid' => $aid,'is_del'=>'0'))->find();

        // 判断是否已发电子票
        if ($ticket_info['status'] == 0 || $ticket_info['status'] == 1) {
            $this->_show_error('非法请求。');
        }

        $activity_info = D('Activity')->getInfo(array('guid' => $aid));
        $user_realname = M('UserAttrInfo')->where(array('guid' => $activity_info['user_guid']))->getField('realname');
        //        $signup_info   = M('ActivitySignup')->where(array('activity_guid' => $aid))->find();
        // 更改用户票务为 已查看
        if ($ticket_info['status'] > 1) {
            $source = intval(I('get.source'));
            $source = $source == 1 ? 1 : ($source == 2 ? 2 : 3);
            if ($source == 1) {
                $update = array('moblie_verify' => 1);
            } elseif ($source == 2) {
                $update = array('email_verify' => 1);
            }
            if ($update) {
                M('User')->where(array('guid' => $signup_userinfo['user_guid']))->save($update);
            }
            if ($ticket_info['check_source'] == 1 && $source == 2) {
                $source = 4;
            }
            if ($ticket_info['check_source'] == 2 && $source == 1) {
                $source = 4;
            }
            if ($ticket_info['status'] == 2) {
                M('ActivityUserTicket')
                    ->where(array('user_guid' => $signup_userinfo['user_guid'], 'activity_guid' => $aid))
                    ->save(array('status' => 3, 'check_source' => $source, 'updated_at' => time()));
            }
        }
        $this->assign('user_realname', $user_realname);
        //        $this->assign('signup_info', $signup_info);
        $this->assign('activity_info', $activity_info);
        $this->assign('signup_userinfo', $signup_userinfo);
        $this->assign('ticket_info', $ticket_info);
        $this->display();
    }

    public function signin_qrcode()
    {
        $ticket_guid = I('get.tid');
        $ticket_info = M('ActivityUserTicket')->field('ticket_code, activity_guid')->where(array('guid' => $ticket_guid))->find();

        $root_path = UPLOAD_PATH;
        $qr_path = '/org/qrcode/activity/' . $ticket_info['activity_guid'];
        $qr_name = $ticket_info['ticket_code'] . '.png';
        return qrcode($ticket_info['ticket_code'], $root_path . $qr_path, $qr_name);
        die();
    }

    /**
     * 生成活动URL二维码，连接中不带TOKEN，APP扫描时需要加token功能
     * @param $activity_info 活动信息
     * CT: 2015-02-05 09:55 BY YLX
     */
    public function qrcode()
    {
        $aid = I('get.aid');
        $activity_info = D('Activity')->field('user_guid, status')->where(array('guid' => $aid))->find();
        if (empty($aid) || empty($activity_info)) {
            echo '';
            exit();
        }
        if ($activity_info['status'] == 0) {
            $url = U('Mobile/Activity/preview', array('guid' => $aid, 'app' => 1), true, true, false);
            $qr_name = $aid . '_preview.png';
        } else {
            $url = U('Mobile/Activity/view', array('guid' => $aid, 'oid' => $activity_info['user_guid']), true, true, false);
            $qr_name = $aid . '.png';
        }
        $qr_path = '/user/qrcode/activity/' . $activity_info['user_guid'] . '';
        //        $qr_name = $aid.'.png';
        echo qrcode($url, UPLOAD_PATH . $qr_path, $qr_name);
        die();
    }

    /**
     * 举报
     * CT: 2015-11-05 10:50 BY zyz
     */
    public function report() {
        layout('layout_report');
        $this->main = '/Public/mobile/js/report.js';
        $aid = I('get.aid');
        $type = I('get.type');
        $user_guid = I('get.guid');
        if ($type == 1) {
            $user_info = $this->get_auth_session();
        }else{
            $user_info = D('User')->getUserInfo($user_guid);
            $user_attr_info = D('UserAttrInfo')->field('realname')->where(array('user_guid' => $user_guid))->find();
            $user_info['realname'] = $user_attr_info['realname'];
        }
        
        if ($user_info) {
        
            if(IS_POST) {
                $aid = I('get.aid');
                // $uid = I('get.uid');
                $reasons = I('post.reason');
                $more_reason = I('post.more_reason');
                if(empty($reasons) && empty($more_reason)){
                    // set_flash_msg('error', '举报成功.', 'report');
                    $this->error('举报内容不能为空');
                }

                $time = time();
                // $user_info = D('User')->getUserInfo($uid);
                $report_guid = create_guid();
                $data = array(
                    'guid' => $report_guid,
                    'user_guid' => $user_info['guid'],
                    'real_name' => $user_info['realname'],
                    'mobile' => $user_info['mobile'],
                    'email' => $user_info['email'],
                    'obj_guid' => $aid,
                    'obj_type' => '1',
                    'more_reason' => trim($more_reason),
                    'created_at' => $time,
                    'updated_at' => $time
                );
                if(M('Report')->add($data)){
                    if(!empty($reasons)) {
                        foreach($reasons as $r) {
                            $data_reason[] = array(
                                'guid' => create_guid(),
                                'report_guid' => $report_guid,
                                // 'reason_guid' => $r,
                                // 'reason_content' => M('ReportReason')->where(array('guid'=>$r))->getField('content'),
                                'reason_content'=>$r,
                                'created_at' => $time,
                                'updated_at' => $time
                            );
                        }
                        M('ReportReasonCon')->addAll($data_reason);
                    }
                }
                // set_flash_msg('success', '举报成功.', 'report');
                if ($type==2) {
                    $this->success('举报成功', U('Home/Act/preview', array('guid' => $aid), true, true));
                }else{
                    $this->success('举报成功', U('Activity/view', array('aid' => $aid, 'token'=>I('get.token'))));
                }
                
            }

            // 获取预置举报原因
            $this->assign('aid',$aid);
            $this->assign('type',$type);
            $this->assign('user_guid',$user_guid);
            $this->assign('reason', M('ReportReason')->where(array('is_del'=>'0'))->getField('guid, content'));
            $this->show();
        }else{
            if ($type==2) {
                $this->error('请登录', U('Home/Auth/login', array('guid' => $aid), true, true));
            }else{
                $this->redirect('Mobile/Auth/login');
            }
            
        }
    }
}
