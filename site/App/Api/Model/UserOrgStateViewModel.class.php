<?php
namespace Api\Model;
use Think\Model\ViewModel;
/**
 * 视图层拼接社团信息等关联Model
 *
 * CT: 2014-12-29 10:50 by QXL
 */
class UserOrgStateViewModel extends ViewModel{
		public $viewFields = array(
			'UserOrgState' => array(
				'org_guid',
				'is_del',
				'status',
				'type',
				'source',
				'created_at',
				'updated_at'
			),
			'Org' => array('name', 'logo', 'description', '_on' => 'UserOrgState.org_guid=Org.guid'),
		);
}
?>