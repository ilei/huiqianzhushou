<?php
namespace Api\Controller\App;
use Api\Controller\BaseController;

/**
 * Created by PhpStorm.
 * User: NYW
 * Date: 2015/12/2
 * Time: 9:20
 */

    class RegisterController extends BaseController{

        /**
         * 发送验证码
         */
      public function sendCode(){
          $this->check_request_method('get');
          //获取参数
          $phone = trim(I('get.phone'));
          $imei = trim(I('get.imei'));
          $code = kookeg_get_mobile_code();
          //判断参数是否为空
          if(empty($phone)||empty($imei)){
              $this->output_error('10003');
          }else{
              $mobile = M('User')->where("mobile = '{$phone}'")->find();
              if($mobile){
                  //返回数据
                  $this->output_error('10046');
              }else{
              //储存code
              mobile_code(md5($phone+$imei),$code);
              //发送短信
              send_sms(C('CODE_TYPE.api_verify_mobile'), $phone, array($code, 30), array('type' => 1));
              //返回数据
              $this->output_data();
              }
          }

      }

        /***
         * 检测验证码
         */
        public function checkCode(){
            $this->check_request_method('put');
            //获取参数
            $phone=trim(I('put.phone'));
            $code=trim(I('put.code'));
            $imei = trim(I('put.imei'));
            if(empty($phone)||empty($code)||empty($imei)){
                $this->output_error('10003');
            }else{
                //生成唯一key
                $key = md5($phone+$imei);
                //查询数据
                $data= mobile_code($key);
                //false 验证码失效
                if($data===false){
                    $this->output_error('10030');
                }else{
                    //验证码错误
                    if ($code != $data) {
                        $this->output_error('10031');
                    }else{
                        $this->output_data();
                    }


                }

            }


        }

        /**
         * 注册方法
         */
        public function reg(){
            $this->check_request_method('post');
            //获取参数
            $phone = trim(I('post.phone'));
//            $imei = trim(I('post.imei'));
            $name = trim(I('post.name'));
            $password = trim(I('post.password'));
            //判断参数是否为空
            if(empty($phone)||empty($name)||empty($password)){
                $this->output_error('10003');
            }else {
                $mobile = M('User')->where("mobile = '{$phone}'")->find();
                if($mobile){
                    //返回数据
                    $this->output_error('10046');
                    return false;
                }
                $level = M('grade_level')->order('sort ASC')->find();
                $time = time();
                $user = array(
                    'guid' => create_guid(),
                    'email' => '',
                    'mobile' => $phone,
                    'password' => $password,
                    'vip' => $level['guid'],
                    'created_at' => $time,
                    'updated_at' => $time,
                    'moblie_verify' => C('default_ok_status'),
                );
                $res = M('User')->add($user);
                if ($res) {
                    $model_user_attr_auth = M('UserAttrAuth');
                    $auth_data = array(
                        'guid' => create_guid(),
                        'user_guid' => $user['guid'],
                        'login_num' => 1,
                        'last_login_at' => $time,
                        'last_login_ip' => get_client_ip(),
                        'updated_at' => $time,
                        'created_at' => $time,
                    );
                    if ($model_user_attr_auth->add($auth_data)) {

                        // 存储用户附加信息表
                        $info_data = array(
                            'guid' => create_guid(),
                            'realname' => $name,
                            'user_guid' => $user['guid'],
                            'updated_at' => $time,
                            'created_at' => $time,
                        );
                        M('UserAttrInfo')->add($info_data);
                        $this->output_data();
                    }

                }else{
                    $this->output_error('10011');
                }
            }

            }

}
