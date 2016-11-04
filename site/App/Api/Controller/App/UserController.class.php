<?php
namespace Api\Controller\App;
use Api\Controller\App\BaseUserController;


/**
 * Created by PhpStorm.
 * User: NYW
 * Date: 2015/12/2
 * Time: 9:20
 */

    class UserController extends BaseUserController{

        /**
         * 获取个人详情
         */
        public function  get_user_info(){
            $this->check_request_method('get');
            //获取参数
            $guid       = $this->user_info['user_guid'];
            $updated_at = I('get.ut', '0');

            // 判断客户端用户信息是否已经是最新
            if ($this->user_info['updated_at'] <= $updated_at) {
                $this->output_error('10013');
            }
            $this->output_data($this->user_info);
        }

        /**
         * 编辑用户信息
         */
        public function edit(){
            $this->check_request_method('put');
            //昵称
            $nickname=trim(I('put.nickname'));
            //性别
            $gender=trim(I('put.gender'));
            //生日
            $birthday=trim(I('put.birthday'));
            //所在地
            $area1=trim(I('put.area1'));
            $area2=trim(I('put.area2'));
            //公司
            $company=trim(I('put.company'));
            //职务
            $position=trim(I('put.position'));
            //详细地址
            $address=trim(I('put.address'));
            if(empty($nickname)||empty($gender)||empty($birthday)
                ||empty($area1)||empty($area2)||empty($company)
                ||empty($position)||empty($address)){
                $this->output_data();
            }
            $guid = $this->user_info['user_guid'];
            $data=array();
            if(!empty($nickname)){
                array_push($data,$data['nickname']=$nickname);
            }
            if(!empty($gende)){
                array_push($data,$data['gender']=$gender);
            }
            if(!empty($birthday)){
                array_push($data,$data['birthday']=$birthday);
            }
            if(!empty($area1)){
                array_push($data,$data['area1']=$area1);
            }
            if(!empty($area2)){
                array_push($data,$data['area2']=$area2);
            }
            if(!empty($company)){
                array_push($data,$data['company']=$company);
            }
            if(!empty($position)){
                array_push($data,$data['position']=$position);
            }
            if(!empty($address)){
                array_push($data,$data['address']=$address);
            }
            $time=time();
            array_push($data,$data['updated_at']=$time);
            $res = M('UserAttrInfo')->where(array('user_guid'=>$guid))->data($data)->save();
            if ($res){
                return $this->output_data(array('ut'=>$time)); // 保存成功
            }else{
                return $this->output_error('10011'); // 保存失败
            }

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
