<?php
namespace Common\Model;
use Common\Model\BaseModel;
use Think\Model;

/**
 * 签到帐号模型
 * CT: 2015-04-15 15:00 by YLX
 */
class SigninUserModel extends BaseModel
{
    public function __construct(){
        parent::__construct();
    }
    /**
     * 用户模型验证条件
     * 
     * CT: 2015-04-15 15:00 by YLX
     */
    protected $_validate = array(

        array('guid','require','{%l_ym_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%l_ym_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('user_guid','require','{%l_ym_model_act_userguid_not_empty}', self::EXISTS_VALIDATE),
        array('user_guid','32','{%l_ym_model_act_userguid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('username','require','{%l_ym_model_signin_username_not_empty}', self::EXISTS_VALIDATE),
        array('username','2,10','{%l_ym_model_signin_username_len_error}', self::EXISTS_VALIDATE, 'length'),
    );


    /**
     * 获取用户详细信息
     * @param $uid 用户guid
     * @return mixed
     * CT: 2015-04-15 15:00 by YLX
     */
    public function getUserInfo($uid)
    {
        return $this->where(array('guid'=>$uid))->find();
    }

}
