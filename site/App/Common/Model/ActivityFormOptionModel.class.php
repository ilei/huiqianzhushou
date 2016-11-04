<?php
namespace Common\Model;

/**
 *  报名表单选项
 *  @author wangleiming <wangleiming@yunmai365.com>
 **/
class ActivityFormOptionModel extends BaseModel{
    
    public function __construct(){
        parent::__construct();
    }

    /**
     * 模型验证条件
     *
     * @access protected 
     * @author wangleiming<wangleiming@yunmai365.com>
     **/

    protected $_validate = array(
            
        array('guid','require','{%l_ym_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%l_ym_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('activity_guid','require','{%l_ym_model_actform_act_guid_not_empty}', self::EXISTS_VALIDATE),
        array('activity_guid','32','{%l_ym_model_actform_act_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('build_guid','require','{%l_ym_model_actformoption_build_guid_not_empty}', self::EXISTS_VALIDATE),
        array('build_guid','32','{%l_ym_model_actformoption_build_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('value','require','{%l_ym_model_actform_name_not_empty}', self::EXISTS_VALIDATE),
        array('value','2, 10','{%l_ym_model_actform_name_len_error}', self::EXISTS_VALIDATE, 'length'),
    );


}
