<?php
namespace Common\Model;
use Common\Model\BaseModel;
use Think\Model;

class GradeAuthorityModel extends BaseModel 
{
    /**
     * 是否批量显示验证信息
     * CT: 2014-12-03 16:14 by QXL
     */
    protected $patchValidate = true;
    
    /**
     * 用户模型自动完成
     * 
     * CT: 2014-12-03 16:14 by QXL
     */
    protected $_auto = array (
            array('updated_at','time', self::MODEL_BOTH, 'function'), // 对updated_at字段在更新的时候写入当前时间戳
            array('created_at','time', self::MODEL_INSERT, 'function'), // 对updated_at字段在更新的时候写入当前时间戳
    );

    /**
     * 用户模型验证条件
     * 
     * CT: 2014-12-03 16:14 by QXL
     */
    protected $_validate = array(
            // form验证
            array('name','require','权限名称必须填写!', self::EXISTS_VALIDATE), 
//             array('name','','权限名称已经存在!',self::EXISTS_VALIDATE,'unique',1), 
            array('key','require','权限标识必须填写!', self::EXISTS_VALIDATE),
    );
}