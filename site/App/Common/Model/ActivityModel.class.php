<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * 活动 模型
 * CT: 2015-08-06 10:26 by ylx
 */
class ActivityModel extends BaseModel
{

    
    public function __construct(){
        parent::__construct();
    }
    /**
     * 主题模型验证条件
     *
     * wangleiming<wangleiming@yunmai365.com> 
     */
    protected $_validate = array(
            
        array('guid','require','{%l_ym_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%l_ym_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('user_guid','require','{%l_ym_model_act_userguid_not_empty}', self::EXISTS_VALIDATE),
        array('user_guid','32','{%l_ym_model_act_userguid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('name','require','{%l_ym_model_act_name_not_empty}', self::EXISTS_VALIDATE),
        array('name','2, 50','{%l_ym_model_act_name_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('content','require','{%l_ym_model_act_content_not_empty}', self::EXISTS_VALIDATE),
        array('content','2, 10000','{%l_ym_model_act_content_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('start_time','require','{%l_ym_model_act_starttime_not_empty}', self::EXISTS_VALIDATE),   
        array('end_time','require','{%l_ym_model_act_endtime_not_empty}', self::EXISTS_VALIDATE),   
        //array('start_time','check_start_time','{%l_ym_model_act_starttime_lt_now}', self::EXISTS_VALIDATE, 'callback'),
        array('start_time','check_end_time','{%l_ym_model_act_starttime_lt_end}', self::EXISTS_VALIDATE, 'callback'),

        array('areaid_1','require','{%l_ym_model_act_province_not_empty}', self::EXISTS_VALIDATE),
        array('areaid_2','require','{%l_ym_model_act_city_not_empty}', self::EXISTS_VALIDATE),
        array('address','require','{%l_ym_model_act_address_not_empty}', self::EXISTS_VALIDATE),
        array('lat','require','{%l_ym_model_act_lat_not_empty}', self::EXISTS_VALIDATE),
        array('lng','require','{%l_ym_model_act_lng_not_empty}', self::EXISTS_VALIDATE),
    );

    /**
     * 验证方法
     *
     * CT: 2014-11-19 16:26 by ylx
     */
    protected function check_start_time(){
        return intval(strtotime(I('post.start_time'))) + 60 > time();
    }

    protected function check_end_time(){
        return intval(strtotime(I('post.start_time'))) < intval(strtotime(I('post.end_time')));
    }

    /**
     * 获取报名详细地址
     * @param $aid 活动guid
     * @return string 详细地址
     * ct: 2015-05-28 09:56 by ylx
     */
    public function getSignupFullAddress($aid)
    {
        $result = $this->where(array('activity_guid' => $aid))->field('areaid_1_name, areaid_2_name, address')->find();
        return $result['areaid_1_name'].' '.$result['areaid_2_name'].' '. $result['address'];
    }

    /**
     * 根据所给条件获取活动详情
     * @param $condition
     * @return mixed
     *  CT: 2014-11-19 14:26 by RTH
     *  UT: 2014-12-05 09:56 by ylx
     */
    public function getInfo($condition)
    {
        return $this->find_one($condition);
    }

    /**
     * 按类型获取当前时间段的所有活动
     * @param null $type 活动类型，为null时获取所有类型活动
     * @param string $fields 要获取的字段，字段名用逗号分开
     * @param bool $getOneField 为true时，$fields参数只能为一个字段
     * @return mixed
     * ct: 2015-06-10 10:38 by ylx
     */
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
