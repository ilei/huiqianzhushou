<?php
namespace Common\Model;
use Common\Model\BaseModel;

class UserTimelineModel extends BaseModel{
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
