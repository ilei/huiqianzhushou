<?php
namespace Api\Controller\App;
use Api\Controller\App\BaseUserController;


/**
 * Created by PhpStorm.
 * User: NYW
 * Date: 2015/12/2
 * Time: 9:20
 */

    class ActivityController extends BaseUserController{

        /**
         * 获取我发布的活动
         */
        public function  get_activity(){
            $this->check_request_method('get');
            //获取参数
            $guid       = $this->user_info['user_guid'];
            $page = I('get.page', '1');
            $pagesize = I('get.pagesize', '8');
            $where = "user_guid='$guid' and is_del=0 ";
            //查询数据
            $list=D('Activity')
                ->field('id,name,guid,start_time,end_time,poster,url')
                ->where($where)->limit($page,$pagesize)->select();
            if(empty($list)){
                $this->output_error('10013');
            }
            //activity_id的集合
            $activity_array=array();
            //循环取出activity_id,ticket_id
            foreach ( $list as $key=>$value) {
                $activity_id=$value['guid'];
                array_push($activity_array,$activity_id);
            }
            $map['activity_guid'] = array('in',$activity_array);
            $ticket_price_array=D('ActivityAttrTicket')->field('price,guid')->where($map)->select();
            foreach ( $list as $key=>$value) {
                $url=U("Home/Event/" . event_id_encode($value['id']), null, false, true, false);
                $list[$key]['price'] =$ticket_price_array[$key]['price'];
                $list[$key]['url'] =$url;
            }
            $this->output_data($list);
        }

        /**
         * 获取我发布的活动-公共的
         */
        public function  get_activity_common(){
            $this->check_request_method('get');
            //获取参数
            $guid       = $this->user_info['user_guid'];
            $page = I('get.page', '1');
            $pagesize = I('get.pagesize', '8');
            $where = "is_public='1' and is_del=0 ";
            //查询数据
            $list=D('Activity')
                ->field('id,name,guid,start_time,end_time,poster,url')
                ->where($where)->limit($page,$pagesize)->select();
            if(empty($list)){
                $this->output_error('10013');
            }
            //activity_id的集合
            $activity_array=array();
            //循环取出activity_id,ticket_id
            foreach ( $list as $key=>$value) {
                $activity_id=$value['guid'];
                array_push($activity_array,$activity_id);
            }
            $map['activity_guid'] = array('in',$activity_array);
            $ticket_price_array=D('ActivityAttrTicket')->field('price,guid')->where($map)->select();
            foreach ( $list as $key=>$value) {
                $url=U("Home/Event/" . event_id_encode($value['id']), null, false, true, false);
                $list[$key]['price'] =$ticket_price_array[$key]['price'];
                $list[$key]['url'] =$url;
            }
            $this->output_data($list);
        }


        /**
         * 获取我发布的活动-公共的
         */
        public function  search_activity(){
            $this->check_request_method('get');
            //获取参数
            $guid       = $this->user_info['user_guid'];
            $key = I('get.k');
            $value = I('get.v');
            $editable = array('areaid_name', 'start_time','type');
            //验证key
            if (!in_array($key, $editable)) return $this->output_error('10003');
            $page = I('get.page', '1');
            $pagesize = I('get.pagesize', '8');
            switch ($key){
                case 'areaid_name':
                    switch($value){
                        //全国
                        case 'all':
                            $where=array('user_guid'=>$guid);
                            break;
                        default:
                            $where=array('user_guid'=>$guid,'areaid_1_name'=>$value);
                            break;
                    }


                    break;
                /**
                 * //php获取今日开始时间戳和结束时间戳
                $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
                $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                //php获取昨日起始时间戳和结束时间戳
                $beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
                $endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;

                //php获取上周起始时间戳和结束时间戳

                $beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
                $endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));

                //php获取本月起始时间戳和结束时间戳

                $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
                $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
                */
                case 'start_time';
                    switch($value){
                        //全时段
                        case 'all':
                            $where=array('user_guid'=>$guid);
                            break;
                        //今天
                        case '1':
                            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
                            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                            $where=array('user_guid'=>$guid,'start_time'>=$beginToday,'start_time'<=$endToday);
                            break;
                        //明天
                        case '2';
                            $beginDay=mktime(0,0,0,date('m'),date('d')+1,date('Y'));
                            $endDay=mktime(0,0,0,date('m'),date('d')+2,date('Y'))-1;
                            $where=array('user_guid'=>$guid,'start_time'>=$beginDay,'start_time'<=$endDay);
                            break;
                        //本周
                        case '3':
                            $beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+2-7,date('Y'));
                            $endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+14-7,date('Y'));
                            $where=array('user_guid'=>$guid,'start_time'>=$beginLastweek,'start_time'<=$endLastweek);
                            break;
                        //本周末
                        case '4':
                            $beginWeek=mktime(0,0,0,date('m'),date('d')-date('w')+2-5,date('Y'));
                            $endWeek=mktime(23,59,59,date('m'),date('d')-date('w')+14-7,date('Y'));
                            $where=array('user_guid'=>$guid,'start_time'>=$beginWeek,'start_time'<=$endWeek);
                            break;
                        //本月
                        case '5':
                            $beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
                            $endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
                            $where=array('user_guid'=>$guid,'start_time'>=$beginThismonth,'start_time'<=$endThismonth);
                            break;
                        default:
                            $where=array('user_guid'=>$guid);
                            break;
                    }
                    break;
                case 'type';
                    switch($value){
                        case 'all':
                            $where=array('user_guid'=>$guid);
                            break;
                        default:
                            $type_guid=D('ActivitySubject')->field('guid')->where(array('name'=>$value))->find();
                            $where=array('user_guid'=>$guid,'subject_guid'=>$type_guid);
                            break;
                    }
                    break;
            }
            //查询数据
            $list=D('Activity')
                ->field('id,name,guid,start_time,end_time,poster,url')
                ->where($where)->limit($page,$pagesize)->select();
            if(empty($list)){
                $this->output_error('10013');
            }
            //activity_id的集合
            $activity_array=array();
            //循环取出activity_id,ticket_id
            foreach ( $list as $key=>$value) {
                $activity_id=$value['guid'];
                array_push($activity_array,$activity_id);
            }
            $map['activity_guid'] = array('in',$activity_array);
            $ticket_price_array=D('ActivityAttrTicket')->field('price,guid')->where($map)->select();
            foreach ( $list as $key=>$value) {
                $url=U("Home/Event/" . event_id_encode($value['id']), null, false, true, false);
                $list[$key]['price'] =$ticket_price_array[$key]['price'];
                $list[$key]['url'] =$url;
            }
            $this->output_data($list);
        }

}
