<?php
namespace Admin\Model;
use Think\Model\ViewModel;
/**
 * 视图层拼接用户信息等关联Model
 *
 * CT: 2014-12-04 15:00 by QXL
 * UT: 2015-08-06 14:52 by QY
 */
class UserViewModel extends ViewModel{
		public $viewFields = array(
			'User'=>array(
			                         'guid',
			                         'email',
			                         'mobile',
			                         'vip',
			                         'moblie_verify',
			                         'email_verify',
			                         'realname_verify',
			                         'created_at',
			                         'updated_at',
			                         'is_del',
			                         'is_lock',
                                     'is_active',
			                         'sort'

			),
            'UserAttrInfo'=>array(
                'nickname',
                'photo',
                'realname',
                '_on'=>'UserAttrInfo.user_guid=User.guid', '_type'=>'LEFT'),
 		    //'OrgAuthentication'=>array('status','legal_p_name','legal_p_phone','legal_p_card','legal_p_card_r','yingye','zuzhi','zuzhi_pic','zuzhi_name','type','qiye_name','qiye_pic','_on'=>'OrgAuthentication.org_guid=Org.guid', '_type'=>'LEFT'),
		    'GradeLevel'=>array('name'=>'vip_name','_on'=>'User.vip=GradeLevel.guid','_type'=>'LEFT')
            //'UserAttrAuth'=>array('name'=>'vip_name','_on'=>'UserAttrAuth.user_guid=User.guid','_type'=>'LEFT'),
		);

//	public function getOrgList(){
//		return $this->where(array('is_del'=>'0'))->order('created_at DESC')->select();
//	}

	public function getSelectUserList($keyword){
		$map['is_del']  = 0;
		$map['realname']  = array('like','%'."$keyword".'%');
		return $this->where($map)->order('created_at DESC')->select();
	}
	
	public function getOrgData($option){
	    return $this->where($option)->find();
	}
}
?>