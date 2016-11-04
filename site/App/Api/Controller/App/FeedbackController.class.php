<?php
namespace Api\Controller\App;
use Api\Controller\App\BaseUserController;


/**
 * Created by PhpStorm.
 * User: NYW
 * Date: 2015/12/2
 * Time: 9:20
 */

    class FeedbackController extends BaseUserController{

        /**
         * 获取个人详情
         */
        public function  feedback(){
            $this->check_request_method('post');
            //获取参数
            $guid       = $this->user_info['user_guid'];
            $content = trim(I('post.content'));

            // 判断是否为空
            if (empty($content)) {
                $this->output_error('10013');
            }
            $info=D('User')->field('email,mobile')->where(array('guid'=>$guid))->find();
            $data=array('guid'=>create_guid(),
                'user_guid'=>$guid,
                'real_name'=>$this->user_info['realname'],
                'mobile'=> $info['mobile'],
                'email'=> $info['email'],
                'content'=>$content,
                'created_at'=>time(),
                'updated_at'=>time());
            $res=M('Opinion')->add($data);
            if($res){
                $this->output_data();
            }else{
                $this->output_error('10009');
            }
        }

        /**
         * 编辑用户信息
         */
        public function edit(){
            $this->check_request_method('put');
        }

        /**
         * 修改密码
         */
        public function set_pw(){
            $this->check_request_method('put');
            $oldPw=trim(I('put.oldPw'));
            $newPw=trim(I('put.newPw'));
            $guid=$this->user_info['user_guid'];
            $data=D('User')->field('password')->where(array('guid'=>$guid))->find();
            $password=$data['password'];
            if(empty($oldPw)||empty($newPw)){
                $this->output_error('10003');
            }
            if($oldPw==$password){
                M('User')->where(array('guid'=>$guid))->data(array('password'=>$newPw))->save();
                $this->output_data();
            }else{
                $this->output_error('10002');
            }

        }
}
