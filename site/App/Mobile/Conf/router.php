<?php
return array(
    // 路由配置
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES' => array(

        // 登陆
        array('login', 'Auth/login', '', array('method' => 'post')),
        array('login', 'Auth/login', '', array('method' => 'get')), 
        // 登出
        array('logout', 'Auth/logout', '', array('method' => 'post')),
        array('logout', 'Auth/logout', '', array('method' => 'get')), 
        // 重新登陆
        array('register', 'Auth/register', '', array('method' => 'post')),
        array('register', 'Auth/register', '', array('method' => 'get')),

        array('activity/view/guid/:aid', 'activity/view'),
        array('activity/view/guid/:aid/oid/:oid', 'activity/view'),
        array('activity/preview/guid/:aid', 'activity/view'),
        array('activity/signup/guid/:aid', 'activity/signup_user'),
        array('activity/userinfo/guid/:aid', 'activity/signup_userinfo'),
	),
);
