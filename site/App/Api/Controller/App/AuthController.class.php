<?php
namespace Api\Controller\App;
use Api\Controller\BaseController;

/**
 * Created by PhpStorm.
 * User: NYW
 * Date: 2015/12/2
 * Time: 9:20
 */

    class AuthController extends BaseController{

        /**
         * 登陆
         */
       public function login(){

            $this->check_request_method("post");

           $user = D('User');
           //获取参数
           $username = trim(I('post.username'));
           $password = I('post.password');
           $imei = I('post.imei');
           $device_type = I('post.device', 0);
           // 检测是否为空
           if (empty($username) || empty($password) || empty($imei)) {
               $this->output_error('10003');
           }

           $user_info = $user->where(array('mobile' => $username,'is_del' => '0'))->find();
           if(!$user_info){
               $this->output_error('10001');
           }
           if(intval($user_info['is_active']) !== 1){
               $this->output_error('10041');
           }
           if($user_info['password'] == $password){
               //token
               $token = $this->_get_token($user_info['guid'], $imei, $device_type);
               if(empty($token)){
                   $this->output_error('10022');
               } else {

                   $time = time();
                   $data['login_num'] = intval($user_info['login_num']) + 1;
                   $data['last_login_at'] = $time;
                   $user->where(array('guid' => $user_info['guid']))->save($data);

                   // 生成json
                   $info=D('UserAttrInfo')->where(array('user_guid'=>$user_info['guid']))->find();
                   $data = array('guid'      => $user_info['guid'], 'email' => $user_info['email'],
                       'mobile'    => $user_info['mobile'],
                       'real_name' => $info['realname'],
                       'is_active' => $user_info['is_active'],
                       'photo'     => !empty($user_info['photo'])?$user_info['photo']:0,
                       'moblie_verify' => $user_info['moblie_verify'],
                       'token'     => $token['token'],
                       'token_num' => 01
                   );
                   $this->output_data($data);
               }
           }else{
               // 密码错误
               $this->output_error('10002');
           }
       }

        /**
         * 生成token
         * @param $user_guid
         * @param $imei
         * @param int $device_type
         * @return bool|mixed
         */
        private function _get_token($user_guid, $imei, $device_type = 0)
        {
            if(empty($user_guid) || empty($imei)) return false;

            //生成token
            $time = time();
            $token = md5($user_guid. $imei . strval($time) . strval(rand(0,999999)));

            $model_device = D('UserDevice');
            $model_device->where(array('user_guid'=>$user_guid, 'status'=>'1'))
                ->save(array('status'=>'0', 'updated_at' => time()));
            $condition = array('imei' => $imei,'user_guid'=>$user_guid);
            $res = $model_device->where($condition)->find();
            if (!empty($res)) {
                $data['status'] = 1;
                $data['token'] = $token;
                $data['token_num'] = 0;
                $data['login_num'] = intval($res['login_num']) + 1;
                $result = $model_device->editTokenInfo($condition, $data);
            } else {
                $data['user_guid'] = $user_guid;
                $data['type'] = 2;
                $data['imei'] = $imei;
                $data['device_type'] = $device_type;
                $data['status'] = 1;
                $data['token'] = $token;
                $data['token_num'] = 0;
                $data['login_num'] = 1;
                $result = $model_device->addTokenInfo($data);
            }

            if($result) {
                $token_info = $model_device->field('token,token_num')->where($condition)->find();
                return $token_info;
            } else {
                return false;
            }
        }

}
