<?php
namespace Common\Model;
use Common\Model\BaseModel;
class BalanceRecordModel extends BaseModel
{
    protected $patchValidate = true;

    protected $_auto = array (
        array('updated_time','time', self::MODEL_BOTH, 'function'),
        array('created_time','time', self::MODEL_INSERT, 'function')
    );

    protected $_validate = array(
        array('account_guid', 'require', '必须填写!', self::EXISTS_VALIDATE),
        array('account_guid', 32, '格式错误！', self::EXISTS_VALIDATE, 'length'),

        array('creater_guid', 'require', '必须填写!', self::EXISTS_VALIDATE),
        array('creater_guid', 32, '格式错误！', self::EXISTS_VALIDATE, 'length'),

        array('balance', 'require', '必须填写!', self::EXISTS_VALIDATE),
    );

}
