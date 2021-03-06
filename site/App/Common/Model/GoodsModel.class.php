<?php
namespace Common\Model;
use Common\Model\BaseModel;

class GoodsModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }

    protected $_validate = array(
        array('name',  'require', '商品名称必须填写!',           self::EXISTS_VALIDATE),
        array('name',  '2,30',    '商品名称必须为2到30个字符！', self::EXISTS_VALIDATE,  'length',  self::MODEL_BOTH),
        array('seller_guid', 'require', '卖家GUID必须填写!'),

    );

}
