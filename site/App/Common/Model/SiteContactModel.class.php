<?php
namespace Common\Model;
use Common\Model\BaseModel;

class SiteContactModel extends BaseModel
{

    protected $patchValidate = false;

    protected $_auto = array (
        array('updated_at','time', self::MODEL_BOTH, 'function'),
        array('created_at','time', self::MODEL_INSERT, 'function'),
    );
    protected $_validate = array(

        array('name','require','姓名必须填写!', self::EXISTS_VALIDATE),
        array('name','1, 50','姓名长度必须为2到50个字!', self::EXISTS_VALIDATE, 'length'),

        array('email','require','邮箱必须填写!', self::EXISTS_VALIDATE),
        array('email','email','邮箱格式不对!', self::EXISTS_VALIDATE),

        array('phone','require','电话号码必须填写!'),
        array('phone','number','电话号码必须为数字!', self::EXISTS_VALIDATE),
        array('phone','7, 20','电话号码必须为7到20位数字!', self::EXISTS_VALIDATE, 'length'),

        array('content','require','信息内容必须填写!', self::EXISTS_VALIDATE),
        array('content','1, 200','信息内容不得超过200个字!', self::EXISTS_VALIDATE, 'length'),

    );
}
