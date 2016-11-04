<?php
namespace Common\Model;

use Think\Model\MongoModel;

/**
 * Pc离线签到客户端下载记录
 * CT: 2015-08-07 11:40 by ylx
 */
class PcSigninLogModel extends MongoModel
{
    protected $connection = null;

    public function __construct($tableName = 'ym_pcsignin_down_log')
    {
        $this->connection = C('DB_CONFIG_MONGO');
        $this->dbName     = C('DB_CONFIG_MONGO.DB_NAME');
        parent::__construct($tableName);
    }

    /**
     * 记录用户上转下载记录
     * @param $data 要存储的数据
     * @param $type down下载记录， up上传记录
     * @return mixed
     */
    private function _addLog($data, $type)
    {
        $time         = time();
        $data_default = array(
            'type'       => $type,
            'created_at' => $time,
            'updated_at' => $time
        );
        $data         = array_merge($data, $data_default);
        $res          = $this->add($data);
        return $res;
    }

    /**
     * 记录用户下载记录
     * @param $data
     * @return mixed
     */
    public function addDownLog($data)
    {
        return $this->_addLog($data, 'down');
    }

    /**
     * 记录用户上传记录
     * @param $data
     * @return mixed
     */
    public function addUpLog($data)
    {
        return $this->_addLog($data, 'up');
    }

}