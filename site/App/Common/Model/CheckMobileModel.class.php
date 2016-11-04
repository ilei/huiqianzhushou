<?php
namespace Common\Model;
use Common\Model\BaseModel;

class CheckMobileModel extends BaseModel
{
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

    public function recordSendNum($mobile, $type)
    {
        $today = date('Ymd', time());
        $cache_key = $mobile.'::'.$today.'::'.$type;
        $current_num = S($cache_key);
        $current_num = !empty($current_num) ? $current_num : 0;
        S($cache_key, $current_num+1, 86400);
    }

    public function checkSendNum($mobile, $type){
        $today = date('Ymd', time());
        $send_num = S($mobile.'::'.$today.'::'.$type);
        $max_num = C('MAX_SMS_NUM', null, 5);
        return  $send_num >= $max_num ? false : true;
    }

}
