<?php

namespace Home\Controller;

use Think\Controller;
use Controls\Control\PagerControl;
use Controls\Model\PagerControlModel;

class OrderController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function mine_orders()
    {
        //获取基础数据
        $pageSize = I('post.ps', 10);//每页数据条目
        $page = intval(I('post.p', 1));//当前页码 公用
        $status = I('post.s','');//状态
        $query = I('post.q','');//查询条件


        //获取数据源
        $userID = $this->kookeg_auth_data('guid');//从Session中获取当前用户的登录ID
        //Create Models
        $model_order = M('Order');
        $model_activity_userinfo = M('ActivityUserinfo');
        $model_activity_user_ticket = M('ActivityUserTicket');
        $model_activity=M('Activity');

        $condation = array();
        $condation['o.user_guid'] = $userID;//判断UserID
        $condation['o.discount_type'] = array('NEQ', 3);
        if ($query !== '') {
            $condation['o.activity_name'] = array('LIKE', '%' . $query . '%');//根据活动名字进行查询
        }


        //获取符合条件的订单信息总数
        $order_all_count = $model_order
            ->alias('o')
            ->where($condation)
            ->count();

        //已完成订单数
        $condation['o.status'] = array('IN', '1');
        $order_success_count = $model_order
            ->alias('o')
            ->where($condation)
            ->count();


        //未完成订单数
        $condation['o.status'] = array('IN', '0');
        $order_unsuccess_count = $model_order
            ->alias('o')
            ->where($condation)
            ->count();
        //已取消订单数
        $condation['o.status'] = array('IN', '3,9');
        $order_cancelled_count = $model_order
            ->alias('o')
            ->where($condation)
            ->count();


        //判断是否需要状态
        if ($status==='') {
            unset($condation['o.status']);
        } else {
            //延迟到数据查询完毕再进行状态条件的附加 用于计算分页
            $condation['o.status'] = array('IN', $status);//判断状态
        }
        $order_page_count = $model_order
            ->alias('o')
            ->where($condation)
            ->count();


        //页面数据获取  数据源 活动(activity) 报名(activity_user_ticket)
        //第一步 获取订单信息 （主入口)
        $orders = $model_order
            ->alias('o')
            ->order('o.created_at desc')//创建时间倒序
            ->limit($pageSize)
            ->page($page)
            ->where($condation)
            ->field(array(
                'o.guid' => 'order_guid',//唯一标识
                'o.order_id' => 'order_number',//订单编号
                'o.status' => 'order_status',//订单状态
                'o.total_price' => 'order_price',//订单付出的款项
                'o.buyer_guid' => 'buyer_guid',//购买者唯一标识
                'o.buyer_name' => 'order_buyer_name',//购买者名称
                'o.title'=>'user_ticket_name',//票名
                'o.target_guid'=>'activity_guid',//活动GUID
            ))
            ->select();

        if (!empty($orders)) {


            //获取其它订单需要的GUID
            $activity_guid_list = kookeg_array_column($orders, 'activity_guid');
            $buyer_guid_list = kookeg_array_column($orders, 'buyer_guid');

            $activities=$model_activity
                ->alias('a')
                ->where(array(
                    'a.guid'=>array('IN',$activity_guid_list)
                ))
                ->field(array(
                    'a.id'=>'activity_id',//活动ID用于计算Event
                    'a.guid'=>'activity_guid',//活动唯一标识
                    'a.name'=>'activity_name',//活动名
                    'a.status'=>'activity_status',//活动状态
                    'a.start_time'=>'activity_start_time',//活动开始时间
                    'a.end_time'=>'activity_end_time',//活动结束时间
                    'a.poster'=>'activity_poster',//活动宣传图片
                ))
                ->select();



            //获取buyer信息
            $buyers = $model_activity_userinfo
                ->alias('au')
                ->where(array('au.guid' => array('IN', $buyer_guid_list)))
                ->field(
                    array(
                        'au.guid' => 'buyer_guid', //购买者唯一标识
                        'au.real_name' => 'buyer_name',//购买者姓名
                        'au.mobile' => 'buyer_mobile',//购买者手机号
                    )
                )
                ->select();


            //获取票据信息
            $user_tickets = $model_activity_user_ticket
                ->alias('aut')
                ->where(array('aut.activity_guid' => array('IN', $activity_guid_list), 'aut.userinfo_guid' => array('IN', $buyer_guid_list)))
                ->field(
                    array(
                        'aut.userinfo_guid' => 'buyer_guid',//购票者唯一标识
                        'aut.guid' => 'user_ticket_guid',//购买者票据唯一标识
                        'aut.status' => 'user_ticket_status',//购买者票据状态
                        'aut.ticket_code' => 'user_ticket_code',//购买者的票号，不存在票号不给显示预览/下载电子票
                    )
                )
                ->select();

            $buyers_map = kookeg_array_column($buyers, null, 'buyer_guid');
            $user_tickets_map = kookeg_array_column($user_tickets, null, 'buyer_guid');
            $activity_map=kookeg_array_column($activities,null,'activity_guid');


            //拼接数据 并处理数据
            foreach ($orders as &$value) {

                //拼接数据
                $buyer = $buyers_map[$value['buyer_guid']];
                $user_ticket = $user_tickets_map[$value['buyer_guid']];
                $activity=$activity_map[$value['activity_guid']];


                if(!empty($activity)){
                    $value=array_merge($value,$activity);
                }

                if (!empty($buyer)) {
                    $value = array_merge($value, $buyer);
                }

                if (!empty($user_ticket)) {
                    $value = array_merge($value, $user_ticket);
                }

                if (empty($value['buyer_name'])) {
                    $value['buyer_name'] = $value['order_buyer_name'];
                }


                //处理数据
                $value['order_status_string'] = kookeg_lang('k__order.status.' . $value['order_status']);
                $value['activity_status_string'] = kookeg_lang('k__activity.status.' . $value['activity_status']);
                $value['activity_poster'] = get_image_path($value['activity_poster']);
                $value['activity_date'] = weekday(array($value['activity_start_time'], $value['activity_start_time']));

                //格式化金额
                $order_price=intval($value['order_price']);
                $value['order_price_string']=empty($order_price)?"免费":"￥".yuan_to_fen($order_price,false).'元';
            }
        }


        //处理分页
        $pager_model = new PagerControlModel($page, $order_page_count, $pageSize);
        $pager = new PagerControl($pager_model);

        //Tab标签的Title
        $tab_titles = array(
            'tab_allText' => kookeg_lang('_TAB_ALL_') . '(' . $order_all_count . ')',
            'tab_successText' => kookeg_lang('_TAB_SUCCESS_') . '(' . $order_success_count . ')',
            'tab_unsuccessText' => kookeg_lang('_TAB_UNSUCCESS_') . '(' . $order_unsuccess_count . ')',
            'tab_cancelledText' => kookeg_lang('_TAB_CANCELLED_') . '(' . $order_cancelled_count . ')'
        );


        //处理模板数据
        $this->assign('datasource', array(
            'pager' => $pager->fetch(),
            'orders' => $orders,
        ));
        $this->assign('tab_titles',$tab_titles);

        //GET请求
        if (IS_GET) {
            layout('layout_new');
            $this->title = kookeg_lang('_MINE_ORDERS_TITLE_');
            $this->css[] = 'meetelf/home/css/release.css';
            $this->main = '/Public/meetelf/home/js/build/order.mine_orders.js';
            $this->show();
        } else if (IS_POST) {
            layout(false);
            $data=array(
                'status' => empty($orders)?C('ajax_failed') :C('ajax_success'),
                'data' => array(
                    'content' => $this->fetch('Order:_mine_orders_item'),//对应订单内容
                    'tab_allText' => kookeg_lang('_TAB_ALL_') . '(' . $order_all_count . ')',
                    'tab_successText' => kookeg_lang('_TAB_SUCCESS_') . '(' . $order_success_count . ')',
                    'tab_unsuccessText' => kookeg_lang('_TAB_UNSUCCESS_') . '(' . $order_unsuccess_count . ')',
                    'tab_cancelledText' => kookeg_lang('_TAB_CANCELLED_') . '(' . $order_cancelled_count . ')'
                ));

            $this->ajaxResponse($data,'json');
        }


    }


    public function detail()
    {

        $order_number = I('get.on');//获取OrderNumber 对应数据库的OrderID

        $model_order = M("Order");
        $model_activity = M('Activity');
        $model_activity_userinfo = M('ActivityUserinfo');
        $model_activity_user_ticket = M('ActivityUserTicket');
        $model_activity_attr_undertaker = M('ActivityAttrUndertaker');
        $model_activity_userinfo_other = M("ActivityUserinfoOther");
        //获取订单
        $orders = $model_order
            ->alias('o')
            ->where(array(
                'o.order_id' => $order_number,
                'o.discount_type' => array('NEQ', 3)
            ))
            ->field(array(
                'o.order_id' => 'order_number',  //订单编号
                'o.finished_time' => 'order_finished_time',//订单完成时间
                'o.payment_time'=>'order_payment_time',//订单付款时间
                'o.created_at'=>'order_created_time',//订单创建时间
                'o.buyer_guid' => 'buyer_guid',//购买者唯一标识
                'o.target_guid' => 'activity_guid',//活动GUID
                'o.title'=>'user_ticket_name',//票名
                'o.status'=>'order_status',//活动状态
            ))
            ->select();


        if (!empty($orders)) {
            //获取依赖的外键
            $activity_guid_list = kookeg_array_column($orders, 'activity_guid');
            $buyer_guid_list = kookeg_array_column($orders, 'buyer_guid');

            //获取Buyer信息
            $buyers = $model_activity_userinfo
                ->alias('au')
                ->where(array(
                    'au.guid' => array('IN', $buyer_guid_list)
                ))
                ->field(array(
                    'au.guid' => 'buyer_guid', //购买者唯一标识
                    'au.real_name' => 'buyer_name',//购买者姓名
                    'au.email' => 'buyer_email',//购买者EMAIL
                    'au.mobile' => 'buyer_mobile',//购买者手机号
                ))
                ->select();
            //获取Buyer详细信息
            $buyer_infos = $model_activity_userinfo_other
                ->alias('auo')
                ->where(array(
                    'auo.activity_guid' => array('IN', $activity_guid_list),
                    'auo.userinfo_guid' => array('IN', $buyer_guid_list),
                    'is_del' => '0'
                ))
                ->field(array(
                    'auo.userinfo_guid' => 'buyer_guid',
                    'auo.key' => 'info_key',
                    'auo.value' => 'info_value'
                ))
                ->select();

            //获取活动信息
            $activities = $model_activity
                ->alias('a')
                ->where(array(
                    'a.guid' => array('IN', $activity_guid_list)
                ))
                ->field(array(
                    'a.id' => 'activity_id',//活动ID
                    'a.guid' => 'activity_guid', //活动唯一标识
                    'a.name' => 'activity_name',//活动名
                ))
                ->select();
            //获取主办方信息
            $undertakers = $model_activity_attr_undertaker
                ->alias('aat')
                ->where(array(
                    'aat.activity_guid' => array('IN', $activity_guid_list),
                    'type' => '1'
                ))
                ->field(array(
                    'aat.activity_guid' => 'activity_guid', //活动唯一标识
                    'aat.name' => 'undertaker_name',//主办方名称
                ))
                ->select();
            //获取票据信息
            $tickets = $model_activity_user_ticket
                ->alias('aut')
                ->where(array(
                    'aut.activity_guid' => array("IN", $activity_guid_list),
                    'aut.userinfo_guid' => array("IN", $buyer_guid_list),
                ))
                ->field(array(
                    'aut.activity_guid' => 'activity_guid',//活动唯一标识
                    'aut.status' => 'user_ticket_status',//票状态
                ))
                ->select();


            $buyer_map = kookeg_array_column($buyers, null, 'buyer_guid');
            $activity_map = kookeg_array_column($activities, null, 'activity_guid');
            $ticket_map = kookeg_array_column($tickets, null, 'activity_guid');
            $undertaker_map = array_to_map($undertakers, "activity_guid", "undertaker_name");
            $buyer_info_map = array_to_map($buyer_infos, 'buyer_guid', null);


            foreach ($orders as &$value) {
                $buyer = $buyer_map[$value['buyer_guid']];
                $activity = $activity_map[$value['activity_guid']];
                $undertaker = $undertaker_map[$value['activity_guid']];
                $ticket = $ticket_map[$value['activity_guid']];
                $buyer_info = $buyer_info_map[$value['buyer_guid']];

                if (!empty($buyer)) {
                    $value = array_merge($value, $buyer);
                }
                if (!empty($activity)) {
                    $value = array_merge($value, $activity);
                }
                if (!empty($ticket)) {
                    $value = array_merge($value, $ticket);
                }
                if (!empty($undertaker)) {
                    $value['activity_undertaker_name'] = implode(',', $undertaker);
                }
                if (!empty($buyer_info)) {
                    $value['activity_buyer_infos'] = $buyer_info;
                }

                //处理数据
                if($value['order_status']==3||$value['order_status']==9){
                    $value['user_ticket_status_string']='已取消';
                }else{
                    $value['user_ticket_status_string'] = array_key_exists("user_ticket_status", $value) ? kookeg_lang('k__activity_user_ticket.status.' . $value["user_ticket_status"]) : "未发票";//格式化票据状态
                }
                $value['order_status_string']=kookeg_lang('k__order.status.' . $value['order_status']);
                $value['order_finished_time_string'] = empty($value['order_payment_time']) ? date('Y-m-d H:i:s', $value['order_created_time']) : date('Y-m-d H:i:s', $value['order_payment_time']);//格式化订单创建时间
            }

        }


        //返回数据
        layout('layout_new');
        $this->css[] = "meetelf/home/css/release.css";//添加所需CSS
        $this->assign('datasource', array(
            'orderDetails' => $orders,
        ));

        $this->title = kookeg_lang("_DETAIL_TITLE_");

        $this->show();
    }

    public function review()
    {

        //获取参数
        $aid = I('get.aguid');
        $currentPage = intval(I('get.p', 1));//当前页码 默认1
        $pageSize = intval(I('get.ps', 10));//每页数据量 默认10
        $owner_user_guid = $this->kookeg_auth_data('guid');
        //检查参数
        if (empty($aid)) {
            $this->_empty();
        }

        //获取页面需要绑定的数据 (活动数据)
        $activity_data = D('Activity')
            ->alias('a')
            ->where(array(
                'guid' => $aid
            ))
            ->field(array(
                'a.guid' => 'activity_guid',//活动唯一标识
                'a.name' => 'activity_name',//活动名
                'a.status' => 'activity_status',//活动状态
                'a.is_verify' => 'activity_is_verify',//审核状态
                'a.start_time' => 'activity_start_time',//活动开始时间
                'a.end_time' => 'activity_end_time',//活动结束时间
                'a.is_del' => 'activity_is_del',//活动是否被删除
                'a.user_guid' => 'owner_user_guid',//活动所属人的GUID
            ))
            ->find();
        if($activity_data['activity_is_verify'] != 1){
            $this->error('活动未通过审核');
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
        //检查所属人
        //检查是否为活动发布者
        if ($activity_data['owner_user_guid'] !== $owner_user_guid) {
            $this->error(kookeg_lang("_ILLEGAL_OPERATION_"));
            die();
        }


        $activity_data['activity_status_string'] = kookeg_lang("k__activity.status." . $activity_data['activity_status']);

        $activity_data['activity_time_string'] = weekday(array(
            $activity_data['activity_start_time'],
            $activity_data['activity_end_time']
        ));

        //获取页面需要绑定的数据
        $order_all_count = D("Order")
            ->alias('o')
            ->where(array(
                'o.target_guid' => $aid,
                'o.discount_type' => array('NEQ', '3')
            ))
            ->count();
        $ticket_all_count = D('ActivityUserTicket')
            ->alias('aut')
            ->where(array(
                'aut.activity_guid' => $aid,
                'aut.is_del' => '0'
            ))
            ->count();
        $order_total_price = D('Order')
            ->alias('o')
            ->where(array(
                'o.target_guid' => $aid,
                'o.discount_type' => array('NEQ', 3),
                'o.status' => array('NOTIN',array(3,9))
            ))
            ->field(
                array('sum(o.total_price)' => 'total')
            )
            ->find()['total'];



        //获取订单数据源
        $condation = array();
        $condation['o.target_guid'] = $aid;
        $condation['o.discount_type'] = array('NEQ', '3');

        $orders = D("Order")
            ->alias('o')
            ->limit($pageSize)
            ->page($currentPage)
            ->order('order_status_sort desc,o.created_at desc')
            ->where($condation)
            ->field(array(
                'o.order_id' => 'order_number',//订单号
                'o.quantity' => 'order_quantity',//购买数量
                'o.total_price' => 'order_total_price',//总价
                'o.finished_time' => 'order_finished_time',//下单时间
                'o.buyer_guid' => 'buyer_guid',//下单者唯一标识
                'o.buyer_name' => 'order_buyer_name',//下单者名称
                'o.title' => 'user_ticket_name',//票名
                'o.status' => 'order_status',//订单状态
                'case o.status WHEN 6 THEN 1 ELSE 0 END ' => 'order_status_sort',//排序依据 6待审核为1 其它为0
            ))
            ->select();

        //默认不需要审核
        $needAduit = false;

        foreach ($orders as &$value) {
            $value['order_finished_time_string'] = empty($value['order_finished_time']) ? '' : date('Y/m/d h:i', $value['order_finished_time']);//票完成时间
            $value['order_status_string'] = kookeg_lang('k__order.status.' . $value['order_status']);//状态中文名
            $value['order_total_price_string'] = floatval($value['order_total_price']) == 0 ? kookeg_lang('_NO_MONEY_') : ($value['order_total_price']/100);//价格
            $value['buyer_type_string'] = kookeg_lang('k__activity_userinfo.type.' . $value['buyer_type']);//来源中文
            $value['buyer_name'] = empty($value['buyer_name']) ? $value['order_buyer_name'] : $value['buyer_name'];//修正下单人信息

            //判断是否存在待审核状态
            if ($value['order_status_sort'] == 1) {
                $needAduit = true;
            }

        }

        //分页
        $pager_model = new PagerControlModel($currentPage, $order_all_count, $pageSize);
        $pager = new PagerControl($pager_model, PagerControl::$Enum_First_Prev_Next_Last);

        //传递模板参数
        $this->assign('datasource', array(
            'activity' => $activity_data,
            'order' => array(
                'order_count' => $order_all_count,
                'ticket_count' => $ticket_all_count,
                'total_price' => $order_total_price/100
            ),
        ));
        $this->assign('act', array(
            'guid' => $aid
        ));

        $this->assign('orders', $orders);
        $this->assign('pager',$pager->fetch());


        layout('layout_new');
        $this->title = kookeg_lang('_REVIEW_TITLE_');
        $this->css[] = 'meetelf/home/css/home.create-activities.css';
        $this->main  = "/Public/meetelf/home/js/build/order.review.js";
        $this->show();

    }

    public function ajax_review_order_data()
    {
        if (IS_POST) {
            layout(false);

            $aid = I('post.aid');//活动ID
            $currentPage = intval(I('post.p', 1));//当前页码 默认1
            $pageSize = intval(I('post.ps', 10));//每页数据量 默认10
            $query = I('post.q'); //查询条件 电话/活动名
            $status = I('post.s');//状态


            //获取订单总数
            $condation = array();
            $condation['o.target_guid'] = $aid;
            $condation['o.discount_type'] = array('NEQ', '3');
            if (!empty($query)) {
                $condation['buyer_name']=$query;
            }

            if ($status !== '') {
                $condation['o.status'] = array(
                    'IN',
                    $status
                );
            }

            $model_order = M('order');

            //获取总条目
            $order_all_count = $model_order
                ->alias('o')
                ->where($condation)
                ->count();

            $orders = $model_order
                ->alias('o')
                ->limit($pageSize)
                ->page($currentPage)
                ->order('order_status_sort desc,o.created_at desc')
                ->where($condation)
                ->field(array(
                    'o.order_id' => 'order_number',//订单号
                    'o.quantity' => 'order_quantity',//购买数量
                    'o.total_price' => 'order_total_price',//总价
                    'o.finished_time' => 'order_finished_time',//下单时间
                    'o.buyer_guid' => 'buyer_guid',//下单者唯一标识
                    'o.buyer_name' => 'order_buyer_name',//下单者名称
                    'o.title' => 'user_ticket_name',//票名
                    'o.status' => 'order_status',//订单状态
                    'case o.status WHEN 6 THEN 1 ELSE 0 END ' => 'order_status_sort',//排序依据 6待审核为1 其它为0
                ))
                ->select();


            //默认不需要审核
            $needAduit = false;


            foreach ($orders as &$value) {
                $value['order_finished_time_string'] = empty($value['order_finished_time']) ? '' : date('Y/m/d h:i', $value['order_finished_time']);//票完成时间
                $value['order_status_string'] = kookeg_lang('k__order.status.' . $value['order_status']);//状态中文名
                $value['order_total_price_string'] = floatval($value['order_total_price']) == 0 ? kookeg_lang('_NO_MONEY_') : $value['order_total_price'];//价格
                $value['buyer_type_string'] = kookeg_lang('k__activity_userinfo.type.' . $value['buyer_type']);//来源中文
                $value['buyer_name'] = empty($value['buyer_name']) ? $value['order_buyer_name'] : $value['buyer_name'];//修正下单人信息

                //判断是否存在待审核状态
                if ($value['order_status_sort'] == 1) {
                    $needAduit = true;
                }

            }


            //分页
            $pager_model = new PagerControlModel($currentPage, $order_all_count, $pageSize);
            $pager = new PagerControl($pager_model, PagerControl::$Enum_First_Prev_Next_Last);


            $this->assign('orders', $orders);
            $this->ajaxResponse(array(
                'status' => C('ajax_success'),
                'data' => array(
                    'pager' => $pager->fetch(),
                    'orders' => $orders,
                    'needAduit' => $needAduit,
                    'html' => $this->fetch('Order:_review_item')
                )
            ), 'json');
        } else {
            echo "none";
        }
    }

    public function  ajax_review_audit()
    {
        if (IS_POST) {
            $order_number_list = I('post.order_number_list', "[]");//订单编号集合
            $order_verify_type = I('post.verify_type');//订单的审核类型
            $order_verify_reason = I('post.verify_reason');//订单拒绝理由


            if (empty($order_number_list) || empty($order_number_list)) {
                $this->ajaxResponse(array(
                    'status' => C('ajax_failed')
                ));
                die();
            }

            $updateResult = D('Order')
                ->where(array(
                    'order_id' => array('IN', $order_number_list)

                ))
                ->data(array(
                    'status' => $order_verify_type,
                    'verify_reason' => $order_verify_reason
                ))
                ->save();


            if ($updateResult == false) {
                $this->ajaxResponse(array(
                    'status' => C('ajax_failed')
                ));
            } else {
                $this->ajaxResponse(array(
                    'status' => C('ajax_success')
                ));
            }

        } else {
            echo "none";
        }
    }
}
