<?php
namespace Common\Model;
use Common\Model\BaseModel;
use Think\Model;

class SigninUserModel extends BaseModel
{
    public function __construct(){
        parent::__construct();
    }
    protected $_validate = array(

        array('guid','require','{%k_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%k_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('user_guid','require','{%k_model_act_userguid_not_empty}', self::EXISTS_VALIDATE),
        array('user_guid','32','{%k_model_act_userguid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('username','require','{%k_model_signin_username_not_empty}', self::EXISTS_VALIDATE),
        array('username','2,10','{%k_model_signin_username_len_error}', self::EXISTS_VALIDATE, 'length'),
    );


    public function getUserInfo($uid)
    {
        return $this->where(array('guid'=>$uid))->find();
    }

}
