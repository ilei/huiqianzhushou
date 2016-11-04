<?php
namespace Api\Model;
use Think\Model\ViewModel;
/**
 * 黑名单与社团关联Model
 *
 * CT: 2014-12-29 10:50 by QXL
 */
class OrgBlacklistViewModel extends ViewModel{

		public function getList($user_guid){
			$sql = 'SELECT ym_org_blacklist.org_guid, ym_org_blacklist.is_del, ym_org.name,ym_org.logo, ym_org.description';
			$sql .= ' FROM ym_org_blacklist INNER JOIN ym_org ON ym_org_blacklist.org_guid = ym_org.guid';
			$sql .= " where ym_org_blacklist.user_guid = '{$user_guid}' and ym_org_blacklist.is_del = 0 and ym_org_blacklist.type = 2";
			return $this->query($sql);
		
		}


}
?>