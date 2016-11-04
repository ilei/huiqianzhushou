<?php
namespace Mobile\Model;
use Think\Model\ViewModel;
/**
 * 视图层拼接用户等级信息等关联Model
 *
 * CT: 2015-03-17 12:00 by RTH
 */
class UserViewModel extends ViewModel{
    public $viewFields = array(
        'User'=>array( 'guid', 'real_name','photo','created_at','_type'=>'LEFT'),
        'UserAttribute'=>array('exp','level','user_guid','_on'=>'User.guid=UserAttribute.user_guid')
    );

}
?>