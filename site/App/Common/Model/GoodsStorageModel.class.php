<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * 商品模型
 */

class GoodsStorageModel extends BaseModel{

	public function __construct(){
		parent::__construct();
	}
    /**
     * 是否批量显示验证信息
     */

    protected $patchValidate = true;

    /**
     * 模型验证条件
     */

    protected $_validate = array(

        array('goods_guid',  'require', '商品GUID必须填写!',           self::EXISTS_VALIDATE),

	);
   
	/**
     * 用户模型自动完成
     * 
     */

    protected $_auto = array (
        array('updated_time', 'time', self::MODEL_BOTH,   'function'), // 对updated_at字段在更新的时候写入当前时间戳
        array('created_time', 'time', self::MODEL_INSERT, 'function'), // 对updated_at字段在更新的时候写入当前时间戳
    );


}