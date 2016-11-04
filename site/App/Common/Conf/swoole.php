<?php
return array(
    'SWOOLE_HOST'     => '127.0.0.1', 
    'SWOOLE_PORT'     => '9501',   
	//设置启动的worker进程数
	'WORKER_NUM'      => 4,

	//配置task进程的数量，配置此参数后将会启用task功能
	'TASK_WORKER_NUM' => 4,

	//数据包分发策略 1 轮询 2 固定 3 抢占
	'DISPATCH_MODE'   => 3,
	//是否转入后台作为守护进程运行 
	'DAEMONIZE'       => 1,
	//log 文件位置 如果没有 将重定向到 /dev/null 
	'LOG_FILE'        => '/tmp/swoole.log',
);
