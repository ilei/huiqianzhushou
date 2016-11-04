<?php
return array(
    'LOAD_EXT_CONFIG'      => 'db,url',      // 分割配置文件
    'URL_CASE_INSENSITIVE' =>  true,    // 默认false 表示URL区分大小写 true则表示不区分大小写

    'DEFAULT_THEME'        => 'Default', // 默认模板主题名称
    'LAYOUT_ON'            => false,     // 是否启用布局


	'meetelf_name'         => '酷客会签',

	//电子票域名
	'meetelf_url'          => 'http://m.smartlei.com',
	'swoole_host'          => '127.0.0.1',
	'swoole_port'          => '9502',
	'log_file'             => '/tmp/swoole-sender-9502.log',
);
