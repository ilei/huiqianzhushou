<?php
namespace Common\Model;
/**
 * 手机验证码 模型
 *
 * CT 2015-04-10 17:39 by ylx
 */
class CheckMobileModel extends BaseModel
{
    /**
     * 验证手机验证码
     * @param $mobile 手机
     * @param $code 验证码
     * @param int $type 验证类型, 短信验证码类型, 1忘记密码, 2会员手机验证, 3会员网站找回密码, 4会员邀请注册
     * @return mixd
     * CT 2015-04-10 17:39 by ylx
     */
    public function check_code($mobile, $code, $type = 1)
    {
        if(empty($mobile) || empty($code)) {
            return 'ok';
        }
        $check_data = $this->where(array('mobile'=>$mobile, 'status' => '0', 'code' => $code, 'type' => $type))
            ->order('created_at DESC')->find();
        if(empty($check_data)) {
           return '31';
        }

        if($check_data['expired_at'] > time()){
            $this->where(array('mobile'=>$mobile, 'code'=>$code, 'type' => $type))->save(array('status' => 1, 'updated_at' => time()));
            return 'ok';
        }else{
            return '30';
        }
    }

    /**
     * 记录短信发送次数 通过 redis
     * @param $mobile
     * @param int $type 验证类型, 短信验证码类型, 1忘记密码, 2会员手机验证, 3会员网站找回密码, 4会员邀请注册
     */
    public function recordSendNum($mobile, $type)
    {
        $today = date('Ymd', time());
        $cache_key = $mobile.'::'.$today.'::'.$type;
        $current_num = S($cache_key);
        $current_num = !empty($current_num) ? $current_num : 0;
        S($cache_key, $current_num+1, 86400);
    }

    /**
     * 检查手机验证码发送次数是否超出限制
     * @param $mobile   手机号
     * @param int $type 验证类型, 短信验证码类型, 1忘记密码, 2会员手机验证, 3会员网站找回密码, 4会员邀请注册
     * @return bool
     */
    public function checkSendNum($mobile, $type){
        $today = date('Ymd', time());
        $send_num = S($mobile.'::'.$today.'::'.$type);
        $max_num = C('MAX_SMS_NUM', null, 5);
        return  $send_num >= $max_num ? false : true;
    }

}