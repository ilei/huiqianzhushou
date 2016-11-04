<?php
namespace Admin\Model;
use Think\Model\ViewModel;
/**
 * 视图层拼接权限信息等关联Model
 *
 * CT: 2014-12-04 15:00 by QXL
 */
class GradeValueViewModel extends ViewModel{
		public $viewFields = array(
			'GradeValue'=>array('value'),
			'GradeLevel'=>array('name'=>'level_name','sort','_on'=>'GradeValue.level_guid=GradeLevel.guid'),
		    'GradeAuthority'=>array('name'=>'auth_name','key'=>'auth_key','_on'=>'GradeValue.authority_guid=GradeAuthority.guid'),
		);
		
		public function get_data_value_list(){
		    return $this->select();
		}
}
?>