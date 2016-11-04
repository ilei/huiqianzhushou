<?php
namespace Common\Model;
use Common\Model\BaseModel;

/**
 * 主办方信息表 
 * 
 * CT: 2015-07-15 16:40 by wangleiming 
 **/

class OrganizerInfoModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 主题模型验证条件
     *
     * wangleiming<wangleiming@yunmai365.com> 
     */
    protected $_validate = array(
            
        array('guid','require','{%l_ym_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%l_ym_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('user_guid','require','{%l_ym_model_act_userguid_not_empty}', self::EXISTS_VALIDATE),
        array('user_guid','32','{%l_ym_model_act_userguid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('name','require','{%l_ym_model_org_name_not_empty}', self::EXISTS_VALIDATE),
        array('name','2, 30','{%l_ym_model_org_name_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('mobile','require','{%l_ym_model_mobile_not_empty}', self::EXISTS_VALIDATE),
        array('mobile','mobile_format','{%l_ym_model_mobile_format_error}', self::EXISTS_VALIDATE, 'callback'),
    
    );

    public function mobile_format(){
        return preg_match('/^1[34578]{1}\d{9}$/', I('post.mobile')); 
    }
}
