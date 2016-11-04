<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * 社团账号充值记录 (类似于订单表) 
 * 
 * CT: 2015-07-15 16:40 by wangleiming 
 */
class UserAccountModel extends BaseModel
{

    public function __construct(){
        parent::__construct();
    }
            
	/**
	 * 验证条件
	 *
     * CT: 2014-12-04 16:40 by ylx
	*/
	protected $_validate = array(
            array('guid','require','{%l_ym_model_guid_not_empty}', self::EXISTS_VALIDATE),
            array('guid','32','{%l_ym_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

			array('account_guid', 'require', '必须填写!', self::EXISTS_VALIDATE),
			array('account_guid', 32, '格式错误！', self::EXISTS_VALIDATE, 'length'),

			array('creater_guid', 'require', '必须填写!', self::EXISTS_VALIDATE),
			array('creater_guid', 32, '格式错误！', self::EXISTS_VALIDATE, 'length'),
	
	);

}
