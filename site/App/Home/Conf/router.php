<?php
return array(
    // 路由配置
    'URL_ROUTER_ON'         =>  true,
    'URL_ROUTE_RULES'       =>  array(
        array('event/:id\d',"Dispatcher/event"),
        array('/', 'Index/index'),
        
        //我的主页
        array('user/index', 'Information/index'),
        array('api/syslogin', 'Api/syslogin'),
        array('user/activity', 'Release/index'),
        array('user/ticket', 'Ticket/mine_tickets'),
        array('user/order', 'Order/mine_orders'),
        array('user/base', 'Information/information'),
        array('user/password', 'Information/password'),
        array('user/sponsor', 'Information/organizers'),
        array('user/account', 'Information/account'),
        array('user/wallet', 'Information/wallet'),
        array('user/download', 'Index/download'),
        array('user/help', 'Help/index'),
        array('user/buy', 'Buy/buy_email_msg'),
        array('user/change_mobile', 'Information/change_mobile'),
        array('user/change_mobile_step2', 'Information/change_mobile_step2'),
        array('user/change_mobile_step3', 'Information/change_mobile_step3'),


       //活动   
       array('act/release', 'Release/index'),
       array('act/unrelease', 'Release/index?s=0'),
       array('act/manage/guid/:aguid', 'Act/act_manage'),
       array('act/tmanage/guid/:aguid', 'Act/tmanage'),
       array('act/edit/guid/:guid', 'Act/edit'),
       array('act/preview/guid/:g', 'Event/index'),
       array('act/mpreview/guid/:aid', 'Act/mobile_preview'),
       array('act/qrcode/guid/:aid', 'Act/qrcode'),

       //订单
       array('order/detail/guid/:on', 'Order/detail'),
       array('order/review/guid/:aguid', 'Order/review'),


       //表单 
       array('form/setting/guid/:aguid', 'Form/form_set'),

       //报名 
        array('signup/users/guid/:aid', 'Registration/signup_userinfo'),

        //结算
        array('await', 'Financing/index'),//等待结算列表
        array('account', 'Financing/account_set'),//账户管理页面
        array('public_ticket/:aid', 'Financing/public_ticket'),//银行管理页面

        array('update', 'Download/app_check', '', array('method' => 'get')), // APK更新信息

        array('auth/login', 'Auth/login', ''),
    )
);
