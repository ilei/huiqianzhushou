<?php
namespace Common\Model;

use Think\Model\MongoModel;

class PcSigninLogModel extends MongoModel
{
    protected $connection = null;

    public function __construct($tableName = 'ym_pcsignin_down_log')
    {
        $this->connection = C('DB_CONFIG_MONGO');
        $this->dbName     = C('DB_CONFIG_MONGO.DB_NAME');
        parent::__construct($tableName);
    }

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

    public function addDownLog($data)
    {
        return $this->_addLog($data, 'down');
    }

    public function addUpLog($data)
    {
        return $this->_addLog($data, 'up');
    }

}
