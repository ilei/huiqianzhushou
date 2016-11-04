<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 上午10:47
 * 配置   约定 > 配置
 */

return array(

    /**
     * 数据库 配置
     *
     */
    'db' => array(
        'type' => 'mysql',
        'host' => 'mysql.meetelf.com',
        'port' => '3306',
        'user' => 'root',
        'pwd' => 'dsfw#22ddx45g',
        'dbname' => 'meetelf',
        'charset' => 'utf-8',
    ),

    /**
     * Redis配置
     */
    'redis' => array(
        'host' => '115.28.189.3',
        'port' => '6379',
        'auth' => 'lewf3281zdi',
    ),


    /**
     * ---------------------
     *
     * 服务配置
     *
     * ---------------------
     */

    'server'=>array(
        'publisher'=>array(
            'host'    => '127.0.0.1',
            'port'    => '9502',
            'options' => array(
                'worker_num'      => 1,
                'task_worker_num' => 1,
                'dispatch_mode'   => 3,
                'daemonize'       => 1,
                'log_file'        => '/tmp/swoole/publisher.log',
            ),
        ),
        'subscriber'=>array(
            'host'    => '127.0.0.1',
            'port'    => '9501',
            'options' => array(
                'worker_num'      => 1,
                'task_worker_num' => 4,
                'dispatch_mode'   => 3,
                'daemonize'       => 1,
                'log_file'        => '/tmp/swoole/Subscriber.log',
            ),
        )
    ),



    //服务商设置
    'SMS_SP'=>'YunPian',
    'Email_SP'=>'Submail',


    //接口设置
    'SMS'=>array(
        'YunPian'=>array(
            'apikey'=>'dc1ac76a8cf86c488eee62e466162abd', //唯一标示
            'tpl_id'=>'1138983',//模板ID
            'get_template_url'=>'http://yunpian.com/v1/tpl/get.json',//获取模板信息的URL
            'send_mutli_url'=>'http://yunpian.com/v1/sms/multi_send.json',//批量发送URL
        ),
        'Submail'=>array(
            'appid'       => '10285',
            'appkey'      => 'b817cd117b99c08918b6423029d761de',
            'sign_type'   => 'sha1',
            'template_id' => '2KgIs2',
        )
    ),
    'Email'=>array(
        'Submail'=>array(
            'appid' => '10605', // Mail 应用ID
            'appkey' => '3531c50a617909272d58285b8aee624d', // Mail 应用密匙
            'sign_type' => 'sha1', //
            'mail' => 'service@mail.shetuanbang.net',
        )
    )
);


