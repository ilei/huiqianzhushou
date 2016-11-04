<?php
namespace Signin\Controller;

/**
 * 注册登陆
 * CT: 2015-07-09 15:00 by YLX
 */
class AuthController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 社团帐号登录
     * CT: 2015-07-09 15:00 by YLX
     */
    public function login()
    {
        if(IS_POST) {
            //获取帐号参数
            $username = trim(I('post.username'));
            $password = md5(I('post.password'));
            $login_verify_code = I('post.login_verify_code');
            //获取设备唯一ID
            $user_device_uniqueId = create_guid();
            cookie(C('user_device_uniqueid_name'), $user_device_uniqueId);
            // 检查所获参数的有效性
            if (empty($username) || empty($password)) {
                $this->error(L('_PARAM_ERROR_'));
            }
            $model_user = D('User');
            $user      = M('SigninUser');
            $user_verify_res = $model_user->where(array('login_verify_code' => $login_verify_code))->find();
            if(empty($user_verify_res)){
                $this->error(L('_USERNAME_OR_PASSWORD_ERROR_'));
            }
            $user_info = $user->where(array('username'  => $username, 'password' => $password,
                                            'is_active' => '1', 'user_guid' => $user_verify_res['guid'], 'is_del' => '0'))->find();
            if (!$user_info) {
                $domain = strstr($username, '@');
                if(empty($domain)){
                    //超级管理员
                    $where     = "mobile='$username' and moblie_verify = '1' and login_verify_code = '$login_verify_code' and password='{$password}' and is_del='0'";
                }else{
                    //超级管理员
                    $where     = "email='$username' and email_verify = '1' and login_verify_code = '$login_verify_code' and password='{$password}' and is_del='0'";
                }
                $user_info = $model_user->where($where)->find();
                // echo $model_user->getLastSQL();die();
                if (!$user_info) {
                    // '用户名不存在' '密码输入有误'
                    $this->error(L('_USERNAME_OR_PASSWORD_ERROR_'));
                }else{
                    //存储或更新user_device表
                    $token = $this->_get_token($user_info['guid'], $user_device_uniqueId);
                    if (empty($token)) {
                        $this->error(L('_USER_PASSWORD_ERROR_'));
                    } else {
                        // $time = time();
                        // //登陆次数+1，最后登陆时间更新
                        // $data['login_num']     = intval($user_info['login_num']) + 1;
                        // $data['last_login_at'] = $time;
                        // $user->where(array('guid' => $user_info['guid']))->save($data);

//                        S($token . ':user_info', $user_info, C('TOKEN_EXPIRE'));    //设置Redis缓存
//                        header('Token:' . $token);
                        // 返回JSON数组
                        $data = array('guid'      => $user_info['guid'],//用户guid
                                      'username'  => $user_info['email'],
                                      'user_guid'  => $user_info['guid'],
                                      'is_active' => $user_info['is_active'],
                                      'token'     => $token,
                                      'token_num' => 0,
                                      'type' => 1 //账号登录类型，1为超级账号
                        );
                        session(C('auth_session_name'), $data);
                        $this->redirect('Index/index');
                    }
                }

            } else {
                //存储或更新user_device表
                $token = $this->_get_token($user_info['guid'], $user_device_uniqueId);

                if (empty($token)) {
                    $this->error(L('_LOGIN_ERROR_'));
                } else {
                    $time = time();
                    //登陆次数+1，最后登陆时间更新
                    $data['login_num']     = intval($user_info['login_num']) + 1;
                    $data['last_login_at'] = $time;
                    $user->where(array('guid' => $user_info['guid']))->save($data);

//                    S($token . ':user_info', $user_info, C('TOKEN_EXPIRE'));    //设置Redis缓存
//                    header('Token:' . $token);
                    // 返回JSON数组
                    $data = array('guid'      => $user_info['guid'],
                                  'username'  => $user_info['username'],
                                  'user_guid'  => $user_info['user_guid'],//用户guid
                                  // 'username'  => $user_info['username'],
                                  'is_active' => $user_info['is_active'],
                                  'token'     => $token,
                                  'token_num' => 0,
                                  'type' => 0 //账号登录类型，0为普通账号
                    );
                    session(C('auth_session_name'), $data);
                    $this->redirect('Index/index');
                }
            }
        }
        $this->display();
    }

    /**
     * 登录生成token
     * @param $user_guid
     * @param $imei
     * @return bool|string
     * CT: 2015-04-15 11:00 by YLX
     */
    private function _get_token($user_guid, $imei)
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
//            S($res['token'].'user_info', null); //清除Redis缓存
//            S($res['token'].'user_device', null);

            $data = array(
                'status'        => 1,
                'login_num'     => intval($res['login_num']) + 1,
                'token'         => $token,
                'token_num'     => 0
            );
            $result = $m->editTokenInfo($condition, $data);
        } else {
            $data = array(
                'user_guid'     => $user_guid,
                'type'          => 2,
                'imei'          => $imei,
                'device_type'   => C('USER_DEVICE_TYPE.web', null, 6),
                'status'        => 1,
                'login_num'     => 1,
                'token'         => $token,
                'token_num'     => 0
            );
            $result = $m->addTokenInfo($data);
        }

        if($result) {
            $token_redis = $m->where($condition)->find();
//            S($token.':user_device', $token_redis, C('TOKEN_EXPIRE'));
            return $token;
        } else {
            return false;
        }
    }

    /**
     * 社团帐号退出登录
     *
     * CT: 2014-09-15 15:00 by YLX
     */
    public function logout()
    {
        $auth = $this->kookeg_auth_data();
        if($auth){
            $this->empty_auth_session();
            $this->redirect( 'Auth/login');
        }else{
            $this->error(L('_NO_LOGIN_'), U('Auth/login'));
        }
    }
}
