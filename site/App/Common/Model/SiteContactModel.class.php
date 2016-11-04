<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * APP官网 '联系我们' 提交 模型
 * 
 * CT: 2014-11-21 09:26 by ylx
 */
class SiteContactModel extends BaseModel
{

    /**
     * 是否批量显示验证信息
     * CT: 2014-12-16 11:13 by ylx
     */
    protected $patchValidate = false;

    /**
     * 主题模型自动完成
     * 
     * CT: 2014-12-16 10:26 by ylx
     */
    protected $_auto = array (
            array('updated_at','time', self::MODEL_BOTH, 'function'), // 对updated_at字段在新增/更新的时候写入当前时间戳
            array('created_at','time', self::MODEL_INSERT, 'function'), // 对created_at字段在新增的时候写入当前时间戳
    );

    /**
     * 主题模型验证条件
     *
     * CT: 2014-12-16 10:26 by ylx
     */
    protected $_validate = array(
            
            // CU时数据对像验证
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