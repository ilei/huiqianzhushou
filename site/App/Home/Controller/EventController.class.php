<?php
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

        $session_auth = $this->kookeg_auth_data();

        $actGuid = I('get.g', null);

        if (!$actGuid) {
            $this->error('参数为空');
            exit;
        }

        $data = array();
        $data['guid'] = $actGuid;
        $dal = D('Activity');
        $model_act = $dal->where($data)->find();
        if (!$model_act) {
            $this->error('没有找到活动');
            exit;
        }

        if (intval($model_act['is_del']) == 1) {
            $this->error('活动已被删除');
            exit;
        }

        //这里需要判断一下是否是自己的活动 如果是自己的活动不需要判断未公开和未发布
        $session_auth = $this->kookeg_auth_data();
        if (!$session_auth) {
            if (intval($model_act['status']) == 0) {
                $this->error('活动未发布');
                exit;
            }
        } else { 

            $user_guid = $session_auth['guid'];
            if ($model_act['user_guid'] != $user_guid) { //如果不是自己的活动
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



        //直接设置状态
        $_view_status = $model_act['status'];

        //根据票据获取状态
        $_signup_status = D('Act', 'Logic')->check_signup_time($model_act['guid'], $model_act['start_time'], $model_act['end_time']);

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


        $dal = D('ActivityAttrTicket');
        $model_list_ticket = $dal
            ->where('activity_guid = "' . $model_act['guid'] . '" and is_del = 0')
            ->select();
        $model_view_tickets = array();
        if ($model_list_ticket) {

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
        }

        $dal = D('ActivityAttrFlow');
        $model_list_flow = $dal
            ->where('activity_guid = "' . $model_act['guid'] . '" and is_del = 0')
            ->order('start_time ')
            ->select();


        $model_view_flows = array();

        //
        $model_view_flows_time_interval = 0;

        //遍历所有
        foreach ($model_list_flow as $m) {
            //格式化后的时间
            $_view_time = "";

            $_view_time = kookeg_date_format(array(
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

        //ym_activity_attr_undertaker 承办机构
        $dal = D('ActivityAttrUndertaker');
        $model_list_taker = $dal
            ->where('activity_guid = "' . $model_act['guid'] . '" and is_del = 0')
            ->order('type')
            ->select();
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

        $dal = D('ActivityUserinfo');
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
        $user_guid_list=kookeg_array_column($model_list_userinfo,'user_guid',null);

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

        $model_photos_map=kookeg_array_column($model_list_photos,null,'user_guid');

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

        $signup_url = U('Home/Event/' . event_id_encode($model_act['id']), null, false, true, false);
        $this->assign('signup_url', $signup_url);
        $this->title = $model_act['name'];
        $this->assign('view_act', $model_view);
        $this->assign('view_tickets', $model_view_tickets);
        $this->assign('view_flows', $model_view_flows);
        $this->assign('view_takers', $model_view_takers);
        $this->assign('view_userinfo', $model_view_userinfo);
        $this->assign('session_auth',$session_auth);
        $this->show();
    }


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
