<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 下午7:20
 */

require_once(dirname(__FILE__). "/../loader.php");
require_once(dirname(__FILE__). "/../Logic/PublisherLogic.php");
class PublisherServer{

     //服务器
     protected  $server;

     protected $config;

    public  function __construct()
    {

        $this->config=Loader::config();

        $publisher_config=$this->config["server"]["publisher"];

        $this->server = new swoole_server($publisher_config["host"], $publisher_config["port"]);
        if (!$this->server) {
            echo date('Y-m-d H:i:s') . ' <--> Fatal error : writer server connect failed' . "\r\n";
        }
        $this->server->set($publisher_config['options']);
        $this->server->on('start', array($this, 'onStart'));
        $this->server->on('connect', array($this, 'onConnect'));
        $this->server->on('workerStart', array($this, 'onWorkerStart'));
        $this->server->on('workerStop', array($this, 'onWorkerStop'));
        $this->server->on('workerError', array($this, 'onWorkerError'));
        $this->server->on('finish', array($this, 'onFinish'));
        $this->server->on('close', array($this, 'onClose'));
        $this->server->on('shutDown', array($this, 'onShutDown'));
        $this->server->on('receive', array($this, 'onReceive'));
        $this->server->on('task', array($this, 'onTask'));
        $this->server->start();
    }


    public function onStart(swoole_server $server)
    {
        echo "Publisher_Server Start";

    }

    public function onWorkerStart(swoole_server $server, $worker_id)
    {

        if ($worker_id == 0) {

        }

        if ($worker_id >= $server->setting['worker_num']) {
            swoole_set_process_name("task worker");

        } else {
            swoole_set_process_name("event worker");
        }
    }

    public function onConnect(swoole_server $server, $fd, $from_id)
    {

    }

    public function onFinish(swoole_server $server, $data)
    {

    }

    public function onClose(swoole_server $server, $fd, $from_id)
    {

    }

    public function onWorkerStop(swoole_server $server, $worker_id)
    {

    }

    public function onWorkerError(swoole_server $server, $data)
    {

    }

    public function onShutDown(swoole_server $server)
    {

    }

    public function onReceive(swoole_server $server, $fd, $from_id, $data)
    {
        echo 'server receive time--' . date('Y-m-d H:i:s') . "\n";

        $data = json_decode($data, true);
        $server->task($data, -1);
        $server->send($fd, "OK\n");
    }

    public function onTask(swoole_server $server, $task_id, $from_id, $data)
    {

        $type = $data['type'];

        try {

            switch ($type) {
                case 'wait':{
                    $msg_id = intval($data['msg_id']);
                    PublisherLogic::hanld_wait_msg($msg_id);
                    break;
                }
                default:
                    echo "Unknow Type"."\n";
            }

        } catch (Exception $e) {


            var_dump("PublisherServer Error:", $e);

            unset($e);
        }

        $server->finish("OK\n");


    }
}

//启动服务
new PublisherServer();
