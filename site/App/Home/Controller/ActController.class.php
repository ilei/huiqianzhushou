<?php
namespace Home\Controller;

use       Think\Image;

/**
 * 活动控制器
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/
class ActController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        layout('layout_new');
        $this->css[] = 'common/css/DateTimePicker.css';
    }

    public function copy()
    {
        $activity_guid = I('guid');
        if (!$activity_guid) {
            $this->error(L('_ACT_NOT_EXIST_'));
        }
        $auth = $this->get_auth_session();
        $activity_guid = D('Act', 'Logic')->copy($activity_guid, $auth['guid']);
        $this->redirect('Home/Event/index', array('g' => $activity_guid));
    }

    /**
     * ajax  检测活动票务名称是否存在
     *
     * @access public
     * @param  void
     * @return void
     **/

    public function ajax_check_ticket_name()
    {
        if (IS_AJAX) {
            $this->ajax_request_limit('act::ajax_check_ticket_name::');
            $guid = trim(I('get.guid'));
            $name = trim(I('post.name'));
            $ticket = trim(I('post.guid'));
            $condition = array('activity_guid' => $guid, 'name' => $name);
            if ($ticket) {
                $condition['guid'] = array('NEQ', $ticket);
            }
            $exist = M('ActivityAttrTicket')->where($condition)->find();
            echo !$exist ? 'true' : 'false';
        }
        exit();
    }

    /**
     * 票务信息添加
     *
     * @access public
     * @param  void
     * @return void
     **/

    public function ticket_add()
    {
        if (IS_AJAX) {
            $this->ajax_request_limit('act::ticket_add::');
            $guid = trim(I('post.activity_guid'));
            $auth = $this->get_auth_session();
            $act = D('Activity')->where(array('guid' => $guid, 'user_guid' => $auth['guid']))->find();
            if (!$act) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            } else {
                $post = I('post.');
                if (!$post['name']) {
                    $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_TICKET_NAME_NOT_EMPTY_')));
                } elseif (!intval($post['num'])) {
                    $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_TiCKET_NUM_NOT_EMPTY_')));
                }
                $condition = array('activity_guid' => $guid, 'name' => $post['name']);
                $exist = M('ActivityAttrTicket')->where($condition)->find();
                if ($exist) {
                    $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_TICKET_NAME_EXIST_')));
                }
                $logic = D('Act', 'Logic');
                $r = $logic->save_ticket(array('new' => array($post)), $guid, $act, $auth['guid'], false);
                if($r){
                    $this->ajax_return(array('status' => C('ajax_success')));
                } else {
                    $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
                }
            }
        }
        exit();
    }

    /**
     * 票务信息编辑
     *
     * @access public
     * @param  void
     * @return void
     **/

    public function ticket_edit()
    {
        if (IS_AJAX) {
            $this->ajax_request_limit('act::ticket_edit::');
            $activity_guid = trim(I('get.aguid'));
            $guid = trim(I('post.guid'));
            $post = I('post.');
            if (!$post['name']) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_TICKET_NAME_NOT_EMPTY_')));
            } elseif (!intval($post['num'])) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_TiCKET_NUM_NOT_EMPTY_')));
            }
            $condition = array('activity_guid' => $activity_guid, 'name' => $post['name']);
            $exist = M('ActivityAttrTicket')->where($condition)->find();
            if ($exist && $exist['guid'] != $guid) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_TICKET_NAME_EXIST_')));
            }
            $act = D('Activity')->where(array('guid' => $activity_guid))->find();
            $logic = D('Act', 'Logic');
            $res  = $logic->save_ticket(array('old' => array($post)), $activity_guid, $act, $auth['guid'], false);
            if ($res) {
                $this->ajax_return(array('status' => C('ajax_success')));
            } else {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            }
        }
        exit();
    }

    /**
     * 获取票的信息
     *
     * @access public
     * @param  void
     * @return void
     **/

    public function get_ticket_info()
    {
        if (IS_AJAX) {
            $this->ajax_request_limit('act::get_ticket_info::');
            $guid = I('get.guid');
            if (!$guid) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            } else {
                $condition = array('guid' => $guid);
                $res = M('ActivityAttrTicket')->where($condition)->find();
                $this->assign('ticket', $res);
                $content = $this->fetch('ticket_info');
                if ($content && $res) {
                    $this->ajax_return(array('status' => C('ajax_success'), 'content' => $content));
                } else {
                    $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
                }
            }
        }
        exit();
    }

    /**
     * 开始或者开始售票
     *
     * @access public
     * @param  void
     * @return void
     **/

    public function stop_sale()
    {
        if (IS_AJAX) {
            $this->ajax_request_limit('act::stop_sale::');
            $guid = trim(I('get.guid'));
            $status = intval(I('post.status')) ? 1 : 0;
            if (!$guid) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            } else {
                $condition = array('guid' => $guid);
                $data = array('is_for_sale' => $status);
                $res = M('ActivityAttrTicket')->where($condition)->save($data);
                $this->ajax_return(array('status' => C('ajax_success')));
            }
        }
        exit();
    }

    /**
     * 关闭开启报名
     *
     * @access  public
     * @param   void
     * @return  void
     **/

    public function change_signup()
    {
        if (IS_AJAX) {
            $this->ajax_request_limit('act::change_signup::');
            $status = intval(I('post.status')) ? 1 : 0;
            $guid = trim(I('post.guid'));
            if (!$guid) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            } else {
                $auth = $this->get_auth_session();
                $condition = array('guid' => $guid, 'user_guid' => $auth['guid']);
                $data = array('is_signup' => $status);
                $res = M('Activity')->where($condition)->save($data);
                $this->ajax_return(array('status' => C('ajax_success')));
            }
        }
        exit();
    }

    /**
     * 门票管理
     *
     * @access public
     * @param  string $guid 活动GUID
     * @return void
     **/

    public function tmanage()
    {
        $guid = I('get.aguid');
        if (!$guid) {
            $this->error(L('_ACT_NOT_EXIST_'));
        } else {
            $auth = $this->get_auth_session();
            $condition = array('user_guid' => $auth['guid'], 'guid' => trim($guid));
            $activity = D('Activity')->where($condition)->find();
            if (!$activity) {
                $this->error(L('_ACT_NOT_EXIST_'));
            } else {
                $this->title = L('_TICKET_MANAGE_');
                $this->css[] = 'meetelf/css/switch_1.css';
                $this->css[] = 'meetelf/css/create-activities.css';
                $this->main  = '/Public/meetelf/home/js/build/home.act.tmanage.js';
                $ticket = M('ActivityAttrTicket')->where(array('activity_guid' => trim($guid), 'is_del' => 0))->select();
                $sales = M('ActivityUserTicket')->where(array('activity_guid' => trim($guid), 'is_del' => 0))->field('ticket_guid, count(guid) as total')->group('ticket_guid')->select();
                if ($sales && $ticket) {
                    $sales = array_columns($sales, 'total', 'ticket_guid');
                    foreach ($ticket as &$value) {
                        $value['sales'] = $sales[$value['guid']];
                    }
                }
//                if($activity['is_verify'] == 0){
//                     4;
//                }elseif($activity['is_verify'] == 1){
//                    $activity['status'];}else{
//                    $activity['is_verify'];
//                }

                $activity['activity_is_verify'] = $activity['is_verify'];
                $this->assign('act', $activity);
                $this->assign('tickets', $ticket);
                $this->show();
            }
        }

    }

    /**
     * 新增或编辑活动
     *
     * @access private
     * @param  $type
     * @param  string $activity_guid
     * @return mixed
     **/

    private function _add_activity($activity_guid = null)
    {
        // 若活动不为文章活动则开始时间和结束时间为必填
        $start_time = I('post.start_time', null);
        $end_time = I('post.end_time', null);
        if (empty($start_time) || empty($end_time)) {
            $this->ajax_return(C('ajax_failed', L('_ACT_START_END_TIME_NOT_EMPTY_')));
        }

        $this->time = time();
        // 获取创建者GUID
        $user_guid = $this->get_auth_session('guid');
        $is_public = I('post.is_public');
        $show_front_list = I('post.show_front_list');
        $data_activity = array(
            'user_guid' => $user_guid,
            'subject_guid' => I('post.options', ''),
            'name' => trim(I('post.title')),
            'content' => I('post.content'),
            'status' => I('post.status', 0),
            'is_verify' => 0,
            'poster' => I('post.poster', ''),
            'is_public' => empty($is_public) ? '1' : '0', //这里修改 页面勾选设置为私有
            'start_time' => !empty($start_time) ? strtotime($start_time) : '',
            'end_time' => !empty($end_time) ? strtotime($end_time) : '',
            'areaid_1' => I('post.area_1'),
            'areaid_1_name' => get_area(I('post.area_1')),
            'areaid_2' => I('post.area_2'),
            'areaid_2_name' => get_area(I('post.area_2')),
            'address' => trim(I('post.address')),
            'lng' => trim(I('post.lng', '')),
            'lat' => trim(I('post.lat', '')),
            'keyword' => trim(I('post.keyword'), ''),
            'num_person' => 0,
            'updated_at' => $this->time,
            'show_front_list' => empty($show_front_list) ?'0':'1',//正常逻辑 勾选了显示报名人
        );

        if (!$activity_guid) {
            $data_activity['guid'] = create_guid();
            $data_activity['created_at'] = $this->time;
        }

        // 判断是否发布
        if ($data_activity['status'] == '1') {
            //判断发布数是否超过配置
            if ($this->check_activity_num('published')) {
                $data_activity['status'] = '0';
                $not_allow_publish = true;
            } else {
                $data_activity['published_at'] = $this->time;
                $not_allow_publish = false;
            }
        }
        $model_activity = D('Activity');
        // 保存活动
        if (!$activity_guid) {
            list($check, $r) = $model_activity->insert($data_activity);
        } else {
            $condition = array('guid' => $activity_guid);
            list($check, $r) = $model_activity->update($condition, $data_activity);
        }
        send_email('service@yunmai365.com','酷客会签','活动审核提醒','<h6><b>有新活动发布了，请去管理后台审核活动</b></h6>');
        if (!$check || !$r) {
            $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_ACT_SAVE_FAILED_')));
        }
        return array($not_allow_publish, $data_activity);
    }


    /**
     * 检测是否还可以创建活动
     *
     * @access private
     * @param  string $type
     * @return true or false
     **/

    private function check_activity_num($type = '')
    {
        return false;
        $auth = $this->get_auth_session();
        $vip = C($auth['vip']);
        $where = array('created_at' => array('GT', strtotime(today)), 'user_guid' => $auth['guid']);
        $publish = false;
        if (trim($type) == 'published') {
            $publish = true;
            $where['status'] = C('act.going_on');
        }
        $count = M('Activity')->where($where)->count();
        return $publish ? $count >= $vip['NUM_ACTIVITY_PUBLISH_PER_DAY'] : $count >= $vip['NUM_ACTIVITY_PER_DAY'];
    }


    /**
     * 编辑活动
     *
     * @access public
     * @param  void
     * @return void
     * @author wangleiming
     **/

    public function edit()
    {
        $this->title = L('_ACT_EDIT_TITLE_');
        $this->css[] = 'meetelf/css/create-activities.css';
        $this->main = '/Public/meetelf/home/js/home.act.add.js';
        $auth = $this->get_auth_session();
        $user_guid = $auth['guid'];
        $activity_guid = trim(I('get.aguid')) ? trim(I('get.aguid')) : trim(I('get.guid'));
        $logic = D('Act', 'Logic');
        if (IS_POST) {
            $this->ajax_request_limit('act::ajax_add_activity', 1, 5);
            $name = I('post.name');
            if ($tmp = censor_words($name)) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_ACTIVITY_TITLE_BANNED_') . $tmp));
            }
            //后台验证文章内容
            if (!I('post.content')) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_ACT_CONTENT_NOT_EMPTY')));
            }

            if (!I('post.op_ticket')) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_ACT_TICKET_NOT_EMPTY')));
            }

            //***************存储活动表数据******************
            list($_not_allow_publish, $data_activity) = $this->_add_activity($activity_guid);
            $data_activity['guid'] = $activity_guid;

            // ************** 增加 承办机构 **************
            $undertakers = I('post.op_undertaker');
            $actLogic = D('Act', 'Logic');
            if (!empty($undertakers)) {
                $actLogic->save_undertaker($undertakers, $activity_guid, $auth['guid']);
            }

            // ************** 增加 活动流程 **************
            $flow = I('post.op_flow');
            if (!empty($flow)) {
                $actLogic->save_flow($flow, $activity_guid);
            }

            // ************** 增加 票务 **************
            $tickets = I('post.op_ticket');
            if (!empty($tickets)) {
                $actLogic->save_ticket($tickets, $activity_guid, $data_activity, $user_guid);
            }

            //************** 创建表名表单 *************
            $items = I('post.items');
            if (!empty($items)) { //其
                $actLogic->save_form($items, $activity_guid);
            }
            $status_post = I('post.status_post');
            if ($_not_allow_publish) {
                $this->ajax_return(array('status' => C('ajax_success'), 'msg' => L('_ACT_SAVE_NOT_PUB_'), 'status_post' => $status_post, 'url' => U('Home/Act/manage/', array('guid' => $activity_guid)), 'preview_url' => U('Home/Act/mpreview', array('guid' => $activity_guid))));
            } else {
                $this->ajax_return(array('status' => C('ajax_success'), 'status_post' => $status_post, 'url' => U('Home/Act/manage', array('guid' => $activity_guid)), 'preview_url' => U('Home/Act/mpreview', array('guid' => $activity_guid,'type'=>1))));
            }
        }
        $all_info = $logic->get_act_info($activity_guid, $user_guid);
        list($activity, $tickets, $build_info, $option_info, $flows, $undertakers) = $all_info;
        if (!$activity) {
            $this->error(L('_ACT_NOT_EXIST_'));
        } elseif ($activity['is_del']) {
            $this->error(L('_ACT_NOT_EXIST_'));
        } elseif (!$activity['status'] && $activity['start_time'] < time()) {
//
//            //未发布
//            if ($activity['status'] != C('act.unpublished')) {
//                M('Activity')->where(array('guid' => $activity['guid']))->save(array('status' => C('act.closed')));
//            } else {
//                M('Activity')->where(array('guid' => $activity['guid']))->save(array('status' => C('act.going_on')));
//            }
//
//
//            $this->error(meetelf_lang('_ACT_STATUS_.' . C('act.going_on')));
        } elseif ($activity['status'] > 0) {
            $this->error(meetelf_lang('_ACT_STATUS_.' . $activity['status']));
        }

        $subjects = M('ActivitySubject')->select();

        $organizers = M('OrganizerInfo')->where(array('user_guid' => $user_guid, 'status' => C('default_ok_status')))->select();
        $area = D('Area')->find_all('deep=1', 'id, name');
        if ($activity['areaid_1']) {
            $area_2 = D('Area')->find_all('parent_id=' . $activity['areaid_1'], 'id, name');
        }
        $label = M('ActivitySubject')->select();
        $where = array(
            'status' => 1,
            '_string' => "user_guid = '{$auth['guid']}' OR type = 1",
        );
        $partner = M('UserPartnerCategory')->where($where)->select();
        $build_info = array_columns($build_info, 'is_required', 'ym_type');
        // var_dump($build_info);die();
        $this->assign('labels', $label);
        $this->assign('build_info', $build_info);
        $this->assign('flows', $flows);
        $this->assign('act', $activity);
        $this->assign('partners', $partner);
        $this->assign('subjects', array_columns($subjects, 'name', 'guid'));
        $this->assign('tickets', $tickets);
        $this->assign('undertakers', $undertakers);
        $this->assign('organizer', $organizers);
        $this->assign('action', L('_EDIT_'));
        $this->assign('area', $area);
        $this->assign('area_2', $area_2);
        $this->show('add');
    }

    /**
     * 添加活动
     *
     * @access public
     * @param  void
     * @return void
     * @author wangleiming
     **/


    public function add()
    {
        $this->title = L('_CREATE_ACT_');
        $this->css[] = 'meetelf/css/create-activities.css';
        $this->css[] = 'meetelf/css/bootstrap-combobox.css';
        $this->css[] = 'meetelf/css/home.registration.load-css.css';
        //判断创建数是否超过配置
        if ($this->check_activity_num()) {
            $this->error(L('_ACT_CREATE_OVER_'));
        }

        $this->main = '/Public/meetelf/home/js/home.act.add.js';
        $auth = $this->get_auth_session();
        $user_guid = $auth['guid'];
        // 发布数量是否超出限制
        $_not_allow_publish = false;
        // 获取一级地区
        $area = D('Area')->find_all('deep=1', 'id, name');
        if (IS_POST) {
            $this->ajax_request_limit('act::ajax_add_activity', 1, 5);
            $name = I('post.title');
            if ($tmp = censor_words($name)) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_ACTIVITY_TITLE_BANNED_') . $tmp));
            }
            //后台验证文章内容
            if (!I('post.content')) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_ACT_CONTENT_NOT_EMPTY')));
            }

            if (!I('post.op_ticket')) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_ACT_TICKET_NOT_EMPTY')));
            }
            if(!I('post.op_undertaker')){
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_ACT_UNDERTAKER_NOT_EMPTY')));
            }

            M()->startTrans();
            //***************存储活动表数据******************
            list($_not_allow_publish, $data_activity) = $this->_add_activity();

            // ************** 增加 承办机构 **************
            $undertakers = I('post.op_undertaker');
            $actLogic = D('Act', 'Logic');
            if (!empty($undertakers)) {
                $m1 = $actLogic->save_undertaker($undertakers, $data_activity['guid'], $auth['guid']);
            }

            // ************** 增加 活动流程 **************
            $flow = I('post.op_flow');
            if (!empty($flow)) {
                $m2 = true;
                $m2 = $actLogic->save_flow($flow, $data_activity['guid']);
            }

            // ************** 增加 票务 **************
            $tickets = I('post.op_ticket');
            if (!empty($tickets)) {
                if (!empty($tickets['new'])) {
                    $m3 = $actLogic->save_ticket($tickets, $data_activity['guid'], $data_activity, $user_guid);
                }
            }

            //************** 创建表名表单 *************
            $items = I('post.items');
            if (!empty($items)) { //其
                $m4 = $actLogic->save_form($items, $data_activity['guid']);
            }

            if($_not_allow_publish && $m1 && $m3 && $m4 && $m2){
                //提交事务
                M()->commit();
            }else{
                //回滚事务
                M()->rollback();
            }

            $status_post = I('post.status_post');
            $return = array(
                'status' => C('ajax_success'),
                'status_post' => $status_post,
                'url' => U('Home/Act/manage', array('guid' => $data_activity['guid'])),
                'preview_url' => U('Home/Act/mpreview', array('guid' => $data_activity['guid'],'type'=>1))
            );
            if ($_not_allow_publish) {
                $return['msg'] = L('_ACT_SAVE_NOT_PUB_');
            }
            $this->ajax_return($return);
        }
        $where = array(
            'status' => 1,
            '_string' => "user_guid = '{$auth['guid']}' OR type = 1",
        );
        $partner = M('UserPartnerCategory')->where($where)->select();
        $label = M('ActivitySubject')->select();

        //   RTH     结算收费比例提示
        $charge = D('FinancingDiscount')->where(array('status' => 1,'is_del' => 0))->find()['ratio'];
        $this->assign('charge', $charge);
        //   RTH
        $this->assign('labels', $label);
        $this->assign('partners', $partner);
        $this->assign('organizer', get_organizer_info($auth['guid']));
        $this->assign('area', $area);
        $this->assign('action', L('_CREATE_'));
        $this->show();
    }

    public function mobile_preview()
    {
        $this->css[] = 'meetelf/css/activity_preview.css';
        $this->title = L('_MOBILE_PREVIEW_');
        $aid = I('get.aid');
        $type = I('get.type');
        $info = D('Activity')->find_one(array('guid' => $aid));
        session('signup_user:aguid',$aid);
        $this->assign('type', $type);
        $this->assign('info', $info);
        $this->show();
    }

    /**
     * 显示页面
     * CT 2015.09.18 10:00 by manonloki
     * UT 2015.09.23 17:45 by manonloki ActivityMange -> Act
     */
    public function act_manage()
    {
        //设置母版页
        layout('layout_new');


        //获取参数
        $aid = I('get.aguid');
        $owner_user_guid = $this->get_auth_session('guid');

        //检查参数
        if (empty($aid)) {
            $this->_empty();
        }

        //获取页面需要绑定的数据
        $activity_data = D('Activity')
            ->alias('a')
            ->where(array(
                'guid' => $aid
            ))
            ->field(array(
                'a.id' => 'id',//活动主键用于生成路径
                'a.guid' => 'guid',//活动唯一标识
                'a.name' => 'activity_name',//活动名
                'a.status' => 'status',//活动状态
                'a.is_public'=>'is_public',//活动是否公开
                'a.is_verify' => 'activity_is_verify',//审核状态
                'a.start_time' => 'start_time',//活动开始时间
                'a.end_time' => 'end_time',//活动结束时间
                'a.poster' => 'activity_poster',//活动宣传图
                'a.url' => 'activity_url',//活动地址
                'a.is_del' => 'activity_is_del',//活动是否被删除
                'a.user_guid' => 'owner_user_guid',//活动拥有者guid
                'a.published_at'=>'published_at',//活动发布时间
            ))
            ->find();

        //适配
        $activity_data['activity_id']=$activity_data['id'];
        $activity_data['activity_guid']=$activity_data['guid'];
        $activity_data['activity_status']=$activity_data['status'];
        $activity_data['activity_is_verify']=$activity_data['activity_is_verify'];
        $activity_data['activity_is_public']=$activity_data['is_public'];
        $activity_data['activity_start_time']=$activity_data['start_time'];
        $activity_data['activity_end_time']=$activity_data['end_time'];

        if($activity_data['activity_is_verify'] == 0 && $activity_data['activity_status'] != 0){
            $activity_data['activity_status'] = 4;
        } else if($activity_data['activity_is_verify'] != 1 && $activity_data['activity_status'] == 0){
            $activity_data['activity_status'] = 0;
        }
//        else if($activity_data['activity_is_verify'] == 1){
//            $activity_data['activity_status'] = 5;
//        }
        else if($activity_data['activity_is_verify'] == 3){
            $activity_data['activity_status'] = 6;
        }

        //检查结果
        if (empty($activity_data)) {
            $this->_empty();
            die();
        } else if ($activity_data['activity_is_del'] == '1') {
            //重定向到活动列表
            $this->redirect(U('Home/User/activity'));
            die();
        }
        //检查是否为活动发布者
        if ($activity_data['owner_user_guid'] !== $owner_user_guid) {
            $this->error(meetelf_lang("_ILLEGAL_OPERATION_"));
            die();
        }


        //格式化结果
        $activity_data['activity_poster'] = get_image_path($activity_data['activity_poster']);

        //获取活动状态
        if (intval($activity_data['activity_status']) == 0) {
            $activity_data['activity_real_status'] = meetelf_lang("k__activity.is_verify." . $activity_data['activity_is_verify']);
        } else {
            $activity_data['activity_real_status'] = meetelf_lang("k__activity.status." . $activity_data['activity_status']);
        }
        $activity_data['activity_status_string'] = meetelf_lang("k__activity.status." . $activity_data['activity_status']);
        //获取活动持续时间
        if (empty($activity_data['activity_start_time']) || empty($activity_data['activity_end_time'])) {
            $activity_data['time_of_duration'] = meetelf_lang('_TIME_ALONG_');
        } else {
            $timestampDiff = floatval($activity_data['activity_end_time']) - floatval($activity_data['activity_start_time']);
            $activity_data['time_of_duration'] = ceil($timestampDiff / (24 * 60 * 60));//单位  天
        }

        $activity_data['activity_time_string'] = weekday(array(
            $activity_data['activity_start_time'],
            $activity_data['activity_end_time']
        ));


        //创建活动地址 Mobile的
        $activity_data['mobile_activity_url'] = U("Home/Event/" . event_id_encode($activity_data['activity_id']), null, false, true, false);


        //传递模板参数
        $this->assign('activity', $activity_data);
        $this->assign('act', array(
            'guid' => $aid,
            'status'=> $activity_data['status'],
            'activity_is_verify'=> $activity_data['activity_is_verify'],
            'name'=> $activity_data['activity_name']
        ));

        if($activity_data['activity_status']==0){
            $this->assign('needEdit',true);
        }
        $this->assign('aguid',$activity_data['activity_guid']);

        //设置数据源
        $this->_manage_order_data();
        $this->_manage_ticket_data();


        //附加JS/CSS/TITILE
        $this->title = meetelf_lang('_MANAGE_TITLE_');
        //样式列表
        $this->css[] = 'meetelf/home/css/home.create-activities.css';
        $this->css[] = 'meetelf/home/css/home.release.css';
        $this->css[] = 'meetelf/home/css/home.activity_manage.css';
        $this->main  = "/Public/meetelf/home/js/build/act.act_manage.js";

        $this->show();

    }


    /**
     * 生成活动URL二维码，连接中不带TOKEN，APP扫描时需要加token功能
     * @param $activity_info 活动信息
     * CT: 2015-02-05 09:55 BY YLX
     */
    public function qrcode()
    {
        $aid = I('get.aid');
        $url = I('get.url');
        $type = I('get.type','');
        //获取页面需要绑定的数据
        $activity_data = D('Activity')
            ->alias('a')
            ->where(array(
                'guid' => $aid
            ))
            ->field(array(
                'a.id' => 'activity_id',//活动主键用于生成路径
                'a.user_guid' => 'owner_user_guid',//活动拥有者guid
                'a.status' => 'activity_status',//活动状态
            ))
            ->find();

        if ($activity_data['activity_status'] == '0') {

//            $url = U('Mobile/Activity/preview', array('aid' => $aid, 'app'=>1), true, true, false);
            $qr_name = $aid . '_preview.png';
        } else {
//            $url = U('Mobile/Activity/view', array('aid' => $aid, 'oid' => $activity_data['org_guid']), true, true, false); //'http://3g.yunmai365.com/activity/view/aid/'.$aid.'/oid/'.$org_guid;
            $qr_name = $aid . '.png';
        }

        if ($url == '1') {
            $qr_path = '/url/qrcode/activity/particulars/' . $activity_data['owner_user_guid'] . '';//活动详情二维码
            $qrcode_url = U('Mobile/Activity/preview', array('guid' => $aid, 'app' => 1), true, true);

            if($type==='d'){
                echo qrcode($qrcode_url, UPLOAD_PATH . $qr_path, $qr_name,false,'5','L',2,true,'application/octet-stream');
            }else{
                echo qrcode($qrcode_url, UPLOAD_PATH . $qr_path, $qr_name);
            }

            die();
        } else {
            //创建活动地址 Mobile的
            $activity_data['mobile_activity_url'] = U("Home/Event/" . event_id_encode($activity_data['activity_id']), null, false, true, false);
            $qr_path = '/url/qrcode/activity/signup/' . $activity_data['owner_user_guid'] . '';

            $data['qrcode_url'] = $qr_path . '/' . $qr_name;
            D('Activity')->where(array('guid' => $aid))->data($data)->save();

            if($type==='d'){
                echo qrcode($activity_data['mobile_activity_url'], UPLOAD_PATH . $qr_path, $qr_name,false,'5','L',2,true,'application/octet-stream');
            }else{
                echo qrcode($activity_data['mobile_activity_url'], UPLOAD_PATH . $qr_path, $qr_name);
            }
            die();
        }

    }

    /**
     * 获取票据数据
     * CT 2015.09.20 17:10 by manonloki
     * UT 2015.09.21 14:55 by manonloki
     * UT 2015.09.23 17:45 BY MANONLOKI ActivityMange -> Act
     */
    private function _manage_ticket_data()
    {
            //获取参数
            $aid = I('get.aguid');


            //获取总票数
            $ticket_all_count = D('ActivityAttrTicket')
                ->alias('aat')
                ->where(array(
                    'aat.activity_guid' => $aid,
                    'aat.is_del' => '0'
                ))
                ->field(
                    array(
                        'sum(aat.num)' => 'total_ticket'
                    )
                )
                ->find()['total_ticket'];

            $ticket_selled_count = D('ActivityUserTicket')
                ->alias('aut')
                ->where(array(
                    'aut.activity_guid' => $aid,
                    'aut.is_del'=>0,
                ))
                ->count();
            $ticket_fixed_count = D('Order')
                ->alias('o')
                ->where(array(
                    'o.target_guid' => $aid,
                    'o.status' => array('NOTIN', '3,9'),
                    'aat.num' => '0'
                ))
                ->field(array(
                    'sum(o.quantity)' => 'fixed_selled'
                ))
                ->join('ym_goods g ON g.guid=o.goods_guid')
                ->join('ym_activity_attr_ticket aat on aat.guid=g.ticket_guid')
                ->find()['fixed_selled'];

            //获取待审核票数
            $ticket_unissue = D('Order')
                ->alias('o')
                ->join('ym_goods g ON g.guid=o.goods_guid')
                ->join('ym_activity_attr_ticket aat ON aat.guid=g.ticket_guid')
                ->where(array(
                    'o.target_guid' => $aid,
                    'o.status' => '6',//待审核
                ))
                ->field(array(
                    'o.title' => 'ticket_name',
                    'count(o.title)' => 'ticket_count',
                ))
                ->group('o.goods_guid')
                ->select();

        $this->assign('ticket_data',array(
            'total' => intval($ticket_all_count) + intval($ticket_fixed_count),
            'selled' => intval($ticket_selled_count),
            'unissue' => $ticket_unissue
        ));
    }

    /**
     * 获取订单数据
     * CT 2015.09.20 19:00 by manonloki
     * UT 2015.09.23 17:45 BY MANONLOKI ActivityMange -> Act
     */
    private function  _manage_order_data()
    {
            //获取参数
            $aid = I('get.aguid');

            $order_info = D('Order')
                ->alias('o')
                ->where(array(
                    'o.target_guid' => $aid
                ))
                ->field(array(
                    'o.guid' => 'order_guid',
                    'o.order_id' => 'order_number',
                    'o.title' => 'ticket_name',
                    'o.created_at' => 'order_create_time',
                    'o.finished_time' => 'order_finished_time',
                    'o.total_price' => 'ticket_price',
                    'o.buyer_name' => 'order_buyer_name',
                    'o.status' => 'order_status',
                ))
                ->order(
                    'o.created_at desc'
                )
                ->limit(5)
                ->page(1)
                ->select();

            foreach ($order_info as &$v) {
                $v['order_status_string'] = meetelf_lang('k__order.status.' . $v['order_status']);
                if (floatval($v['ticket_price'] == 0)) {
                    $v['ticket_price_string'] = meetelf_lang('_NO_MONEY_');
                } else {
                    $v['ticket_price_string'] = $v['ticket_price'];
                }

                $v['order_ordertime'] = empty($v['order_finished_time']) ? '' : date('Y/m/d H:i', $v['order_finished_time']);
            }

        $this->assign("datasource", $order_info);
    }

    /**
     * 取消发布
     * CT 2015.10.12 17:20 by manonloki
     */
    public function  ajax_manage_cancel_activity()
    {
        layout(false);

        //获取参数
        $aid = I('post.aid');

        if (empty($aid)) {
            $this->ajaxReturn(array(
                'status' => C('ajax_failed'),
            ), 'json');
        }

        $model_activity = D("Activity");
        $model_activity_history = M("ActivityHistory");

        //获取历史数据
        $activity = $model_activity
            ->where(array(
                'guid' => $aid
            ))
            ->find();
        //复制到历史数据表中
        //去掉ID
        unset($activity['id']);
        //序列化历史Data
        $history_data = array(
            'guid' => $activity['guid'],
            'content' => json_encode($activity),
            'created_at' => time()
        );
        //添加到记录表
        $model_activity_history->data($history_data)->add();

        //更新数据
        $update_result = D("Activity")
            ->where(array(
                'guid' => $aid
            ))
            ->save(array(
                'status' => '0',
                'published_at' => '0'
            ));

        if ($update_result === false) {
            $this->ajaxReturn(array(
                'status' => C('ajax_failed'),
            ), 'json');
        } else {
            $this->ajaxReturn(array(
                'status' => C('ajax_success'),
            ), 'json');
        }
    }

    /**
     * 关闭活动
     * CT 2015.09.21 09:55 by manonloki
     * UT 2015.09.23 17:45 BY MANONLOKI ActivityMange -> Act
     */
    public function ajax_manage_close_activity()
    {
        //关闭母版页
        layout(false);
        //获取参数
        $aid = I('post.aid');

        if (empty($aid)) {
            $this->ajaxReturn(array(
                'status' => C('ajax_failed'),
            ), 'json');
        }

        //更新数据
        $update_result = D("Activity")
            ->where(array(
                'guid' => $aid
            ))
            ->save(array(
                'status' => '3'
            ));

        if ($update_result === false) {
            $this->ajaxReturn(array(
                'status' => C('ajax_failed'),
            ), 'json');
        } else {
            $this->ajaxReturn(array(
                'status' => C('ajax_success'),
            ), 'json');
        }


    }

    /**
     * 删除活动
     * CT 2015.09.21 10:00 by manonloki
     * UT 2015.09.23 17:45 BY MANONLOKI ActivityMange -> Act
     */
    public function  ajax_manage_del_activity()
    {
        //关闭母版页
        layout(false);
        //获取参数
        $aid = I('post.aid');

        if (empty($aid)) {
            $this->ajaxReturn(array(
                'status' => C('ajax_failed'),
            ), 'json');
        }

        //更新数据
        $update_result = D("Activity")
            ->where(array(
                'guid' => $aid
            ))
            ->save(array(
                'is_del' => '1'
            ));

        if ($update_result === false) {
            $this->ajaxReturn(array(
                'status' => C('ajax_failed'),
            ), 'json');
        } else {
            $this->ajaxReturn(array(
                'status' => C('ajax_success'),
            ), 'json');
        }
    }

    public function ajax_censor_words(){
        if(IS_AJAX){
           $words = I('post.words');   
           if(!$words){
               $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => C("_PARAM_ERROR_"))); 
           }
           if($tmp = censor_words($words)) {
               $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_ACTIVITY_TITLE_BANNED_') . $tmp));
           }
           $this->ajax_return(array('status' => C('ajax_success')));
        } 
        exit();
    }
}
