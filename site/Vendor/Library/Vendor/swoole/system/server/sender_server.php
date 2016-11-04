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


$config = Loader::load_config('server');
$config = $config['sender'];
$redis  = init_redis();
$redis_config = Loader::load_config('redis');
$server = new swoole_server($config['host'], $config['port']);

if(!$server){
    exit('sender server connect failed');	
}
$server->set($config['options']);


//绑定一些事件及相应的回调函数

$server->on('start', function(swoole_server $server){
    echo 'server start_time--' . date('Y-m-d H:i:s') . "\n";
    echo "master_pid:{$server->master_pid}--manager_pid:{$server->manager_pid}\n";	
    echo 'version--[' . SWOOLE_VERSION . "]\n";	
});

$server->on('workerStart', function(swoole_server $server, $worker_id){
    global $argv;
    if($worker_id == 0){
        $server->tick(1000, function() use ($server){
            for($i = 1;$i < 5; $i++){
                $server->task(array('cmd' => 'exec'), -1);
            }
        });
    }
    echo 'workerStart time--' . date('Y-m-d H:i:s') . "\n";
    if($worker_id >= $server->setting['worker_num']) {
        echo 'task_id:' . $worker_id . "\n";
        swoole_set_process_name("php {$argv[0]} task worker");
    } else {
        echo 'worker_id:' . $worker_id . "\n";
        swoole_set_process_name("php {$argv[0]} event worker");
    }
});

$server->on('connect', function(swoole_server $server, $fd, $from_id){
    echo 'client connect time--' . date('Y-m-d H:i:s') . "\n";
    echo 'client fd:' . $fd . "\n";
    echo 'client from_id:' . $from_id . "\n";
});


$server->on('finish', function(swoole_server $server, $data){
    echo 'async_task finish time--' . date('Y-m-d H:i:s') . "\n";
    echo 'connect_pid:' . posix_getpid().".\n";
});

$server->on('close', function(swoole_server $server, $fd, $from_id){
    echo 'client close time--' . date('Y-m-d H:i:s') . "\n";
    echo 'client fd:' . $fd . "\n";
    echo 'client from_id:' . $from_id . "\n";
});

$server->on('workerStop', function(swoole_server $server, $worker_id){
    echo 'workerStop time--' . date('Y-m-d H:i:s') . "\n";
    echo 'worker_id:' . $worker_id . "\n";
    echo 'pid:' . posix_getpid().".\n";
});

$server->on('workerError', function(swoole_server $server, $data){
    echo 'workerError time--' . date('Y-m-d H:i:s') . "\n";
    echo 'pid:' . posix_getpid().".\n";
});

$server->on('shutdown', function(swoole_server $server){
    echo 'server shutdown time--' . date('Y-m-d H:i:s') . "\n";
    echo 'server_pid:' . posix_getpid().".\n";
});

$server->on('receive', function(swoole_server $server, $fd, $from_id, $data){
    echo 'server receive time--' . date('Y-m-d H:i:s') . "\n";
    echo 'client fd:' . $fd . "\n";
    echo 'client--from_id:' . $from_id . "\n";
    $data = json_decode($data, true);
    $cmd  = $data['cmd'];
    switch($cmd){
    case 'start':
        $this->task($data, 0);
        $server->send($fd, "OK\n");
        break;
    case 'exec':
        $server->task($data, 0);
        $server->send($fd, "OK\n");
        break;
    default:
        echo "error cmd \n";
    }
});

$server->on('task', function(swoole_server $server, $task_id, $from_id, $data) use($redis, $redis_config){
    echo 'task start time--' . date('Y-m-d H:i:s') . "\n";
    echo 'tast_id :' . $task_id. "\n";
    echo 'client--from_id:' . $from_id . "\n";
    if($data){
        if($redis->lSize($redis_config['wail_list']) >= $redis_config['max_num']){
            $data = $redis->lRange(0, $redis_config['max_num']); 
            $redis->lTrim(0, $redis_config['max_num']);
            if($data){
                $success = $fail = array();
                $tail = getter::get_msg_tail($data); 
                if($tail){
                    $res = submail_multixsend($tail);
                    $res = json_decode($res, true);
                    foreach($res as $key => $value){
                        if($value['status'] == 'success'){
                            $success[$key] = $tail[$value['to']]['id'];
                        }elseif($value['status'] == 'error'){
                            $fail[$key] = $tail[$value['to']]['id'];
                        }  
                    }
                    $all_ids = array_values(array_merge($success, $fail)); 
                    getter::delete($all_ids); 
                }
            }
        } 
    }
    $server->finish("OK\n");	
});

$server->start();
