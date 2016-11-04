<?php
namespace Common\Model;

use Common\Model\BaseModel;

/**
 * 查看统计 模型
 * 
 * CT: 2015-01-07 14:00 by YLX
 */
class StatisticViewModel extends BaseModel
{
    /**
     * 记录
     * @param $obj_guid     被查看目标guid
     * @param $user_guid    查看人guid
     * @param $obj_type     被查看目标类型  1文章 2投票 3讨论 4报名 5问卷
     * @return bool|mixed
     * CT: 2015-01-07 14:00 by YLX
     */
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