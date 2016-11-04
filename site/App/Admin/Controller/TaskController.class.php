<?php
namespace Admin\Controller;

class TaskController extends BaseController{
    public function save_flow(){
        $task_flow_guid = I('post.guid');
        $task_flow_name = I('post.name');
        
        $task_flow_list = explode(',', I('post.selected_guid'));
        $task_flow_data = array();
        if($task_flow_guid){
            $task_flow_data['name'] = I('post.name');
            $task_flow_data['sequence'] = json_encode($task_flow_list);
            $task_flow_data['updated_at'] = time();
            $task_flow_data['is_del'] = I('post.state');
            $res = M('TaskFlow')->where(array('guid'=>I('post.guid')))->save($task_flow_data);
        }else{
            $task_flow_data['guid'] = create_guid();
            $task_flow_data['name'] = I('post.name');
            $task_flow_data['sequence'] = json_encode($task_flow_list);
            $task_flow_data['created_at'] = time();
            $task_flow_data['updated_at'] = time();
            $task_flow_data['is_del'] = I('post.state');
            $res = M('TaskFlow')->add($task_flow_data);
        }
        if($res){
            $this->ajaxReturn(array('status'=>'ok'));
        }else{
            $this->ajaxReturn(array('status'=>'ko', 'msg'=>'任务流保存失败, 请重试.'));
        }
    }
    
    public function flow_edit(){
        if(I('get.guid')){
            $task_flow_info = M('TaskFlow')->where(array('guid'=>I('get.guid')))->find();
            $selected_task_list = M(TaskInfo)->field('id,guid,name')->where(array('guid'=>array('IN',json_decode($task_flow_info['sequence']))))->select();
            $this->assign('selected_task_list',$selected_task_list);
            $this->assign('task_flow_info',$task_flow_info);
        }
        $task_list = M('TaskInfo')->field('id,guid,name')->where(array('type'=>'1'))->select();
        $this->assign('task_list',$task_list);
        $this->display();
    }
    
    public function flow(){
        $flow_list = M('task_flow')->order('id DESC')->select();
        $this->assign('flow_list',$flow_list);
        $this->display();
    }
    
    public function index(){
        $task_list = M('TaskInfo')->select();
        $this->assign('task_list',$task_list);
        $this->display();
    }
    
    public function edit(){
        if(I('get.guid')){
           $task_info = M('TaskInfo')->where(array('guid'=>I('get.guid')))->find();
           $task_condition = M('TaskCondition')->where(array('task_guid'=>I('get.guid')))->select();
           $this->assign('task_condition', $task_condition);
           $this->assign('task_info', $task_info);
        }
        $ym_js = M('Webjs')->select();
        $task_type_list = M('TaskSign')->select();
        $this->assign('task_type_list', $task_type_list);
        $this->assign('ym_js', $ym_js);
        $this->display();
    }
    
    public function save_task(){
        $task_data = array();
        $task_data['guid'] = I('post.guid') ? I('post.guid') : create_guid();
        $task_data['name'] = I('post.name');
        $task_data['type'] = I('post.type');
        $task_data['integral'] = I('post.integral');
        $task_data['exp'] = I('post.exp');
        $task_data['sign'] = I('post.sign');
        $task_data['thumb'] = I('post.thumb');
        $task_data['startime'] = I('post.startTime');
        $task_data['endtime'] = I('post.endTime');
        $task_data['is_del'] = I('post.is_del');
        $task_data['description'] = htmlspecialchars_decode(stripslashes(I('post.description')));
        
        $task_condition = array();
        $condition_guid = array();
        $condition_init = I('post.condition');
        foreach($condition_init as $key=>$value){
            $task_condition[$key]['guid'] = $condition_guid[] = $value['guid'] ? $value['guid'] : create_guid();
            $task_condition[$key]['task_guid'] = $task_data['guid'];
            $task_condition[$key]['name'] = $value['name'];
            $task_condition[$key]['sign'] = $value['sign'];
            $task_condition[$key]['type'] = $value['type']?$value['type']:0;
            $task_condition[$key]['finish_num'] = $value['num']?$value['num']:0;
            $task_condition[$key]['webjs'] = $value['webjs'];
            $task_condition[$key]['updated_at'] = time();
        }
        
        $del_condition_guid =array();
        
        if(I('post.guid')){
            $task_data['updated_at'] = time();
            $res = M('TaskInfo')->where(array('guid'=>I('post.guid')))->save($task_data);
            
            $task_condition_list = M('TaskCondition')->where(array('task_guid'=>$task_data['guid']))->select();
            $task_condition_guid = array();
            foreach($task_condition_list as $key=>$value){
                $task_condition_guid[] = $value['guid'];
            }
            $del_condition_guid = array_diff($task_condition_guid, $condition_guid);
        }else{
            //新增数据
            $task_data['create_at'] = time();
            $task_data['updated_at'] = time();
            $res = M('TaskInfo')->add($task_data);   
        }
        
        if($res){
            if(!empty($del_condition_guid)){
                $del_condition_guid_str = implode(',', $del_condition_guid);
                if(!M('TaskCondition')->where(array('guid'=>array('IN',$del_condition_guid_str)))->delete()){
                    $this->ajaxReturn(array('status'=>'ko', 'msg'=>'任务条件删除失败, 请重试.'));
                }
            }
            if(M('TaskCondition')->inserUpAll($task_condition)){
                $this->ajaxReturn(array('status'=>'ok'));
            }else{
                $this->ajaxReturn(array('status'=>'ko', 'msg'=>'任务条件保存失败, 请重试.'));
            }
        }else{
            $this->ajaxReturn(array('status'=>'ko', 'msg'=>'任务信息保存失败, 请重试.'));
        }
    }
}