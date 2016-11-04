<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/11/20
 * Time: 上午10:15
 */


//自行管理的Server 定时从Redis缓存中读取需要处理的数据 进行操作

//SwooleServer地址
$server_address='127.0.0.1';
$server_port=8888;
////Redis配置
//global $redis_address;
//global $redis_port;
global $redis_key;

$redis_key='data';
//$redis_auth='lewf3281zdi';
//定时器配置
global $tick_trigger_time;
$tick_trigger_time=10*1000;//10秒定时器间隔
//批量发送条数配置
global $batch_count;
$batch_count=100;//100条发送一次

global $current_message_service;
//短信服务商配置 0.submail 1.云片
$current_message_service=1;

Loader::load('setter');

//SwooleServer配置
$server_config=array(
    //工作线程1
    'worker_num'=>1,
    //任务线程4
    'task_worker_num'=>4,
    //work分发为抢占模式
    'dispatch_mode'=>3,
    //task分配为队列+抢占模式
    'task_ipc_mode'=>3,
    //是否转为守护进程
    'daemonize'       => 1,
    //错误日志
    'log_file'        => '/tmp/swoole/swoole_send_server.log',
);

$server_config = Loader::load_config('server'); 
//初始化Server地址和端口
$serv = new swoole_server($server_config['sender']['host'], $server_config['sender']['port']);

//配置swoole服务参数
$serv->set($server_config['sender']['options']);

//注册事件函数
$serv->on('start','onStart');
$serv->on('shutdown','onShutdown');
$serv->on('workerstart',"onWorkerStart");
$serv->on('workerstop',"onWorkerStop");
$serv->on('task',"onTask");
$serv->on('receive','onReceive');
$serv->on('finish','onFinish');
$serv->on('workererror','onWorkerError');


//服务开启
function onStart($server){
    echo 'server_start'."\r\n" ;
}
//服务结束
function onShutdown($server){
    echo 'server_shutdown'."\r\n" ;
    //关闭Redis
   

}
//工作进程启动
function onWorkerStart($server,$work_id){
    echo "worker_start at index:".$work_id."\r\n" ;
    //这里启动定时器 读取redis数据 定时器根worker进程一起呗销毁 不需要特殊处理
    if(!$server->taskworker){

        global $tick_trigger_time;
        //5秒1次轮询
        $server->tick($tick_trigger_time,function($id) use($server){
            echo "\r\n".'Tick '.$id.' tiggerd at '.date('H:i:s',time()).'. try receive data'."\r\n";


            try {

//                echo "Try Init Redis"."\r\n";

//                global $redis_address;
//                global $redis_port;
                global $redis_key;
//                global $redis_auth;
//
//
//                $redis = new Redis();
//                $redis->connect($redis_address, $redis_port);
//                $redis->auth($redis_auth);

                $redis=init_redis();




                //从队头pop数据
                $data = $redis->lPop($redis_key);
                while (!empty($data)) {
                    //分配任务
                    $server->task($data, -1);
                    //再次尝试获取数据
                    $data = $redis->lPop($redis_key);
                    //sleep(1);
                }

//                echo "Try Release Redis"."\r\n";
                //关闭Redis
                $redis->close();
                unset($redis);


            }catch (Exception $e){
                var_dump("Send Server Error:",$e);
                unset($e);
            }
        });
    }
}

//工作进程退出
function onWorkerStop($server,$work_id){
//    echo 'worker_stop at index:'.$work_id."\r\n";
}
function onReceive($server,$fd,$from_id,$data){
//    echo 'received '.$fd.' from:',$from_id, 'and data:'.$data."\r\n";
    //nothing
}

//任务完成
function onFinish($server,$task_id,$data){
//    echo 'task:'.$task_id.' finished. and data:'.$data."\r\n";
}

function onWorkerError($server, $worker_id, $worker_pid,  $exit_code){
//    echo 'error catched with'.$worker_id,' ',$worker_pid,' reason code:'.$exit_code."\r\n";
}


//接受到任务
function onTask($server,$task_id,$from_id,$data){
//    echo 'task:'.$task_id.' handled from:'.$from_id."\r\n";

    try {

        //默认此处的数据是来自王叔叔的 $tail 批量数据
        $tail = json_decode($data, true);
        if(!$tail){
            return false;
        }
        $send_type = isset($tail['send_type']) ? $tail['send_type'] : 1;
        unset($tail['send_type']);
        $tail = array_columns($tail, null, 'mobile');
        unset($data);

        $success_arr = array();
        $fail_arr = array();
        if($send_type == 1){

            global $current_message_service;

            $res='[]';
            echo $current_message_service."\r\n";
            switch($current_message_service){
                case 0:
                    //进行批量发送 并且获取结果
                    $res = submail_multixsend($tail);
                    break;
                case 1:
                    //进行批量发送 并且获取结果
                    $res = yunpian_multixsend($tail);
                    break;
            }
            if(!is_array($res)){
                $res = json_decode($res, true);
            }
            if(!$res){
                return false;
            }
            //遍历结果集 区分成功失败
            foreach ($res as $value) {
                if ($value['status'] == 'success') {
                    $success_arr[] = $tail[$value['to']];
                } else {
                    $fail_arr[] = $tail[$value['to']];
                }
            }
            unset($res);
        }elseif($send_type == 2){
            foreach($tail as $key => $value){
                $subject = '【' . $value['app_name'] . '】活动电子票';
                $res = send_email($value['email'], $value['app_name'], $subject, $value['content']);       
                if(!$res){
                    return false;
                }
                if(!is_array($res)){
                    $res = json_decode($res, true); 
                }
                if($res['status'] == 'success'){
                    $success_arr[] = $value;
                }else{
                    $fail_arr[] = $value;
                }
                sleep(0.05);
            }
        }
        setter::set_msg_status($success_arr, $fail_arr);
        //回写数据库 这里写2张表 短信表/票务表 请王叔叔填坑

    }
    catch(Exception $e) {
        //处理异常 避免由于异常造成task进程崩溃
        unset($e);
    }
}

$serv->start();
