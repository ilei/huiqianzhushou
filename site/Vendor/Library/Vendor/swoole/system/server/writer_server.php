<?php
// +----------------------------------------------------------------------
// | Meetelf Framework [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011-2014 Meetelf Team (http://www..com)
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author wangleiming<wangleiming@yunmai365.com>
// +----------------------------------------------------------------------

Loader::load('setter');

class WriterServer
{


    protected $db_config;

    public function __construct()
    {
        $config = Loader::load_config('server');
        if (!$config) {
            return false;
        }

        //读取数据库配置
        $this->db_config = Loader::load_config('db');


        $this->server = new swoole_server($config['writer']['host'], $config['writer']['port']);
        if (!$this->server) {
            echo date('Y-m-d H:i:s') . ' <--> Fatal error : writer server connect failed' . "\r\n";
        }
        $this->server->set($config['writer']['options']);
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
//        echo 'server start_time--' . date('Y-m-d H:i:s') . "\n";
//        echo "master_pid:{$server->master_pid}--manager_pid:{$server->manager_pid}\n";
//        echo 'version--[' . SWOOLE_VERSION . "]\n";
    }

    public function onWorkerStart(swoole_server $server, $worker_id)
    {
        global $argv;
//        echo 'workerStart time--' . date('Y-m-d H:i:s') . "\n";
        if ($worker_id == 0) {
            /*$server->tick(1000, function() use ($server){
            for($i = 1;$i < 5; $i++){
                $server->task(array('cmd' => 'exec'), -1);
            }
        });
         */
        }
        if ($worker_id >= $server->setting['worker_num']) {
//            echo 'task_id:' . $worker_id . "\n";
            swoole_set_process_name("php {$argv[0]} task worker");
            //每一个task进程保存数据连接


//            $this->redis = init_redis();

        } else {
//            echo 'worker_id:' . $worker_id . "\n";
            swoole_set_process_name("php {$argv[0]} event worker");
        }
    }

    public function onConnect(swoole_server $server, $fd, $from_id)
    {
//        echo 'client connect time--' . date('Y-m-d H:i:s') . "\n";
//        echo 'client fd:' . $fd . "\n";
//        echo 'client from_id:' . $from_id . "\n";
    }


    public function onFinish(swoole_server $server, $data)
    {
//        echo 'async_task finish time--' . date('Y-m-d H:i:s') . "\n";
//        echo 'connect_pid:' . posix_getpid().".\n";
    }

    public function onClose(swoole_server $server, $fd, $from_id)
    {
//        echo 'client close time--' . date('Y-m-d H:i:s') . "\n";
//        echo 'client fd:' . $fd . "\n";
//        echo 'client from_id:' . $from_id . "\n";
    }

    public function onWorkerStop(swoole_server $server, $worker_id)
    {
//        echo 'workerStop time--' . date('Y-m-d H:i:s') . "\n";
//        echo 'worker_id:' . $worker_id . "\n";
//        echo 'pid:' . posix_getpid().".\n";
    }

    public function onWorkerError(swoole_server $server, $data)
    {
//        echo 'workerError time--' . date('Y-m-d H:i:s') . "\n";
//        echo 'pid:' . posix_getpid().".\n";
    }

    public function onShutDown(swoole_server $server)
    {
//        echo 'server shutdown time--' . date('Y-m-d H:i:s') . "\n";
//        echo 'server_pid:' . posix_getpid().".\n";
    }

    public function onReceive(swoole_server $server, $fd, $from_id, $data)
    {
        echo 'server receive time--' . date('Y-m-d H:i:s') . "\n";
//        echo 'client fd:' . $fd . "\n";
//        echo 'client--from_id:' . $from_id . "\n";
        $data = json_decode($data, true);
        $server->task($data, -1);
        $server->send($fd, "OK\n");
    }

    public function onTask(swoole_server $server, $task_id, $from_id, $data)
    {
//        echo 'task start time--' . date('Y-m-d H:i:s') . "\n";
//        echo 'tast_id :' . $task_id. "\n";
//        echo 'client--from_id:' . $from_id . "\n";
        $type = $data['type'];

        try {
//            echo "Try Init Redis !" . "\r\n";
            //随用随声明 用完关闭/删除
            $redis = init_redis();
            $mysql = init_db($this->db_config);

            switch ($type) {
                case 'wait':
                    $msg_id = intval($data['msg_id']);
                    setter::set_msg_to_wait($msg_id, $mysql, $redis);
                    break;
                case 'success':
                    set_msg_to_success($msg_id, $mysql, $redis);
                    break;
                case 'fail':
                    set_msg_to_fail($msg_id, $mysql, $redis);
                    break;
                default:
                    echo 'error';
            }

//            echo "Try Release Redis !" . "\r\n";


            $redis->close();
            unset($redis);
        } catch (Exception $e) {

            var_dump("Write_Server Error:", $e);

            unset($e);
        }


        $server->finish("OK\n");


    }
}

//启动
new WriterServer();
