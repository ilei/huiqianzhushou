<?php 
return array(
    'sender' => array(
        'host'    => '127.0.0.1',
        'port'    => '9501',
        'options' => array(
            'worker_num'      => 4,	
            'task_worker_num' => 4,
            'dispatch_mode'   => 3,
            'daemonize'       => 1,
            'log_file'        => '/tmp/swoole/sender.log',
        ),
    ),
    'writer' => array(
        'host'    => '127.0.0.1',
        'port'    => '9502',
        'options' => array(
            'worker_num'      => 4,	
            'task_worker_num' => 4,
            'dispatch_mode'   => 3,
            'daemonize'       => 1,
            'log_file'        => '/tmp/swoole/writer.log',
        ),
    ),
);

