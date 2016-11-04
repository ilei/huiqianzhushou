<?php
return array(
    'URL_CASE_INSENSITIVE'      => true,   // 默认false 表示URL区分大小写 true则表示不区分大小写

    'TOKEN_EXPIRE'              => 60 * 60 * 24, // 1 days
    'NUM_PER_PAGE'              => '10',

    'COOKIE_PREFIX'             => 'signin_',      // Cookie前缀 避免冲突

    // 各种session redis等命名
    'user_device_uniqueid_name' => 'signin::user_device::uniqueid', // 用户设备cookie名称
    'auth_session_name'         => 'signin::auth::session',
);