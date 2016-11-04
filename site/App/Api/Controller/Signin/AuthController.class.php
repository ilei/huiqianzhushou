<?php
namespace Api\Controller\Signin;
use Api\Controller\BaseController;

/**
 * 注册登陆
 * CT: 2015-04-15 09:30 by YLX
 */
class AuthController extends BaseController
{
    /**
     * 社团帐号登录
     *
     * CT: 2014-09-19 17:00 by YLX
     * UT: 2014-12-31 11:00 by qiu
     */
    public function login()
    {
        // 判断提交方式是否正确
        $this->check_request_method('post');

        //获取帐号参数
        $username = trim(I('post.username'));
        $password = I('post.password');
        //获取相关设备参数
        $imei = I('post.imei');
        $login_verify_code = I('post.login_verify_code');
//        $verify_code = "000000";
        $device_type = I('post.device', 0);
        // 检查所获参数的有效性
        if (empty($username) || empty($password) || empty($imei)) {
            $this->output_error('10003');
        }

		$signin_user = M('SigninUser');
		$user = M('User');
        $user_res_verify = $user->where(array('login_verify_code' => $login_verify_code, 'is_del' => '0'))->find();
        if(empty($user_res_verify)){
            $this->output_error('10001');//用户不存在
        }
		$user_info = $signin_user->where(array('username' => $username,'user_guid' => $user_res_verify['guid'],'is_del' => '0'))->find();
        if(!$user_info){
            $domain = strstr($username, '@');
            if(empty($domain)){
                //查询用户表对应签到
                $user_info = $user->where(array('mobile' => $username,'login_verify_code' => $login_verify_code, 'mobile_verify' => '1', 'is_del' => '0'))->find();
            }else{
                //查询用户表对应签到
                $user_info = $user->where(array('email' => $username,'login_verify_code' => $login_verify_code, 'email_verify' => '1', 'is_del' => '0'))->find();
            }
            if(!$user_info){
                $this->output_error('10001');//用户不存在
            }
        }
        if($user_info['user_guid']){
            //判断用户账号是否被锁定
            $user_active_res = $user->field('is_active')->where(array('guid' => $user_info['user_guid'],'is_del' => '0'))->find();
            if(intval($user_active_res['is_active']) !== 1){
                $this->output_error('10041','User account is locked');
            }
        }
        //判断签到账号是否激活
		if(intval($user_info['is_active']) !== 1){
			$this->output_error('10041','Registration account is locked');
		}
        //验证密码准确性
        if($user_info['password'] != $password){
            $this->output_error('10002');
        }else{

            //存储或更新user_device表
            $token = $this->_get_token($user_info['guid'], $imei, $device_type);
            //生成token
            if(empty($token)){
                $this->output_error('10022');//登录失败
            } else {

                $time = time();
                //登陆次数+1，最后登陆时间更新
                $data['login_num'] = intval($user_info['login_num']) + 1;
                $data['last_login_at'] = $time;
                if(!$user_info['user_guid']){
                    $signin_user->where(array('guid' => $user_info['guid']))->save($data);
                }

                // 返回JSON数组
                $data = array('guid'      => $user_info['guid'],
                              'user_guid' => $user_info['user_guid'],
                              'is_active' => $user_info['is_active'],
                              'token'     => $token['token'],
                              'token_num' => 0
                );
                $this->output_data($data);
            }
        }
    }

    /**
     * 登录生成token
     * @param $user_guid
     * @param $imei
     * @param int $device_type
     * @return bool|string
     * CT: 2015-04-15 11:00 by YLX
     */
    private function _get_token($user_guid, $imei, $device_type = 0)
    {
        if(empty($user_guid) || empty($imei)) return false;

        //生成新的token
        $time = time();
        $token = md5($user_guid. $imei . strval($time) . strval(rand(0,999999)));

        $model_device = D('UserDevice');
        // 用户设备状态设为不在线
//        $model_device->logoutAll($user_guid);
        $model_device->where(array('user_guid'=>$user_guid, 'status'=>'1'))
            ->save(array('status'=>'0', 'updated_at' => time()));

        // 检查设备是否存在
        $condition = array('imei' => $imei,'user_guid'=>$user_guid);
        $res = $model_device->where($condition)->find();
        // 根据检查结果, 进行更新或新增操作
        if (!empty($res)) {
            $data['status'] = 1;
            $data['token'] = $token;
            $data['token_num'] = 0;
            $data['login_num'] = intval($res['login_num']) + 1;
//            switch($device_type){
//                case 0:
//                    $data['other_num'] = intval($res['other_num']) + 1;
//                    break;
//                case 1:
//                    $data['android_num'] = intval($res['android_num']) + 1;
//                    break;
//                case 2:
//                    $data['ios_num'] = intval($res['ios_num']) + 1;
//                    break;
//                case 3:
//                    $data['pc_num'] = intval($res['pc_num']) + 1;
//                    break;
//                case 3:
//                    $data['web_num'] = intval($res['web_num']) + 1;
//                    break;
//            }

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
//            switch($device_type){
//                case 0:
//                    $data['other_num'] = intval($res['other_num']) + 1;
//                    break;
//                case 1:
//                    $data['android_num'] = intval($res['android_num']) + 1;
//                    break;
//                case 2:
//                    $data['ios_num'] = intval($res['ios_num']) + 1;
//                    break;
//                case 3:
//                    $data['pc_num'] = intval($res['pc_num']) + 1;
//                    break;
//                case 3:
//                    $data['web_num'] = intval($res['web_num']) + 1;
//                    break;
//            }
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
