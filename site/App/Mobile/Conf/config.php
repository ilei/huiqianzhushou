<?php
return array(
    'LOAD_EXT_CONFIG'      => 'router,status_new', // 分割配置文件
    'URL_CASE_INSENSITIVE'  =>  true,   // 默认false 表示URL区分大小写 true则表示不区分大小写

    'LAYOUT_ON'            => false, // 是否启用布局
    'TMPL_ACTION_ERROR'     =>  APP_PATH.'Mobile/View/Default/Tpl/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'   =>  APP_PATH.'Mobile/View/Default/Tpl/dispatch_jump.tpl', // 默认成功跳转对应的模板文件
	
	// 应用设置
    'REMEMBER_KEY'          => 'B984F5F82123CCF9D2E6AEB5171B0365', // 记住登录cookie名称
    'REMEMBER_EXPIRE'       =>  2592000, // 记住登录过期时长(1个月)
);
