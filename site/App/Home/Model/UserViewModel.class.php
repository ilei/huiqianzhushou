<?php
namespace Home\Model;
use Think\Model\ViewModel;
/**
 * 视图层拼接社团信息等关联Model
 *
 * CT: 2014-12-24 16:50 by QXL
 */
class UserViewModel extends ViewModel{
		public $viewFields = array(
			'User'=>array(
                             'guid',
                             'email',
			                 'mobile',
			                 'real_name',
			                 'photo',
			                 'remark',
							 '_type'=>'LEFT'
			),
			'UserCompany'=>array('_type'=>'LEFT','name'=>'company_name','position','_on'=>'User.guid=UserCompany.user_guid'),
		    'Area'=>array('name'=>'area','_on'=>'Area.id=User.areaid_1')
		);

}
?>