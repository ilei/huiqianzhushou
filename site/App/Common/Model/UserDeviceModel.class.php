<?php
namespace Common\Model;

use Common\Model\BaseModel;

/**
 * 用户设备 模型
 * 
 * CT: 2014-09-23 14:26 by YLX
 */
class UserDeviceModel extends BaseModel 
{
    protected $patchValidate = true;
    
    /**
     * 自动完成
     * 
     * CT: 2014-09-23 14:26 by YLX
     */
    protected $_auto = array (
        array('guid','create_guid', self::MODEL_INSERT, 'function'), // 对 guid 字段在 add 的时候写入当前时间戳
        array('last_login','time', self::MODEL_BOTH, 'function'), // last_login字段在 save&add 的时候写入当前时间戳
        array('last_login_ip','get_client_ip', self::MODEL_BOTH, 'function'), // last_login_ip字段在 save&add 的时候写入当前时间戳
        array('updated_at','time', self::MODEL_BOTH, 'function'), // 对updated_at字段在 save&add 的时候写入当前时间戳
        array('created_at','time', self::MODEL_INSERT, 'function'), // 对 created_at 字段在 add 的时候写入当前时间戳
    );

    /**
     * 自动验证条件
     * 
     * CT: 2014-09-23 14:26 by YLX
     */
//     protected $_validate = array(
            
//             // CU时数据对像验证
//             array('guid', 32, 'guid长度不对.', 0, 'length'), 
//             array('mac','require','终端mac地址为空.', 0), 
//             array('sdk_uid','require','终端SDK UID为空.', 0), 
//             array('name','require','行业名称必须填写!', 0), 
//             array('name','require','行业名称必须填写!', 0), 
//             array('name','1, 50','行业名称最大长度为50个字符!', self::EXISTS_VALIDATE, 'length')

//     );
    
    public function logoutAll($user_guid)
    {
//        // 清除所有redis缓存                             **********       暂时不用       ***********
//        $tokens = $this->where(array('user_guid'=>$user_guid))->getField('token', true);
//        foreach($tokens as $t){
//            S($t.':user_info', null); //清除Redis缓存
//            S($t.':user_device', null);
//        }

        $res = $this->where(array('user_guid'=>$user_guid, 'status'=>'1'))
                    ->save(array('status'=>'0', 'updated_at' => time()));
        return $res;
    }


    /**
     * 查询
     *
     * @param array $condition 查询条件
     * @return array
     */
    public function getTokenInfo($condition) {
        return $this->where($condition)->find();
    }

    public function getTokenInfoByToken($token) {
        if(empty($token)) {
            return null;
        }
        return $this->getTokenInfo(array('token' => $token));
    }

    /**
     * 新增
     *
     */
    public function addTokenInfo($data){
        $this->create($data);
        return $this->add();
    }

    /**
     * 编辑
     *
     */
    public function editTokenInfo($condition, $data){
        $this->create($data);
        return $this->where($condition)->save();
    }

    /**
     * 删除
     *
     */
    public function delTokenInfo($condition){
        return $this->where($condition)->delete();
    }

}