<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 用户记录Timeline  模型
 *
 * CT 2014-12-05 13:54 by RTH
 */
class UserTimelineModel extends BaseModel{

    /**
     * 记录
     * @param $user_guid 用户GUID
     * @param string $obj_guid 目标GUID
     * @param $obj_type 目标类型
     * @param string $content 内容
     * @return bool|mixed
     * CT 2015-01-09 17:34 by ylx
     */
    public function record($user_guid, $obj_type, $obj_guid='', $content='', $decr=0, $is_show=1) {
        $time = time();
        $data = array(
            'guid'       => create_guid(),
            'obj_guid'   => $obj_guid,
            'obj_type'   => $obj_type,
            'user_guid'  => $user_guid,
            'content'    => $content,
            'is_show'    => $is_show,
            'year'       => date('Y', $time),
            'month'      => date('m', $time),
            'day'        => date('d', $time),
            'date'       => date('Ymd', $time),
            'created_at' => $time+$decr,
            'updated_at' => $time
        );
        return $this->inserUp($data);
    }
    
    
    

}