<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * 社团账号充值记录 (类似于订单表) 
 * 
 * CT: 2015-07-15 16:40 by wangleiming 
 */
class RechargeRecordModel extends BaseModel
{
	protected $patchValidate = true;
	
	/**
	 * 自动完成
	 *
     * CT: 2014-12-04 16:40 by ylx
	 */
	protected $_auto = array (
			
			array('updated_time','time', self::MODEL_BOTH, 'function'), // 对updated_at字段在更新的时候写入当前时间戳
			array('created_time','time', self::MODEL_INSERT, 'function') // 对updated_at字段在更新的时候写入当前时间戳
			
	);
	
	/**
	 * 验证条件
	 *
     * CT: 2014-12-04 16:40 by ylx
	*/
	protected $_validate = array(
			array('account_guid', 'require', '必须填写!', self::EXISTS_VALIDATE),
			array('account_guid', 32, '格式错误！', self::EXISTS_VALIDATE, 'length'),

			array('creater_guid', 'require', '必须填写!', self::EXISTS_VALIDATE),
			array('creater_guid', 32, '格式错误！', self::EXISTS_VALIDATE, 'length'),
	
			array('money', 'require', '必须填写!', self::EXISTS_VALIDATE),
	);

}