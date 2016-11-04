<?php
return array(
    'SIGINUSER' => array(
        'ACTIVE'    => 1,
        'NO_ACTIVE' => 0,
        'DEL'       => 1,
        'NO_DEL'    => 0,
        'MAX_NUM'   => 10,
    ),

    // 订单状态
    'ORDER_STATUS' => array(
        'unpay'     => '0', // 新订单, 未支付
        'payed'     => '1', // 支付成功
        'payfailed' => '2', // 支付失败
        'cancel'    => '3', // 订单被取消
        'shipped'   => '4', // 已发货
        'success'   => '5', // 交易成功
    ),

    'CONTACT_MOBILE_ENCRYPT_KEY' => 'ym365ＹＭ３６５.ｃｏｍ',


    'MOBILE_CODE_TYPE'  => array(
        'api_forget_password'  => 1,
        'api_verify_mobile'    => 2,
        'site_forget_password' => 3,
        'site_invite_register' => 4
    ),

    'TICKET_TYPE' => array(
        'sms_ticket' => array(
            'code'        => 3, 
            'hook_prefix' => 'smsTicket', 
            'template_id' => array(
                'SubMail' => '2KgIs2'
            )
        ),
        'mail_ticket' => array(
            'code'        => 4, 
            'hook_prefix' => 'mailTicket'
        ),
    ),

    'CODE_TYPE'           => array(
        'api_find_password' => array(
            'code'        => 5, 
            'hook_prefix' => 'smsApiFindPwd', 
            'template_id' => array(
                'SubMail' => 'zW9AI2',
            )
        ),
        'api_verify_mobile'    => array(
            'code'        => 6, 
            'hook_prefix' => 'smsApiVerMobile',
            'template_id' => array(
                'SubMail' => 'X5oPR2',
            )
        ),
        'site_find_password'   => array(
            'code'        => 7, 
            'hook_prefix' => 'smsSiteFindPwd',
            'template_id' => array(
                'SubMail' => 'zW9AI2',
            )
        ),
        'site_invite_register' => array(
            'code' => 8, 
            'hook_prefix' => 'smsSiteInvReg',
            'template_id' => array(
                'SubMail' => 'X5oPR2',
            )
        ),
    ),

    // 接口图片返回大小
    'API_PHOTO' => array(
        'user_list' => 120,
        'user_info' => 240,
        'org_list' => 120
    ),

    'USER_DEVICE_TYPE' => array(
        'unknow'        => 0,
        'android_phone' => 1,
        'android_pad'   => 2,
        'iphone'        => 3,
        'ipad'          => 4,
        'wp'            => 5,
        'web'           => 6
    ),
    'pay_type' => array(
        'alipay' => 1,
    ),

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
        'unpublished' => 0,
        'going_on'    => 1,
        'closed'      => 3

    ),
    'own_goods' => array(
        'discount_type' => 2,
        'ok'            => 1,
        'nolimit'       => -1,
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
