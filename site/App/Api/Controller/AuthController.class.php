<?php
namespace Api\Controller;

use Api\Controller\BaseController;
use Org\Api\YmChat;
use Org\Api\YmSMS;

/**
 * 注册登陆
 *
 * CT: 2014-09-19 17:00 by YLX
 *
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

        $user = D('User');
        //获取帐号参数
        $username = trim(I('post.username'));
        $password = I('post.password');
        //获取相关设备参数
        $imei = I('post.imei');
        $device_type = I('post.device', 0);
        // 检查所获参数的有效性
        if (empty($username) || empty($password) || empty($imei)) {
            $this->output_error('10003');
        }

        $user_info = $user->where(array('mobile' => $username, 'type' => '1', 'is_del' => '0'))->find();
		if(!$user_info){
			$this->output_error('10001');
		}
		if(intval($user_info['is_active']) !== 1){
			$this->output_error('10041');
		}
//        var_dump($user_info['guid'],$user_info['password'] == $password,true);
        if($user_info['password'] == $password){
            //存储或更新user_device表
            $token = $this->_get_token($user_info['guid'], $imei, $device_type);
//            dump($token );die();

            if(empty($token)){
                $this->output_error('10022');
            } else {
                // 解除所有与该用户别名绑定的CID
                $this->unbindGetuiAlias($user_info['guid']);

                $time = time();
                //登陆次数+1，最后登陆时间更新
                $data['login_num'] = intval($user_info['login_num']) + 1;
                $data['last_login_at'] = $time;
                $user->where(array('guid' => $user_info['guid']))->save($data);

                S($token.':user_info', $user_info, C('TOKEN_EXPIRE'));    //设置Redis缓存
                header('Token:'.$token);
                // 返回JSON数组
                $data = array('guid'      => $user_info['guid'], 'email' => $user_info['email'],
                              'mobile'    => $user_info['mobile'],
                              'real_name' => isset($user_info['real_name']) ? $user_info['real_name'] : $user_info['email'],
                              'is_active' => $user_info['is_active'],
                              'photo'     => !empty($user_info['photo'])?$user_info['photo']:0,
                              'moblie_verify' => $user_info['moblie_verify'],
                              'token'     => $token,
                              'token_num' => 0
                );
                $this->output_data($data);
            }
        }else{
            // '用户名不存在' '密码输入有误'
            $this->output_error('10002');
        }
    }

    /**
     * 登录生成token
     * CT: 2014-11-13 11:00 by YLX
     */
    private function _get_token($user_guid, $imei, $device_type)
    {
        if(empty($user_guid) || empty($imei)) return false;

        //生成新的token
        $time = time();
        $token = md5($user_guid. $imei . strval($time) . strval(rand(0,999999)));

        $m = D('UserDevice');
        // 用户所有设备状态设为不在线
        $m->logoutAll($user_guid);
        // 检查设备是否存在
        $condition = array('imei'=>$imei, 'user_guid'=>$user_guid);
        $res = $m->field('token, login_num')->where($condition)->find();
        // 根据检查结果, 进行更新或新增操作
        if (!empty($res)) {
            $data = array(
                'status'        => 1,
                'login_num'     => intval($res['login_num']) + 1,
                'last_login'    => $time,
                'last_login_ip' => get_client_ip(),
                'updated_at'    => $time,
                'token'         => $token,
                'token_num'     => 0,
                'app_version'   => I('post.app_version')
            );
            $result = $m->editTokenInfo($condition, $data);       
        } else {
            $data = array(
                'guid'          => create_guid(),
                'user_guid'     => $user_guid,
                'imei'          => $imei,
                'device_type'   => $device_type,
                'status'        => 1,
                'login_num'     => 1,
                'last_login'    => $time,
                'last_login_ip' => get_client_ip(0),
                'created_at'    => $time,
                'updated_at'    => $time,
                'token'         => $token,
                'token_num'     => 0,
                'app_version'   => I('post.app_version')
            );
            $result = $m->addTokenInfo($data);
        }

        if($result) {
            $token_redis = $m->where($condition)->find();
            S($token.':user_device', $token_redis, C('TOKEN_EXPIRE')); //生成redis
            return $token;
        } else {
            return false;
        }
    }

    /**
     * 忘记密码操作一
     * 手机验证及手机验证码验证
     */
    public function mobile_check()
    {
        // 检查号码是否存在
        if(IS_POST) {
            $mobile = I('post.mobile');
            if(empty($mobile)) {
                $this->output_error('10003');
            }

            $check = M('User')->where(array('mobile' => $mobile))->find();
            if(!$check) {
                $this->output_error('10009', 'mobile not exist.');
            }
            $this->output_data();
        }

        // 发送验证码
        if(IS_GET) {
            $mobile = I('get.mobile');
            if(empty($mobile)) {
                $this->output_error('10003');
            }
            $sms_type = C('MOBILE_CODE_TYPE.api_forget_password');//1,手机端忘记密码
            // 检查短信发送次数
            $check_send_num = D('CheckMobile')->checkSendNum($mobile, $sms_type);
            if(!$check_send_num){
                $this->output_error('10033');
            }

            $code = get_mobile_code();
            $time = time();
            $check_data=array(
                'mobile'=>$mobile,
                'code'=>$code,
                'expired_at'=>$time + 60*30,
                'type' => $sms_type, //1,手机端忘记密码
                'created_at'=>$time,
                'updated_at'=>$time
            );
            if(M('CheckMobile')->add($check_data)){
				vendor('YmPush.VerCodeInfo');
                $sms_result = \VerCodeInfo::send(C('CODE_TYPE.api_find_password'), $mobile, array($code, 30));
                if($sms_result['code'] == '0'){
                    // 统计发送次数
                    D('CheckMobile')->recordSendNum($mobile, $sms_type);
                    $this->output_data();
                }else{
                    $this->output_error('10008');
                }
            }else{
                $this->output_error('10011');
            }
        }

        // 检查验证码是否正确
        if(IS_PUT) {
            $params = I('put.');
            $mobile = $params['mobile'];
            $code = $params['code'];
            if(empty($mobile) || empty($code)) {
                $this->output_error('10003');
            }

            $check_data = D('CheckMobile')->check_code($mobile, $code, C('MOBILE_CODE_TYPE.api_forget_password'));
            switch($check_data){
                case '31': // 验证码错误
                    $this->output_error('10031');
                    break;
                case '32': // 操作过期
                    $this->output_error('10030');
                    break;
                case 'ok':
                    $this->output_data();
                    break;
                case 'ko':
                    $this->output_error('10003');
                    break;
            }
        }
        $this->check_request_method('error');
    }

    /**
     * 忘记密码二
     * 手机验证通过, 修改密码
     */
    public function change_password() {
        $this->check_request_method('put');
        $params = I('put.');

        $new = $params['password'];
        $renew = $params['repassword'];
        $mobile = $params['mobile'];
        $code = $params['code'];
        if(empty($renew) || empty($new) || empty($mobile) || empty($code) || $renew != $new){
            $this->output_error('10003', 'params error');
        }

        //检查对应手机号和验证码是否存在
        $check_code_where = array('mobile'=>$mobile, 'code'=>$code, 'status'=>'1', 'type' => C('MOBILE_CODE_TYPE.api_forget_password'));
        $check_code = M('CheckMobile')->where($check_code_where)->find();
        if(!$check_code) $this->output_error('10003', 'code and mobile not exist');
        if($check_code['expired_at'] < time()) $this->output_error('10030');
        // 若通过则删除验证码
        M('CheckMobile')->where($check_code_where)->delete();

        // 获取用户信息
        $user_info = D('User')->find_one(array('mobile'=>$mobile));

        $ymchat = new YmChat();
        $r = $ymchat->editPassword(array('username'=>$user_info['guid'],'password'=>hashCode($user_info['password']),'newpassword'=>hashCode($new)));
        if($r['status'] != 200) {
            $this->output_error('10011', 'hx operation failed');
        }

        if(M('User')->where(array('guid'=>$user_info['guid']))->save(array('password'=>$new))){
            $this->output_data();
        }else{
            $this->output_data('10011');
        }
    }
    
    /**
     * 帐号注册
     * 
     * CT: 2014-09-19 17:00 by YLX
     */
//     public function register()
//     {
//         if (IS_POST) {
//             $user = D('User');
//             $data = array();
//             $time = time();

//             $data['guid'] = create_guid();
//             $data['email'] = trim(I('post.email'));
//             $data['password'] = trim(I('post.password'));
//             $data['repassword'] = trim(I('post.repassword'));
//             $data['mobile'] = trim(I('post.mobile'));
//             $data['agree'] = I('post.agree');
//             $data['last_login_at'] = $time;
//             $data['login_num'] = '1';
//             $data['is_active'] = '1';
//             $data['type'] = '22';
            
//             $result = $user->create($data);
//             if (!$result){
//                 $this->error($user->getError());
//             }else{
//                 if($user->add()){
//                     $auth = array(
//                             'user_guid' => $data['guid'],
//                             'user_email' => $data['email'],
//                             'user_mobile' => $data['mobile'],
//                     );
//                     session('auth', $auth);
//                     cookie('user_guid', $data['guid']);
//                     $this->success('注册成功', U('Index/index'));
//                 }else{
//                     $this->error('服务器忙, 请稍候重试', U('Auth/register'));
//                 }
//                 exit();
//             }
//         }
        
//         $this->assign('meta_title', '注册');
//         $this->display();
//     }
    
    /**
     * 注册时相关ajax检查
     * 
     * CT: 2014-09-19 17:00 by YLX
     */
    public function check()
    {
        $type = I('get.type');
        
        switch ($type){
        	case 'email':
        	    $email = I('post.email');
        	    $res = D('User')->getFieldByEmail($email, 'guid');
        	    echo empty($res)?'true':'false';
        	    break;
        	case 'mobile':
        	    $mobile = I('post.mobile');
        	    $res = D('User')->getFieldByMobile($mobile, 'guid');
        	    echo empty($res)?'true':'false';
        	    break;
        	default:
        	    $this->ajaxResponse(array('status' => 'ko', 'msg'=>'参数不对.'));
        	    break;
        }
        exit;
    }
    
}
