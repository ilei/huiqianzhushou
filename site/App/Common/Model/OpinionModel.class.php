<?php
namespace Common\Model;
use Common\Model\BaseModel;
class OpinionModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }

    protected $_validate = array(

        array('guid','require','{%k_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%k_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('email','require','{%k_model_option_email_not_empty}', self::EXISTS_VALIDATE),
        array('email','check_format','{%k_model_option_email_format_error}', self::EXISTS_VALIDATE, 'callback'),

        array('content','require','{%k_model_option_conent_not_empty}', self::EXISTS_VALIDATE),
    );

    public function check_format(){
        return preg_match('/^([\w\.\_]{2,10})@(\w{1,}).([a-z]{2,4})$/i', I('post.email')); 
    }
}

