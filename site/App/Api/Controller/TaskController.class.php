<?php
namespace Api\Controller;

/**
 * 任务
 *
 * Class TaskController
 * @package Api\Controller
 *
 * CT: 2014-11-29 11:30 by Qxl
 */
class TaskController extends BaseUserController{
    /**
     * 任务完成列表
     *
     * CT: 2015-01-30 17:30 by Qxl
     */
    public function task_finish_list(){
        if (IS_GET){
            $user_guid = $this->user_info['guid'];  //I('post.user_guid');
            $user_task_progress = M('TaskProgress')->where(array('user_guid'=>$user_guid, 'type'=>'1','state'=>'2'))->order('finished_at DESC')->select();
            $finish_task_data = array();
            foreach($user_task_progress as $key=>$value){
                $task_accept_info = M('TaskInfo')->where(array('guid'=>$value['task_guid'], 'is_del'=>0))->find();
                $task_accept_info['description'] = strip_tags($task_accept_info['description']);
                $task_accept_info['progress_guid'] = $value['guid'];
                $finish_task_data[] = $task_accept_info;
            }
            $this->output_data(array('finish_task'=>$finish_task_data));
        }else{
           $this->output_error('10025', 'HTTP request error');
       }
    }
    
    /**
     * 任务可接&已接列表
     *
     * CT: 2015-01-30 17:30 by Qxl
     */
    public function task_list(){
       if (IS_GET){
            $user_guid = $this->user_info['guid'];   //I('post.user_guid');

            $view_new_task_data = array(); //普通新任务
            $view_new_daily_task_data = array(); //日常新任务
            $view_accept_task_data = array(); //已接普通任务
            $view_accept_daily_task_data = array(); //已接日常任务
             
            $flow_list = M('TaskFlow')->where(array('is_del'=>'0'))->select();//查询任务流
            foreach ($flow_list as $key=>$value){
                //取得当前任务流最新的任务情况
                $user_task_progress = M('TaskProgress')->where(array('user_guid'=>$user_guid, 'flow_id'=>$value['id']))->order('id DESC')->find();
                //当前任务流各任务GUID
                $task_guid_list = json_decode($value['sequence'],true);
                 
                if(empty($user_task_progress)){ //没有任务进度
                    $view_new_task_data[$key]['guid']= $task_guid_list[0];
                    $view_new_task_data[$key]['flowid']= $value['id'];
                }else if($user_task_progress['state'] == '1'){
                    $view_accept_task_data[$key]['guid']= $user_task_progress['task_guid'];
                    $view_accept_task_data[$key]['flowid']= $value['id'];
                    $view_accept_task_data[$key]['progress_guid']= $user_task_progress['guid'];
                }else if($user_task_progress['state'] == '2'){
                    if(end($task_guid_list) !== $user_task_progress['task_guid']){
                        $view_new_task_data[$key]['guid']= $task_guid_list[array_search($user_task_progress['task_guid'],$task_guid_list)+1];
                        $view_new_task_data[$key]['flowid']= $value['id'];
                    }
                }
            }
             
            //日常任务列表
            $daily_task_list = M('TaskInfo')->where(array('type'=>'2','is_del'=>'0'))->select();
            $todyStamp = strtotime(date('Y-m-d',time()));
            $tomorrowStamp = strtotime(date('Y-m-d',strtotime('+1 day')));
            foreach($daily_task_list as $key=>$value){
                $daily_task = M('TaskProgress')->where(array('user_guid'=>$user_guid, 'task_guid'=>$value['guid']))->order('id DESC')->find();
            
                $time = time();
                $time_limit = false;
                if(!empty($value['startime']) && !empty($value['endtime'])){
                    $startime = strtotime(date('Y-m-d H:i:s',strtotime($value['startime'])));
                    $endtime = strtotime(date('Y-m-d H:i:s',strtotime($value['endtime'])));
                    $time_limit = true;
                }
                 
                if(empty($daily_task)){
                    if($time_limit){
                        if($time > $startime && $time < $endtime){
                            $view_new_daily_task_data[$key]['guid'] = $value['guid'];
                        }
                    }else{
                        $view_new_daily_task_data[$key]['guid'] = $value['guid'];
                    }
                }else{
                    if($daily_task['state'] == '1'){
                        if($time_limit){
                            if($time < $endtime){
                                $view_accept_daily_task_data[$key]['guid'] = $value['guid'];
                                $view_accept_daily_task_data[$key]['progress_guid']= $daily_task['guid'];
                            }else{
                                M('TaskProgress')->where(array('guid'=>$daily_task['guid']))->delete(); //如果时间过期则删除任务进程
                            }
                        }else{
                            $view_accept_daily_task_data[$key]['guid'] = $value['guid'];
                            $view_accept_daily_task_data[$key]['progress_guid']= $daily_task['guid'];
                        }
                    }else if($daily_task['state'] == '2'){
                        if($daily_task['finished_at'] < $todyStamp){ //如果完成时间小于今日凌晨的时间
                            if($time_limit){
                                if($time > $startime && $time < $endtime){
                                    $view_new_daily_task_data[$key]['guid'] = $value['guid'];
                                }
                            }else{
                                $view_new_daily_task_data[$key]['guid'] = $value['guid'];
                            }
                        }
                    }
                }
            }
    
            $new_task_data_list =(array_merge($view_new_daily_task_data,$view_new_task_data));
            $new_task_data = array();
            foreach($new_task_data_list as $key=>$value){
                $task_new_info = M('TaskInfo')->where(array('guid'=>$value['guid'], 'is_del'=>'0'))->find();
                if(!empty($task_new_info)){
                    $task_new_info['description'] = strip_tags($task_new_info['description']);
                    $task_new_info['flow_id'] = isset($value['flowid']) ? $value['flowid'] : '0';
                    $new_task_data[] = $task_new_info;
                }
            }
             
            $accept_task_daily_data_list =(array_merge($view_accept_task_data,$view_accept_daily_task_data));
            $accept_task_daily_data = array();
            foreach($accept_task_daily_data_list as $key=>$value){
                $task_accept_daily_info = M('TaskInfo')->where(array('guid'=>$value['guid'], 'is_del'=>'0'))->find();
                if(!empty($task_accept_daily_info)){
                    $task_accept_daily_info['description'] = strip_tags($task_accept_daily_info['description']);
                    $task_accept_daily_info['flow_id'] = isset($value['flowid']) ? $value['flowid'] : '0';
                    $task_accept_daily_info['progress_guid'] = $value['progress_guid'];
                    $accept_task_daily_data[] = $task_accept_daily_info;
                }
            }
           
            $this->output_data(array('new_task'=>$new_task_data, 'accept_task'=>$accept_task_daily_data));
       }else{
            $this->output_error('10025', 'HTTP request error');
       }
    }
    
    
	/**
     * 检查信息是否补全
     *
     * CT: 2014-11-24 15:00 by Qxl
     */
	public function task_check_user_info(){
		$this->check_request_method('get');
		$type=I('get.type');
		if(empty($type)){
			$this->output_error('10003');
		}
		switch ($type){
			case 'face':
				$status=empty($this->user_info['photo'])? 0 : 1;
				break;
			case 'area':
				$status=empty($this->user_info['areaid_1']) || empty($this->user_info['areaid_2']) ? 0 : 1;
				break;
			case 'industry':
				$status=empty($this->user_info['main_industry_guid']) ? 0 : 1;
				break;
			default:
				$this->output_error('10003');
		}
        $this->output_data(array('status'=>$status));
	}
}