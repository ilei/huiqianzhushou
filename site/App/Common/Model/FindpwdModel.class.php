<?php
/**
 * Created by PhpStorm.
 * User: RTH
 * Date: 2015/8/25
 * Time: 10:13
 */
namespace Common\Model;
use Common\Model\BaseModel;

class OrderModel extends BaseModel
{

    /**
     *
     */

    protected $patchValidate = true;

    /**
     * 
     */
    protected $_validate = array(
        // form验证
        /*array('username', 'require', '用户名必须填写!', self::EXISTS_VALIDATE),
//        array('email', 'email', '邮箱格式不对!', self::EXISTS_VALIDATE),

        array('password', 'require', '密码必须填写!', self::EXISTS_VALIDATE),
        array('password', '6,18', '密码必须为6到18位数字！', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),
        array('re_password', 'require', '确认密码必须填写!', self::EXISTS_VALIDATE, '', self::MODEL_BOTH),
        array('re_password', 'password', '确认密码与密码不一致!', 1, 'confirm', self::MODEL_BOTH),*/

    );}