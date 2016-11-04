<?php
namespace Common\Model;

use Common\Model\BaseModel;

class StatisticLikeModel extends BaseModel
{
    public function record($obj_guid, $user_guid, $obj_type, $is_like = 1) {
        if(empty($obj_guid) || empty($user_guid) || empty($obj_type)) {
            return false;
        }
        if($is_like != 1) { // 或为取消点赞, 则删除已点赞
            return $this->phy_delete(array('obj_guid'=>$obj_guid, 'user_guid'=>$user_guid, 'obj_type'=>$obj_type));
        } else { // 点赞
            $data = array(
                'guid'       => create_guid(),
                'obj_guid'   => $obj_guid,
                'obj_type'   => $obj_type,
                'user_guid'  => $user_guid,
                'is_like'    => $is_like,
                'created_at' => time(),
                'updated_at' => time()
            );
            return $this->add($data);
        }
    }

    public function get_total_count($obj_guid, $obj_type, $is_like=1) {
        if(empty($obj_guid) || empty($obj_type)) {
            return false;
        }
        return $this->where(array('obj_guid' => $obj_guid, 'obj_type' => $obj_type, 'is_like' => $is_like))->count();
    }

    public function is_like($obj_guid, $obj_type, $user_guid) {
        if(empty($obj_guid) || empty($user_guid) || empty($obj_type)) {
            return false;
        }
        return $this->where(array('obj_guid' => $obj_guid,
            'obj_type' => $obj_type,
            'is_like' => 1,
            'user_guid' => $user_guid))->count();
    }
}
