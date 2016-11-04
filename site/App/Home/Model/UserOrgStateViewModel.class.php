<?php
namespace Home\Model;
use Think\Model\ViewModel;
/**
 * 视图层拼接社团信息等关联Model
 *
 * CT: 2014-12-24 16:50 by QXL
 */
class UserOrgStateViewModel extends ViewModel{
		public $viewFields = array(
			'UserOrgState'=>array(
			                 'guid',
                             'user_guid',
			                 'org_guid',
			                 'status',
			                 'type'
			),
			'User'=>array('guid'=>'user_guid','email', 'mobile', 'real_name', '_on'=>'UserOrgState.user_guid=User.guid')
		);

}
?>