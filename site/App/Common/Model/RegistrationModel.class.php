<?php
namespace Common\Model;

use Common\Model\BaseModel;
/**
 * Created by PhpStorm.
 * User: RTH
 * Date: 2015/10/19
 * Time: 12:19
 */

class RegistrationCompanyModel extends BaseModel
{
    /**
     * 用户模型验证条件
     *
     * CT: 2014-09-07 15:00 by YLX
     */
    protected $_validate = array(

        // 参数有效性验证
//        array('name', 'require', '公司名称必须填写!', self::EXISTS_VALIDATE, '', 365),
//        array('position', 'require', '职位必须填写!', self::EXISTS_VALIDATE, '', 365)

    );

}