<?php
namespace Common\Model;
use Common\Model\BaseModel;

class UserAccountModel extends BaseModel
{

    public function __construct(){
        parent::__construct();
    }

    protected $_validate = array(
        array('guid','require','{%k_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%k_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('account_guid', 'require', '必须填写!', self::EXISTS_VALIDATE),
        array('account_guid', 32, '格式错误！', self::EXISTS_VALIDATE, 'length'),

        array('creater_guid', 'require', '必须填写!', self::EXISTS_VALIDATE),
        array('creater_guid', 32, '格式错误！', self::EXISTS_VALIDATE, 'length'),

    );

}
