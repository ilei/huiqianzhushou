<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * 订单模型
 * @author wangleiming<wangleiming@yunmai365.com>
 */

class OrderModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }

	/**
	 * 模型验证条件
	 **/

	protected $_validate = array(
        array('guid','require','{%l_ym_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%l_ym_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

		array('order_id',  'require', '{%l_ym_model_orderid_not_empty}', self::EXISTS_VALIDATE),
		array('order_id',  '25',    '{%l_ym_model_orderid_len_error}', self::EXISTS_VALIDATE,  'length',  self::MODEL_BOTH),


		array('quantity', 'require', '商品数量必须填写!'),
		array('quantity', 'number',  '商品数量必须为数字!',       self::EXISTS_VALIDATE),

		array('goods_guid', 'require', '商品GUID必须填写!'),
		array('goods_guid',  '32',    '商品GUID必须为32个字符！', self::EXISTS_VALIDATE,  'length',  self::MODEL_BOTH),
		array('seller_guid', 'require', '卖家GUID必须填写!'),
		array('seller_guid',  '32',    '卖家GUID必须为32个字符！', self::EXISTS_VALIDATE,  'length',  self::MODEL_BOTH),
		array('buyer_guid', 'require', '买家GUID必须填写!'),
		array('buyer_guid',  '32',    '买家GUID必须为32个字符！', self::EXISTS_VALIDATE,  'length',  self::MODEL_BOTH),
	);

}
