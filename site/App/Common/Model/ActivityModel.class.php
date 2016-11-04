<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ActivityModel extends BaseModel
{

    public function __construct(){
        parent::__construct();
    }
    protected $_validate = array(

        array('guid','require','{%k_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%k_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('user_guid','require','{%k_model_act_userguid_not_empty}', self::EXISTS_VALIDATE),
        array('user_guid','32','{%k_model_act_userguid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('name','require','{%k_model_act_name_not_empty}', self::EXISTS_VALIDATE),
        array('name','2, 50','{%k_model_act_name_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('content','require','{%k_model_act_content_not_empty}', self::EXISTS_VALIDATE),
        array('content','2, 10000','{%k_model_act_content_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('start_time','require','{%k_model_act_starttime_not_empty}', self::EXISTS_VALIDATE),   
        array('end_time','require','{%k_model_act_endtime_not_empty}', self::EXISTS_VALIDATE),   
        array('start_time','check_end_time','{%k_model_act_starttime_lt_end}', self::EXISTS_VALIDATE, 'callback'),

        array('areaid_1','require','{%k_model_act_province_not_empty}', self::EXISTS_VALIDATE),
        array('areaid_2','require','{%k_model_act_city_not_empty}', self::EXISTS_VALIDATE),
        array('address','require','{%k_model_act_address_not_empty}', self::EXISTS_VALIDATE),
        array('lat','require','{%k_model_act_lat_not_empty}', self::EXISTS_VALIDATE),
        array('lng','require','{%k_model_act_lng_not_empty}', self::EXISTS_VALIDATE),
    );

    protected function check_start_time(){
        return intval(strtotime(I('post.start_time'))) + 60 > time();
    }

    protected function check_end_time(){
        return intval(strtotime(I('post.start_time'))) < intval(strtotime(I('post.end_time')));
    }

    public function getSignupFullAddress($aid)
    {
        $result = $this->where(array('activity_guid' => $aid))->field('areaid_1_name, areaid_2_name, address')->find();
        return $result['areaid_1_name'].' '.$result['areaid_2_name'].' '. $result['address'];
    }

    public function getInfo($condition)
    {
        return $this->find_one($condition);
    }

    public function getCurrentActivity($type = null, $fields = '*', $getOneField = false)
    {
        $time = time();
        $where = array(
            'is_del' => 0,
            'start_time' => array('elt', $time),
            'end_time' => array('egt', $time)
        );
        if(!is_null($type)) {
            $where['type'] = $type;
        }
        if($getOneField){
            return $this->where($where)->order('start_time desc')->getField($fields, true);
        } else {
            return $this->fields($fields)->where($where)->select();
        }
    }

    public function getConditionNum($condition)
    {
        return $this->where($condition)->count();
    }
}
