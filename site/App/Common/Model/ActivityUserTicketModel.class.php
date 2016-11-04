<?php
namespace Common\Model;

/**
 * 发票 模型
 * CT: 2015-06-11 11:26 by ylx
 */
class ActivityUserTicketModel extends BaseModel
{
    /**
     * 根据活动获取所有参与活动的人员guid
     * @param $aids 活动guids
     * @param int $status 电子票状态，0未发送，1发送失败，2已发送，3已查看，4已签到
     * @return mixed
     * ct: 2015-06-10 10:38 by ylx
     */
    public function getUserGuidsByActivity($aids, $status = 4)
    {
        return $this->where(array('status' => $status,
                                  'activity_guid' => array('in', $aids),
                                  '_string' => " LENGTH(user_guid)=32"
                            ))
            ->getField('user_guid', true);
	}
	
	/**
     * 根据活动ID和用户guid过滤活动ID
     * @param $aids 活动guids
     * @param $user_guid 用户GUID
     * @param int $status 电子票状态，0未发送，1发送失败，2已发送，3已查看，4已签到
     * @return mixed
     * ct: 2015-06-10 10:38 by ylx
     */
    public function getActivityGuidsByUserGuid($aids, $user_guid, $status = 4)
    {
        return $this->where(array('status' => $status,
                                  'activity_guid' => array('in', $aids),
								  'user_guid'     => $user_guid
                            ))
            ->getField('activity_guid', true);
    }

	

}