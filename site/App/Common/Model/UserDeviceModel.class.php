<?php
namespace Common\Model;

use Common\Model\BaseModel;

class UserDeviceModel extends BaseModel 
{
    public function logoutAll($user_guid){
        $res = $this->where(array('user_guid'=>$user_guid, 'status'=>'1'))
            ->save(array('status'=>'0', 'updated_at' => time()));
        return $res;
    }


    public function getTokenInfo($condition) {
        return $this->where($condition)->find();
    }

    public function getTokenInfoByToken($token) {
        if(empty($token)) {
            return null;
        }
        return $this->getTokenInfo(array('token' => $token));
    }

    public function addTokenInfo($data){
        $this->create($data);
        return $this->add();
    }

    public function editTokenInfo($condition, $data){
        $this->create($data);
        return $this->where($condition)->save();
    }

    public function delTokenInfo($condition){
        return $this->where($condition)->delete();
    }

}
