<?php
/**
 * Created by PhpStorm.
 * User: T430
 * Date: 2014/11/12
 * Time: 15:37
 *
 * 主要针对手机APP端的接口基控制器
 */

namespace Api\Controller\App;
use Api\Controller\BaseController;

class BaseUserController extends BaseController {

    protected $_request_params = array();

    protected $user_info = array();

    public function __construct() {
        parent::__construct();

        // 检查用户在线状态
        $this->check_token();
    }
    
    /**
     * 检查用户token
     * CT: 2014-11-13 10:20 by YLX
     */
    public function check_token()
    {
        $this->_request_params = $params = I($this->_method.'.');
        $headers = get_request_headers();
        $token = $headers['Token'];
        $token_num = $headers['Tokennum'];
        if(!isset($token)) {
            $token = $params['Token'];
        }
        if(!isset($token_num)) {
            $token_num = $params['Tokennum'];
        }
        if(!isset($token) || !isset($token_num)) {
            $this->output_error('10023', 'token not exist.');
        }

        $this->_request_params['Token'] = $token;
        $this->_request_params['Tokennum'] = $token_num;

        $token_expire = C('TOKEN_EXPIRE');//token时限
        // 检查token信息是否生成
        $token_info = D('UserDevice')->where(array('token' => $token,'status' =>1))->find();
        if(empty($token_info)) {
            $this->output_error('10023', 'please relogin.1');
        }

        // 检查token是否超时, 限制时间为1天
        if(time() > $token_info['last_login']+$token_expire){
            $this->output_error('10023', 'please relogin.2');
        }

        $user_info = M('UserAttrInfo')->where(array('user_guid' => $token_info['user_guid']))->find();
        if(!$user_info){
            $user_info = M('User')->where(array('guid' => $token_info['user_guid']))->find();
        }

        //更新token_num
        $new_token_num = $token_info['token_num']+1;
        $data['token_num'] = $new_token_num;
        $data['updated_at'] = time();
        D('UserDevice')->where(array('guid' => $token_info['guid']))->data($data)->save();

        //用户不存在
        if(!$user_info){
            $this->output_error('10023','please relogin.3');
        }else{
//            $this->output_data($user_info);
            $this->user_info = $user_info;
            return true;
        }
    }

} 