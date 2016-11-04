<?php
return array(
    'default_ok_status' => 1,
    'default_version'   => 1,
    'ajax_failed'       => 0,	
    'ajax_success'      => 1,	
    'order_length'      => 25,
    'user' => array(
        'ok'     => 1,	
        'delete' => 0,
        'banned' => 2,
        'locked' => 1,
        'not_auto_login' => 0,
        'auto_login'     => 1,
        'user_mobile_verify' => 1,
        'third_type_weixin'  => 1,
    ),	

    'act' => array(
        'unpublished'=>0,
        'going_on' => 1,
        'closed'=>3

    ),
    'own_goods' => array(
        'discount_type' => 2,
        'ok'  => 1,
        'nolimit' => -1,
    ),
    'act_text' => array(
        0 => '未发布',
        1 => '活动中',
        2 => '已结束',
        3 => '已关闭',
        4 => '未审核',
        5 => '已拒绝',
    ),
);
