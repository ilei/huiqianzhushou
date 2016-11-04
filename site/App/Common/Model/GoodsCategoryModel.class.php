<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 商品类别表
 *
 * CT 2015 -08-05 13:52 by wangleiming 
 */
class GoodsCategoryModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 模型验证条件
     */

    protected $_validate = array(
        array('guid','require','{%l_ym_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%l_ym_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('name', 'require', '{%l_ym_model_goods_cate_name_not_empty}', self::EXISTS_VALIDATE),
        array('name', '2,30', '{%l_ym_model_goods_cate_name_len_error}', self::EXISTS_VALIDATE, 'length', self::MODEL_BOTH),

        array('creater_guid', 'require', '{%l_ym_model_goods_cate_creater_not_empty}', self::EXISTS_VALIDATE),
        array('creater_guid', '32', '{%l_ym_model_goods_cate_creater_len_error}', self::EXISTS_VALIDATE, 'length'),
    );

}
