<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * 主题 模型
 * 
 * CT: 2014-09-24 09:26 by RTH
 **/
class ActivitySubjectModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }
    /**
     * 主题模型验证条件
     *
     * CT: 2014-09-24 09:26 by RTH
     * UT: 2014-11-19 16:26 by ylx
     */
    protected $_validate = array(
            
        array('guid','require','{%l_ym_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%l_ym_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('name','require','{%l_ym_model_actsubject_name_not_empty}', self::EXISTS_VALIDATE),
        array('name','2, 10','{%l_ym_model_actsubject_name_len_error}', self::EXISTS_VALIDATE, 'length'),
    );

}
