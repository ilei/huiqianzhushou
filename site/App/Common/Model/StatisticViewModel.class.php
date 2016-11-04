<?php
namespace Common\Model;

use Common\Model\BaseModel;

class StatisticViewModel extends BaseModel
{
    public function record($obj_guid, $user_guid, $obj_type) {
        if(empty($obj_guid) || empty($user_guid) || empty($obj_type)) {
            return false;
        }
        $data = array(
            'guid'       => create_guid(),
            'obj_guid'   => $obj_guid,
            'obj_type'   => $obj_type,
            'user_guid'  => $user_guid,
            'created_at' => time(),
            'updated_at' => time()
        );
        return $this->add($data);
    }

    public function get_total_count($obj_guid, $obj_type) {
        if(empty($obj_guid) || empty($obj_type)) {
            return false;
        }
        return $this->where(array('obj_guid' => $obj_guid, 'obj_type' => $obj_type))->count();
    }
}
