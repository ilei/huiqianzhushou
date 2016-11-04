<?php
namespace Common\Model;

use Common\Model\BaseModel;

/**
 * 点赞统计 模型
 * 
 * CT: 2015-01-07 14:00 by YLX
 */
class StatisticLikeModel extends BaseModel
{
    /**
     * 记录
     * @param $obj_guid     被查看目标guid
     * @param $user_guid    查看人guid
     * @param $obj_type     被查看目标类型 1为活动
     * @param $is_like      是否点赞, 0为取消赞, 1为点赞
     * @return bool|mixed
     * CT: 2015-01-07 14:00 by YLX
     */
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

    /**
     * 获取被查看目标被赞总数
     * @param $obj_guid     被查看目标guid
     * @param $obj_type     被查看目标类型 1为活动
     * @param $is_like      是否点赞, 0为取消赞, 1为点赞
     * @return int
     */
    public function get_total_count($obj_guid, $obj_type, $is_like=1) {
        if(empty($obj_guid) || empty($obj_type)) {
            return false;
        }
        return $this->where(array('obj_guid' => $obj_guid, 'obj_type' => $obj_type, 'is_like' => $is_like))->count();
    }

    /**
     * 返回用户是否已点赞
     * @param $obj_guid
     * @param $obj_type
     * @param $user_guid
     * @return int 大于0表是已点赞
     */
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