<?php
namespace Home\Controller;
use       Think\Image;

/**
 * 活动报名表单控制器 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/

class FormController extends BaseController{

    public function __construct(){
        parent::__construct();	
        layout('layout_new');
    }

    /**
     * 表单项添加
     *
     * @access public 
     * @param  void 
     * @return void 
     **/

    public function form_build_add(){
        if(IS_AJAX){
            $activity_guid = I('get.aguid'); 
            $item          = I('post.');
            if (!empty($item) && $item['name']) { 
                $exist = M('ActivityForm')->where(array('activity_guid' => $activity_guid, 'name' => trim($item['name'])))->find();
                if($exist){
                    $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_SAME_NAME_EXIST_')));
                }
                $time         = time();
                $buid_guid    = create_guid();
                $form = array(
                    'guid'          => $buid_guid,
                    'activity_guid' => $activity_guid,
                    'name'          => trim($item['name']),
                    'note'          => $item['note'] ? trim($item['note']) : '请输入' . $item['name'],
                    'ym_type'       => $item['ym_type'],
                    'html_type'     => $item['html_type'],
                    'is_info'       => 0,
                    'is_required'   => isset($i['is_required']) ? 1 : 0,
                    'created_at'    => $time,
                    'updated_at'    => $time
                );
                $data_options = array();
                if(isset($item['options']) && $item['options']){
                    foreach ($item['options'] as $o) {
                        $data_options[] = array(
                            'guid'          => create_guid(),
                            'activity_guid' => $activity_guid,
                            'build_guid'    => $buid_guid,
                            'value'         => $o,
                            'created_at'    => $time,
                            'updated_at'    => $time,
                        );
                    }
                }
            }
            if (!empty($form)) {
                $res  = M('ActivityForm')->data($form)->add();
            }
            if (!empty($data_options) && $res) {
                $res = M('ActivityFormOption')->addAll($data_options);
            }
            foreach($data_options as $key => $value){
                unset($data_options[$key]);
                $data_options[$value['build_guid']][] = $value; 
            }
            $this->assign('build_info', array($form));
            $this->assign('option_info', $data_options);
            $item = $this->fetch('add_item');
            $option = $this->fetch('add_item_target');
            if($res){
                $this->ajax_return(array('status' => C('ajax_success'), 'form' => $item, 'target' => $option));
            }else{
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            }
        }
        exit(); 


    }

    /**
     * 表单删除 
     *
     * @access public 
     * @param  void 
     * @return void 
     **/ 

    public function form_build_del(){
        if(IS_AJAX){
            //$this->ajax_request_limit('act::form_build_del::');
            $activity_guid = I('get.aguid'); 
            $guid = I('post.guid');
            $condition  = array('guid' => trim($guid), 'activity_guid' => trim($activity_guid));
            $res = M('ActivityForm')->where($condition)->delete();
            $condition  = array('build_guid' => trim($guid), 'activity_guid' => trim($activity_guid));
            M('ActivityFormOption')->where($condition)->delete(); 
            if($res){
                $this->ajax_return(array('status' => C('ajax_success')));
            }else{
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            }
        }
        exit(); 
    }
    /**
     * 表单项编辑 
     *
     * @access public 
     * @param  void 
     * @return void 
     **/ 

    public function form_build_edit(){
        if(IS_AJAX){
            $this->ajax_request_limit('act::form_build_edit::');
            $activity_guid = trim(I('get.aguid')); 
            $form_guid     = trim(I('post.guid'));
            foreach(I('post.') as $key => $value){
                $post[$key] = !is_array($value) ? trim($value) : $value;
            }

            $mulit = in_array($post['html_type'], array('checkbox', 'radio', 'select')) ? true : false;
            //名称和提示都存在
            if(!$post['name'] || (!$mulit && !$post['note'])){
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_FORM_NAME_OR_NOTE_NOT_EMPTY_')));
            }

            //检测是否存在同名的表单
            $cond = array(
                'guid' => array('NEQ', $form_guid), 
                'name' => $post['name'],
                'activity_guid' => $activity_guid,
            );
            $exist = M('ActivityForm')->where($cond)->find();
            if($exist){
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_SAME_NAME_EXIST_')));
            }

            if($mulit && !$post['options']){
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_FORM_OPTION_NOT_EMPTY_')));
            }

            //组装表单数据
            $time = time();
            if($post['ym_type']=='sex' && $post['name']!='性别'){
                $post['ym_type'] = 'default';
            }
            $data_build = array(
                'name' => $post['name'], 
                'note' => $post['note'],
                'ym_type'     => $post['ym_type'],
                'html_type'   => $post['html_type'],
                'is_required' => intval($post['is_required']), 
                'updated_at'  => $time, 
            ); 
            //判断是否还有options 
            if(isset($post['options']) && $post['options']){
                $tmp = $options = array();
                foreach($post['options'] as $key => $option){
                    if(!isset($option['value']) || !$option['value']){
                        $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_FORM_OPTION_NAME_NOT_EMPTY_')));
                    }
                    $value = trim($option['value']);
                    if(!in_array($value, $tmp)){
                        array_push($tmp, $value);
                    }else{
                        $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_SAME_OPTION_NAME_EXIST_')));
                    }   
                    $options[] = array(
                        'guid' => create_guid(),
                        'build_guid' => $form_guid,
                        'activity_guid' => $activity_guid, 
                        'value' => $value,
                        'created_at' => $time,
                        'updated_at' => $time,
                    );                  
                }            
                $condition = array(
                    'build_guid' => $form_guid,
                    'activity_guid' => $activity_guid,
                );
                M('ActivityFormOption')->where($condition)->delete();
                M('ActivityFormOption')->addAll($options);
            }

            $cond = array(
                'activity_guid' => $activity_guid,  
                'guid'          => $form_guid,
            );
            $res = M('ActivityForm')->where($cond)->save($data_build);
            $this->ajax_return(array('status' => C('ajax_success')));
        }
        exit(); 
    }

    /**
     * 表单设置 
     *
     * @access public 
     * @param  void 
     * @return void 
     **/ 

    public function form_set(){
        $this->title = L('_TICKET_MANAGE_');
        $guid = I('get.aguid');
        if(!$guid){
            $this->error(L('_ACT_NOT_EXIST_'));
        }else{
            $auth = $this->get_auth_session();
            $condition = array('user_guid' => $auth['guid'], 'guid' => trim($guid));
            $activity = D('Activity')->where($condition)->find(); 
            if(!$activity){
                $this->error(L('_ACT_NOT_EXIST_'));
            }else{
                $this->title = L('_FORM_SET_TITLE_');
                $this->css[] = 'meetelf/css/switch_1.css';
                $this->css[] = 'meetelf/css/create-activities.css';
                $this->css[] = 'meetelf/css/form-set.css';
                if($activity['status']==0){
                    $this->main = '/Public/meetelf/home/js/build/form.form_set.js';
                }
                $build_info   = D('ActivityForm')->where(array('activity_guid' => $guid))->order('sort desc,id')->select();
                $option_info  = D('ActivityFormOption')->where(array('activity_guid' => $guid))->field('guid,build_guid,value')->select();
                foreach($option_info as $key => $value){
                    unset($option_info[$key]);
                    $option_info[$value['build_guid']][] = $value; 
                }
                $activity['activity_is_verify'] = $activity['is_verify'];
                $this->assign('build_info', $build_info);
                $this->assign('option_info', $option_info);
                $this->assign('act', $activity);
                $this->show();
            }
        }
    }
// 表单排序
    public function form_set_edit(){
        $param = I('post.');
        // var_dump($param);
        $num   = count($param);
        // echo $num;die;
        $guid  = I('get.aguid');
        $i=$num;
        $num_x = $num+2;
        $num_s = $num+1;
        $res_x = M('ActivityForm')->where(array('name'=>"姓名",'activity_guid'=>$guid))->save(array('sort'=>$num_x));
        $res_s = M('ActivityForm')->where(array('name'=>"手机",'activity_guid'=>$guid))->save(array('sort'=>$num_s));
        foreach ($param as $key => $value) {
            $res = M('ActivityForm')->where(array('id'=>$key,'activity_guid'=>$guid))->save(array('sort'=>$i)); 
           $i--;
        }
        $this->success('保存完成',U('Home/Form/setting',array('guid'=>$guid)));

    }

    public function ajax_check_item_name(){
        if(IS_AJAX){
            $aguid = trim(I('get.guid'));
            $name  = trim(I('post.name')); 
            $guid  = trim(I('post.guid'));
            $condition = array(
                'activity_guid' => $aguid,
                'name'          => $name,
            );
            if($guid){
                $condition['guid'] = array('NEQ', $guid);
            }
            $exist = M('ActivityForm')->where($condition)->find();
            echo $exist ? 'false' : 'true';
        } 
        exit();
    }
}
