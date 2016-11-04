<?php
return array(
    'SIGINUSER' => array(
        'ACTIVE'    => 1,
        'NO_ACTIVE' => 0,
        'DEL'       => 1,
        'NO_DEL'    => 0,
        //签到用户最大数目
        'MAX_NUM'   => 10,
    ),
    //通知按列表推送时 每个列表的最大数目 由个推限制
    'YMPUSH'   => array(
        'LIST_MAX_NUM' => 50,
    ),

    //通知推送类型
    'MESSAGE'  => array(
        'TYPE'         => 11010,
        'ADMIN_NOTICE' => 11010,
        'FIX_GROUP'    => 11011, // 修复群组数据时发送消息
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

    // 短信验证码类型, 1忘记密码, 2会员手机验证, 3会员网站找回密码, 4会员邀请注册
    'MOBILE_CODE_TYPE'  => array(
        'api_forget_password'  => 1,
        'api_verify_mobile'    => 2,
        'site_forget_password' => 3,
        'site_invite_register' => 4
    ),

    'TICKET_TYPE' => array(
        'sms_ticket'  => array('code' => 3, 'hook_prefix' => 'smsTicket', 'template_id' => array('SubMail' => '2KgIs2')),
        'mail_ticket' => array('code' => 4, 'hook_prefix' => 'mailTicket'),
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

    'UCPAAS_CODE_SEND_TYPE'      => array(
        'voice' => 2,
        'text'  => 1,
    ),

    // 用户设备类型: 0.未识别 1.Android phone, 2.Android pad, 3.iphone, 4.ipad, 5.wp, 6.web
    'USER_DEVICE_TYPE'           => array(
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

);
