<?php
namespace Common\Model;

class ActivityFormOptionModel extends BaseModel{

    public function __construct(){
        parent::__construct();
    }

    protected $_validate = array(

        array('guid','require','{%k_model_guid_not_empty}', self::EXISTS_VALIDATE),
        array('guid','32','{%k_model_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('activity_guid','require','{%k_model_actform_act_guid_not_empty}', self::EXISTS_VALIDATE),
        array('activity_guid','32','{%k_model_actform_act_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('build_guid','require','{%k_model_actformoption_build_guid_not_empty}', self::EXISTS_VALIDATE),
        array('build_guid','32','{%k_model_actformoption_build_guid_len_error}', self::EXISTS_VALIDATE, 'length'),

        array('value','require','{%k_model_actform_name_not_empty}', self::EXISTS_VALIDATE),
        array('value','2, 10','{%k_model_actform_name_len_error}', self::EXISTS_VALIDATE, 'length'),
    );


}
