<?php
return array(
    'LOAD_EXT_CONFIG'       => 'router, generate_by_admin', // 分割配置文件
    'URL_CASE_INSENSITIVE'  =>  true,   // 默认false 表示URL区分大小写 true则表示不区分大小写
    'CONTROLLER_LEVEL'      => 2,

    'TOKEN_EXPIRE'          => 60*60*24*15, // 15 days
);
