<?php
namespace Api\Controller\App;
use Api\Controller\App\BaseUserController;


/**
 * Created by PhpStorm.
 * User: NYW
 * Date: 2015/12/2
 * Time: 9:20
 */

    class  CollectController extends BaseUserController{

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



}
