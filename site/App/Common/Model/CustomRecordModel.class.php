<?php
namespace Common\Model;
use Common\Model\BaseModel;

class CustomRecordModel extends BaseModel
{
    protected $patchValidate = true;

    protected $_auto = array (
        array('updated_time','time', self::MODEL_BOTH, 'function'),
        array('created_time','time', self::MODEL_INSERT, 'function') 
    );

    protected $_validate = array(
        array('goods_guid', 'require', '必须填写!', self::EXISTS_VALIDATE),
        array('goods_guid', 32, '格式错误！', self::EXISTS_VALIDATE, 'length'),
        array('nums', 'require', '必须填写!', self::EXISTS_VALIDATE),
    );

}
