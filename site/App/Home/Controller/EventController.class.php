<?php
/**
 * Created by PhpStorm.
 * User: qqyy
 * Date: 2015/9/18
 * Time: 11:08
 */

namespace Home\Controller;

use Controls\Control\PagerControl;
use Controls\Model\PagerControlModel;
use Home\Controller\BaseController;

class EventController extends BaseController
{
    public function __construct()
    {
        parent::__construct(false);
        layout('layout_new');
    }


    public function  index()
    {
        //0获得数据
        $this->main = '/Public/meetelf/home/js/build/home.event.js';
        $this->css[] = 'meetelf/home/css/home.event.css';
        if (!IS_GET) {
            $this->error('非法类型请求');
            exit;
        }//over

        $session_auth = $this->get_auth_session();

        $actGuid = I('get.g', null);
        //测试数据
        //$actGuid = 'B073397D96E4BE7BFC3DC50600C4E07B';

        if (!$actGuid) {
            $this->error('参数为空');
            exit;
        }//over

        //构造查询条件
        $data = array();
        $data['guid'] = $actGuid;
        //$data['is_del']='false';

        $dal = D('Activity');
        $model_act = $dal->where($data)->find();
        if (!$model_act) {
            $this->error('没有找到活动');
            exit;
        }//over

        if (intval($model_act['is_del']) == 1) {
            $this->error('活动已被删除');
            exit;
        }

        //这里需要判断一下是否是自己的活动 如果是自己的活动不需要判断未公开和未发布
        $session_auth = $this->get_auth_session();
        if (!$session_auth) {
//            if (intval($model_act['is_public']) == 0) {
//                $this->error('活动未公开');
//                exit;
//            }
            if (intval($model_act['status']) == 0) {
                $this->error('活动未发布');
                exit;
            }
        } else { //登陆的情况

            $user_guid = $session_auth['guid'];
            if ($model_act['user_guid'] != $user_guid) { //如果不是自己的活动
//                if (intval($model_act['is_public']) == 0) {
//                    $this->error('活动未公开');
//                    exit;
//                }
                if (intval($model_act['status']) == 0) {
                    $this->error('活动未发布');
                    exit;
                }
            } else {

                $this->assign('is_owner', true);
            }
        }


        $_view_time = weekday(array(
            $model_act['start_time'],
            $model_act['end_time']
        ));
        //if ($model_act['end_time']-$model_act['start_time']<=86400){
        // $_view_time = weekday($model_act['start_time'],'Y年m月d日 星期{w}').' '.weekday($model_act['start_time'],'H:i').' ~ '.weekday($model_act['end_time'],'H:i');
        //}
        //else{
        //$_view_time = weekday($model_act['start_time'],'Y年m月d日 星期{w}').' '. weekday($model_act['start_time'], 'H:i') . ' ~ ' . weekday($model_act['end_time'],'Y年m月d日 星期{w}').' '. weekday($model_act['end_time'], 'H:i');
        //}

        //算时间差
        $startdate = time();
        $enddate = $model_act['start_time'];
        $date = 0;
        $hour = 0;
        $minute = 0;
        if ($model_act['start_time'] > $startdate) {

            $date = floor((intval($enddate) - intval($startdate)) / (24 * 60 * 60));
            $hour = floor((intval($enddate) - intval($startdate)) / (60 * 60) % 24);
            $minute = floor((intval($enddate) - intval($startdate)) / 60 % 60);
        }
        $model_view_surplus_time = array(
            'date' => $date,
            'hour' => $hour,
            'minute' => $minute
        );


//        //活动状态计算
//        $_view_status = 0;
//        if ($model_act['status'] == 2) {
//            $_view_status = 2;//以结束
//        } elseif ($model_act['status'] == 1) {
//            //活动中 有三种情况 一是报名中 一个是活动进行中  一个活动没开始报名
//            if (time() >= $model_act['start_time'] && time() <= $model_act['end_time']) {
//                $_view_status = 3; //活动开始
//            }
//            if (time() >= $model_act['start'] && time() <= $model_act['end']) {
//                $_view_status = 1;//报名开始
//            }
//        }

        //直接设置状态
        $_view_status = $model_act['status'];

        /*$_view_num_person = intval($model_act['num_person']);
        if ($_view_num_person==0) {
            $_view_num_person = "∞";
        }*/

        //根据票据获取状态
        $_signup_status = D('Act', 'Logic')->check_signup_time($model_act['guid'], $model_act['start_time'], $model_act['end_time']);

//        var_dump($_signup_status);
//        die;

        //返回给前台的
        $model_view = array();
        $model_view['name'] = $model_act['name'];
        $model_view['guid'] = $model_act['guid'];
        $model_view['image'] = get_image_path($model_act['poster'], 1);
        $model_view['address'] = $model_act['areaid_1_name'] . $model_act['areaid_2_name'] . $model_act['address'];
        $model_view['time'] = $_view_time;
        $model_view['content'] = $model_act['content'];
        $model_view['is_verify'] = $model_act['is_verify'];
        $model_view['surplus_time'] = $model_view_surplus_time;
        //$model_view['num'] = $_view_num_person;
        $model_view['status'] = $_view_status;
        $model_view['activity_status'] = $model_act['status'];
        $model_view['lat'] = $model_act['lat'];
        $model_view['lng'] = $model_act['lng'];
        $model_view['lng_lat'] = $model_act['lng'] . ',' . $model_act['lat'];
        $model_view['lat_lng'] = $model_act['lat'] . ',' . $model_act['lng'];
        $model_view['start_time'] = $model_act['start_time'];
        $model_view['show_front_list'] = $model_act['show_front_list'];
        $model_view['can_signup'] = $_signup_status['status'] ? '0' : '1';//可否签到
        $model_view['now_signup_type'] = $_signup_status['time_type'];//不能签到的类型


        //取得标签 这里以后要修改成多对多

        $dal = D('ActivitySubject');
        $model_list_subject = $dal
            ->where('guid = "' . $model_act['subject_guid'] . '" and is_del = 0')
            ->find();

        $model_view['tags'] = $model_list_subject['name'];


        //ym_activity_attr_ticket 活动票务
        $dal = D('ActivityAttrTicket');
        $model_list_ticket = $dal
            ->where('activity_guid = "' . $model_act['guid'] . '" and is_del = 0')
            ->select();
        $model_view_tickets = array();
        if ($model_list_ticket) {

            //ym_activity_user_ticket 购票表 求出该活动下所有有效票
            $dal = D('ActivityUserTicket');
            $model_list_user_ticker = $dal
                ->where('activity_guid = "' . $model_act['guid'] . '" and is_del = 0')
                ->order('created_at DESC')
                ->select();


            //循环票种列表

            foreach ($model_list_ticket as $m) {
                //求剩余表
                $_view_surplus = intval($m['num']);
                if ($_view_surplus == 0) {
                    $_view_surplus = "无限";//表示无限
                } elseif (!$model_list_user_ticker) {
                    //还没任何售票 $_view_surplus = intval($m['num']);
                } else {

                    $_view_surplus = $_view_surplus - $this->surplusTicket($model_list_user_ticker, $m['guid']);
                    if ($_view_surplus == 0) {
                        $_view_surplus = "已售罄";
                    }
                }

                //票的有效时间

                $_view_end_time = intval($m['end_time']);
                $_view_start_time = intval($m['start_time']);


                if ($_view_end_time < time()) {
                    $_view_end_time = "已停售";
                } else {
                    //这里格式化时间
                    $_view_end_time = date("Y-m-d H:i", $_view_end_time);
                }
//                if($_view_start_time<time()){
//                    $_view_start_time='已开始';
//                }else{
                $_view_start_time = date("Y-m-d H:i", $_view_start_time);
//                }


                $_view_price = intval($m['price']);
                if ($_view_price == 0) {
                    $_view_price = "免费";
                } else {
                    $_view_price = "￥" . yuan_to_fen($_view_price, false) . '元';
                }

                //组织前台输出的票种列表
                $model_view_tickets[] = array(
                    'name' => $m['name'],
                    'price' => $_view_price,
                    'surplus' => $_view_surplus,
                    'end_time' => $_view_end_time,
                    'start_time' => $_view_start_time,
                    'desc' => $m['desc']
                );
            }

            //计算人数
            $_view_p_num = 0; //人数
            foreach ($model_list_ticket as $m) {
                $_view_t_num = intval($m['num']);
                if ($_view_t_num == 0) {
                    $_view_p_num = "∞";//表示无限
                    break;
                } else {
                    $_view_p_num += $_view_t_num;
                }
            }

            $model_view['num'] = $_view_p_num;


        } else {
            $this->error('活动没有设置有效票务');
            exit;
            //$model_list_ticket =  null;
        }//over

        //ym_activity_attr_flow 活动流程
        $dal = D('ActivityAttrFlow');
        $model_list_flow = $dal
            ->where('activity_guid = "' . $model_act['guid'] . '" and is_del = 0')
            ->order('start_time ')
            ->select();


        /*if(!$model_list_flow) {
           $this->error('没有设置会议流程');
            exit;
        }*/
        //U('');
        $model_view_flows = array();

        //
        $model_view_flows_time_interval = 0;

        //遍历所有
        foreach ($model_list_flow as $m) {
//            //计算月份差
//            $tmp_diff_month = intval(date('n', $m['end_time'])) - intval(date('n', $m['start_time']));
//            //计算天数差 绝对值
//            $tmp_diff_day = abs( intval(date('j', $m['end_time'])) - intval(date('j', $m['start_time'])));

            //格式化后的时间
            $_view_time = "";

//            if($tmp_diff_month>0||$tmp_diff_day>0){
//                //跨天了
//                //当天
//                $_view_time=weekday(array(
//                    $m['start_time'],
//                    $m['end_time']
//                ),'m月d日 H:i','<br>');
//            }else{
//                //当天
//                $_view_time=weekday(array(
//                    $m['start_time'],
//                    $m['end_time']
//                ),'m月d日 H:i');
//            }
//            $_view_time = weekday(array(
//                $m['start_time'],
//                $m['end_time']
//            ), 'm月d日 H:i');

            $_view_time = mtf_date_format(array(
                $m['start_time'],
                $m['end_time']
            ), array(
                'ymd' => 'm月d日',
                'his' => 'H:i'
            ));


            $model_view_flows[] = array(
                'name' => $m['title'],
                'time' => $_view_time
            );


        }


//        $_temp_prv_time = 0;
//        foreach ($model_list_flow as $m) {
//            //返回时间显示
//            $_view_time = "";
//            if ($_temp_prv_time == 0 || $m['end_time'] - $m['start_time'] > 86400 || $m['end_time'] - $_temp_prv_time > 86400) {
//                //$_view_time = weekday($m['start_time'],'Y年m月d日 星期{w}').' '.weekday($m['start_time'],'H:i').' ~ '.weekday($m['end_time'],'H:i');
//                $_view_time = weekday(array(
//                    $m['start_time'],
//                    $m['end_time']
//                ));
//            } else {
//                //$_view_time =  weekday($m['start_time'], 'H:i') . ' ~ ' . weekday($m['end_time'], 'H:i');
//                $_view_time = weekday(array(
//                    $m['start_time'],
//                    $m['end_time']
//                ), 'H:i');
//            }
//            //需要保留本条 第二条看看有咩有隔天
//            $_temp_prv_time = $m['start_time'];
//
//            $model_view_flows[] = array(
//                'name' => $m['title'],
//                'time' => $_view_time
//            );
//            $_temp_prv_time = $m['end_time'];//保留本次结束时间
//        }

        //ym_activity_attr_undertaker 承办机构
        $dal = D('ActivityAttrUndertaker');
        $model_list_taker = $dal
            ->where('activity_guid = "' . $model_act['guid'] . '" and is_del = 0')
            ->order('type')
            ->select();
//        if (!$model_list_taker) {
//            $this->error('必须要有主办方');
//            exit;
//        }//over

        $model_view_takers = array(
            'zb' => array(),
        );
        foreach ($model_list_taker as $m) {
            if ($m['type'] == 1) { //主办
                $model_view_takers['zb'][] = array(
                    'name' => $m['name'],
                    'type' => $m['type']
                );

            } else {
                foreach (explode(',', $m['name']) as $value) {
                    if ($value) {
                        $model_view_takers[$m['type']][] = $value;
                    }
                }
            }
        }

        //ym_activity_userinfo 用户报名表
        $dal = D('ActivityUserinfo');
//        $model_list_userinfo = $dal->alias('a')
//            ->join('ym_user_attr_info u on a.user_guid=u.user_guid')
//            ->field('a.*,u.photo')
//            ->where('a.activity_guid = "' . $model_act['guid'] . '" and a.is_del = 0')
//            ->order('a.created_at desc')
//            ->page(1, 5)
//            ->select();
        $model_list_userinfo=$dal->alias('a')
            ->field(
              array(
                  'a.user_guid'=>'user_guid',
                  'a.created_at'=>'created_at',
                  'a.real_name'=>'real_name',
              )
            )
            ->where(array(
                'a.activity_guid'=>$model_act['guid'],
                'a.is_del'=>'0'
            ))
            ->order('a.created_at desc')
            ->page(1,5)
            ->select();

        //用户表获取头像
        $user_guid_list=array_columns($model_list_userinfo,'user_guid',null);

        $model_list_photos=D('UserAttrInfo')->alias('uai')
            ->field(
                array(
                    'uai.user_guid',
                    'uai.photo'
                )
            )
            ->where(
                array(
                    'uai.user_guid'=>array('IN',$$user_guid_list)
                )
            )
            ->select();

        $model_photos_map=array_columns($model_list_photos,null,'user_guid');

        //组装数据
        foreach($model_list_userinfo as &$value){
            $model_photo=$model_photos_map[$value['user_guid']];

            if(!empty($model_photo)){
                $value = array_merge($value, $model_photo);
            }
        }



        $model_view_userinfo = array();
        foreach ($model_list_userinfo as $m) {
            $model_view_userinfo[] = array(
                'time' => date('Y/m/d H:i', $m['created_at']),
                'name' => $m['real_name'],
                'photo' => get_image_path($m['photo'], 1)//修复头像地址
            );
        }

//        $signup_url = U('Mobile/Activity/view', array('aid' => $model_act['guid']), false, true, false);

        $signup_url = U('Home/Event/' . event_id_encode($model_act['id']), null, false, true, false);
        $this->assign('signup_url', $signup_url);
        //$this->assign('title',$model_act['name']);
        $this->title = $model_act['name'];
        $this->assign('view_act', $model_view);
        $this->assign('view_tickets', $model_view_tickets);
        $this->assign('view_flows', $model_view_flows);
        $this->assign('view_takers', $model_view_takers);
        $this->assign('view_userinfo', $model_view_userinfo);
        $this->assign('session_auth',$session_auth);
        $this->show();
    }


    /**
     * @param $list 购票列表
     * @param $tid  票种id
     * @return int
     */
    function  surplusTicket($list, $tid)
    {

        $i = 0;
        foreach ($list as $m) {
            if ($m['ticket_guid'] == $tid) {
                $i++;
            }
        }
        return $i;

    }
}
