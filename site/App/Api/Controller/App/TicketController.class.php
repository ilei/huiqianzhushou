<?php
namespace Api\Controller\App;
use Api\Controller\App\BaseUserController;


/**
 * Created by PhpStorm.
 * User: NYW
 * Date: 2015/12/2
 * Time: 9:20
 */

    class TicketController extends BaseUserController{

        /**
         * 获取电子票----未完成
         */
        public function  get_tickect(){
            $this->check_request_method('get');
            //获取参数
            $guid       = $this->user_info['user_guid'];
            $page = I('get.page', '1');
            $pagesize = I('get.pagesize', '8');
            $where = "user_guid='$guid' and is_del=0 and status!=4";
            //查询数据
            $list=D('ActivityUserTicket')
                ->field('guid,activity_guid,user_guid,ticket_guid,status,ticket_name')
                ->where($where)->limit($page,$pagesize)->select();
            if(empty($list)){
                $this->output_error('10013');
            }
            //activity_id的集合
            $activity_array=array();
            //ticket_id的集合
            $ticket_array=array();
            //循环取出activity_id,ticket_id
            foreach ( $list as $key=>$value) {
                $activity_id=$value['activity_guid'];
                array_push($activity_array,$activity_id);
//                $activity_name=D('Activity')->field('name')->where(array('guid'=>$activity_id))->find();
                $ticket_id=$value['ticket_guid'];
                array_push($ticket_array,$ticket_id);
//                $price=D('ActivityAttrticket')->field('price')->where(array('ticket_guid'=>$ticket_id))->find();
//                $list[$key]['activity_name'] =$activity_name;
//                $list[$key]['price'] =$price;
            }
            $map['guid'] = array('in',$activity_array);
            $act_name_array=D('Activity')->field('name,guid,start_time,end_time,poster,qrcode_url,url,address')->where($map)->select();
            $map['guid'] = array('in',$ticket_array);
            $ticket_price_array=D('ActivityAttrTicket')->field('price,guid')->where($map)->select();
            foreach ( $list as $key=>$value) {
                $list[$key]['activity_name'] =$act_name_array[$key]['name'];
                $list[$key]['start_time'] =$act_name_array[$key]['start_time'];
                $list[$key]['poster'] =$act_name_array[$key]['poster'];
                $list[$key]['qrcode_url'] =$act_name_array[$key]['qrcode_url'];
                $list[$key]['activity_url'] =$act_name_array[$key]['url'];
                $list[$key]['address'] =$act_name_array[$key]['address'];
                $list[$key]['end_time'] =$act_name_array[$key]['end_time'];
                $list[$key]['price'] =$ticket_price_array[$key]['price'];
            }
            $this->output_data($list);
        }

        public function  get_tickect_success(){
            $this->check_request_method('get');
            //获取参数
            $guid       = $this->user_info['user_guid'];
            $page = I('get.page', '1');
            $pagesize = I('get.pagesize', '8');
            $where = "user_guid='$guid' and is_del=0 and status=4";
            //查询数据
            $list=D('ActivityUserTicket')
                ->field('guid,activity_guid,user_guid,ticket_guid,status,ticket_name')
                ->where($where)->limit($page,$pagesize)->select();
            if(empty($list)){
                $this->output_error('10013');
            }
            //activity_id的集合
            $activity_array=array();
            //ticket_id的集合
            $ticket_array=array();
            //循环取出activity_id,ticket_id
            foreach ( $list as $key=>$value) {
                $activity_id=$value['activity_guid'];
                array_push($activity_array,$activity_id);
//                $activity_name=D('Activity')->field('name')->where(array('guid'=>$activity_id))->find();
                $ticket_id=$value['ticket_guid'];
                array_push($ticket_array,$ticket_id);
//                $price=D('ActivityAttrticket')->field('price')->where(array('ticket_guid'=>$ticket_id))->find();
//                $list[$key]['activity_name'] =$activity_name;
//                $list[$key]['price'] =$price;
            }
            $map['guid'] = array('in',$activity_array);
            $act_name_array=D('Activity')->field('name,guid,start_time,end_time,poster,qrcode_url,url,address')->where($map)->select();
            $map['guid'] = array('in',$ticket_array);
            $ticket_price_array=D('ActivityAttrTicket')->field('price,guid')->where($map)->select();
            foreach ( $list as $key=>$value) {
                $list[$key]['activity_name'] =$act_name_array[$key]['name'];
                $list[$key]['start_time'] =$act_name_array[$key]['start_time'];
                $list[$key]['poster'] =$act_name_array[$key]['poster'];
                $list[$key]['qrcode_url'] =$act_name_array[$key]['qrcode_url'];
                $list[$key]['activity_url'] =$act_name_array[$key]['url'];
                $list[$key]['address'] =$act_name_array[$key]['address'];
                $list[$key]['end_time'] =$act_name_array[$key]['end_time'];
                $list[$key]['price'] =$ticket_price_array[$key]['price'];
            }
            $this->output_data($list);
        }

}
