<?php
namespace Home\Model;
use Think\Model\ViewModel;
/**
 * 视图层拼接黑名单列表等关联Model
 *
 * CT: 2015-01-12 10:25 by QXL
 */
class OrgBlacklistViewModel extends ViewModel{
		public $viewFields = array(
			'OrgBlacklist'=>array(
			                 'guid',
                             'user_guid',
			                 'org_guid',
			                 'type'
			),
			'User'=>array('email', 'mobile', 'real_name', '_on'=>'OrgBlacklist.user_guid=User.guid')
		);

}
?>