<?php
namespace Common\Model;
use Common\Model\BaseModel;
class OrganizerInfoModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }

    protected $_validate = array(
        array('guid','require','{%k_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%k_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('user_guid','require','{%k_model_act_userguid_not_empty}', self::EXISTS_VALIDATE),
        array('user_guid','32','{%k_model_act_userguid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('name','require','{%k_model_org_name_not_empty}', self::EXISTS_VALIDATE),
        array('name','2, 30','{%k_model_org_name_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('mobile','require','{%k_model_mobile_not_empty}', self::EXISTS_VALIDATE),
        array('mobile','mobile_format','{%k_model_mobile_format_error}', self::EXISTS_VALIDATE, 'callback'),

    );

    public function mobile_format(){
        return preg_match('/^1[34578]{1}\d{9}$/', I('post.mobile')); 
    }
}
