<?php

/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 下午4:42
 *
 * Reids队列 用于管理Redis的生命周期 以及形成一个队列
 */
class RedisQueue
{

    /**
     * @var Redis对象
     */
    protected $redis;

    /**
     * @var Redis是否连接
     */
    protected $isConnected;


    public function __construct($config)
    {
        if (empty($config)) {
            echo "RedisManager_Construct Error : EmptyConfig" . "\n";
            return;
        }
        $host = empty($config["host"]) ? "localhost" : $config["host"];
        $port = empty($config["port"]) ? 3306 : $config["port"];
        $auth = $config["auth"];


        $this->redis = new Redis();
        $this->redis->connect($host, $port);

        if (!empty($auth)) {
            $this->redis->auth($auth);
        }

        $this->isConnected=true;

        echo "RedisManager_Construct Successed" . "\n";
    }


    public function __destruct()
    {
        // TODO: Implement __destruct() method.

        if (!empty($this->redis)) {
            //关闭Redis连接
            $this->redis->close();
        }

        unset($this->redis);

        echo "RedisManager_Destruct Successed" . "\n";
    }


    /**
     * @param $key Key
     * @param $value Value
     * Push数据到Redis
     */
    public function rPush($key, $value)
    {

        if (!$this->isConnected) {
            echo "RedisQueue_rPush Error: Not Connected" . "\n";
            return;
        }

        //如果是array 则序列化
        if (is_array($value)) {
            $value = json_encode($value);
        }

        try {
            $this->redis->rPush($key, $value);
        } catch (Exception $e) {
            echo "RedisQueue_rPush Error:" . $e->getTraceAsString() . "\n";
        }

    }


    /**
     * @param $key Key
     * @return null Value
     *
     * 从RedisPop数据
     */

    public function lPop($key)
    {
        $data = null;

        if (!$this->isConnected) {
            echo "RedisQueue_lPop Error: Not Connected" . "\n";
            return null;
        }

        try {
            $data = $this->redis->lPop($key);
        } catch (Exception $e) {
            echo "RedisQueue_lPop Error:" . $e->getTraceAsString() . "\n";
        }
        return $data;
    }


    /**
     * @param $config
     * @return RedisQueue
     *
     * 创建一个实例
     */
    public  static  function create($config){
        return new RedisQueue($config);
    }

}