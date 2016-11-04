<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 意见反馈表  模型
 *
 * @author wangleiming<wangleiming@yunmai365.com> 
 **/
class OpinionModel extends BaseModel{

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

        array('email','require','{%l_ym_model_option_email_not_empty}', self::EXISTS_VALIDATE),
        array('email','check_format','{%l_ym_model_option_email_format_error}', self::EXISTS_VALIDATE, 'callback'),

        array('content','require','{%l_ym_model_option_conent_not_empty}', self::EXISTS_VALIDATE),
    );

    public function check_format(){
        return preg_match('/^([\w\.\_]{2,10})@(\w{1,}).([a-z]{2,4})$/i', I('post.email')); 
    }

}

