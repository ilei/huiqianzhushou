<?php
namespace Api\Controller;

use Think\Controller\RestController;
use Org\Api\YmPush;

/**
 * API基控制器
 *
 * CT: 2014-09-19 17:00 by YLX
 *
 */
class BaseController extends RestController 
{
    // REST允许请求的资源类型列表
//    protected   $allowType      =   array('json');
    // 默认的资源类型
    protected   $defaultType    =   'json';
    
    /**
     * action前置操作
     * 
     * ct: 2014-09-24 11:15 by ylx
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 错误输出
     *
     * CT: 2014-09-19 17:00 by YLX
     * UT: 2014-09-23 14:00 by YLX
     *
     */
    public function output_error($code, $msg=null, $data=null)
    {
        $data = array('code'=>$code, 'msg'=>$msg, 'data'=>$data);
        return $this->response($data, 'json');
    }
    
    /**
     * 数据输出
     *
     * CT: 2014-09-19 17:00 by YLX
     * UT: 2014-09-23 14:00 by YLX
     */
    public function output_data($data = null,$msg=null)
    {
        $data = array('code'=>10000, 'msg'=>$msg, 'data' => $data);
        return $this->response($data, 'json');
    }

    /**
     * 检查所传的REQUEST_METHOD是否为要求的method, 若不符合则返回错误
     * @param $method
     * CT: 2014-12-12 13:40 by YLX
     */
    public function check_request_method($method) {
        if (strtolower($method) != $this->_method) {
            $this->output_error('10025', 'HTTP request method error.');
        }
    }

    /**
     * 检查手机验证码发送次数是否超出限制
     * @param $mobile   手机号
     * @param int $type 验证类型, 短信验证码类型, 1忘记密码, 2会员手机验证, 3会员网站找回密码, 4会员邀请注册
     * @return bool
     */
//    public function check_sms_num($mobile, $type){
//        $today = strtotime(today);
//        $num = M('CheckMobile')->where(array('mobile'=>$mobile, 'type' => $type, 'created_at'=>array('gt',$today)))->count();
//        $max_num = C('MAX_SMS_NUM', null, 3);
//        return $max_num <= $num ? true : false;
//    }

    /**
     * 解除所有与该用户别名绑定的CID
     * @param $guid 用户对应guid即别名
     * ct: 2015-06-18 17:15 by ylx
     */
    public function unbindGetuiAlias($guid)
    {
        vendor('getui.YmPush');
        $push = new \YmPush();
        $push->aliasUnBindAll($guid);
    }

}