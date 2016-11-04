<?php
namespace Common\Model;
use Common\Model\BaseModel;

class ActivitySubjectModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }
    protected $_validate = array(
            
        array('guid','require','{%k_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%k_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('name','require','{%k_model_actsubject_name_not_empty}', self::EXISTS_VALIDATE),
        array('name','2, 10','{%k_model_actsubject_name_len_error}', self::EXISTS_VALIDATE, 'length'),
    );

}
