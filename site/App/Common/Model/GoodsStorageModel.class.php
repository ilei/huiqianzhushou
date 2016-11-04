<?php
namespace Common\Model;
use Common\Model\BaseModel;

class GoodsStorageModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }
    protected $patchValidate = true;

    protected $_validate = array(

        array('goods_guid',  'require', '商品GUID必须填写!',           self::EXISTS_VALIDATE),

    );

    protected $_auto = array (
        array('updated_time', 'time', self::MODEL_BOTH,   'function'), 
        array('created_time', 'time', self::MODEL_INSERT, 'function'),
    );
}
