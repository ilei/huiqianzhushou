<?php
namespace Common\Model;

class ActivityUserTicketModel extends BaseModel
{
    public function getUserGuidsByActivity($aids, $status = 4)
    {
        return $this->where(array('status' => $status,
            'activity_guid' => array('in', $aids),
            '_string' => " LENGTH(user_guid)=32"
        ))
        ->getField('user_guid', true);
    }

    public function getActivityGuidsByUserGuid($aids, $user_guid, $status = 4)
    {
        return $this->where(array('status' => $status,
            'activity_guid' => array('in', $aids),
            'user_guid'     => $user_guid
        ))
        ->getField('activity_guid', true);
    }



}
