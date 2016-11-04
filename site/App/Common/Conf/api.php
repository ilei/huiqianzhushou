<?php

return array(

    /********************** 邮件设置 ***********************/

    'SUBMAIL_CONFIG' => array(
        'appid'      => '11831',
        'appkey'     => '07e3dfa35653c704fdf023edcc572e2b',
        'sign_type'  => 'sha1'
    ),

    'MAIL_SENDER'    => 'service@mail.net',




    /********************** alipay 设置 ***********************/

    'ALIPAY_CONFIG' => array(
        'partner'       => '2088611885779723', 
        'key'           => 't984a0ke7dzw6jer7yufhgdbexx3yqi9',
        'sign_type'     => strtoupper('MD5'),
        'input_charset' => strtolower('utf-8'),
        'cacert'        => getcwd() . '\\cacert.pem',
        'transport'     => 'http',

        'seller_email'  => 'service@mail.com',
        'successpage'   => 'Info/myorder?status=1',
        'errorpage'     => 'Info/myorder?status=0',
        'msuccesspage'  => 'Info/myorder?status=1&m=1', 
        'merrorpage'    => 'Info/myorder?status=0&m=1',
    ),

    'PAY_CONFIG' => array(
        'notify_url'    => 'http://hz.smartlei.com/paymsg/notify_url',
        'return_url'    => 'http://hz.smartlei.com/paymsg/return_url',

    ),

    'ORDER_PAY_CONFIG' => array(
        'notify_url'    => 'http://hz.smartlei.com/payment/notify_url',
        'return_url'    => 'http://hz.smartlei.com/payment/return_url',
    ),

    /********************** 短信设置 ***********************/

    'SubMail' => array(
        'appid'       => '10377',
        'appkey'      => '88be7034384e391c4a155002ac01efcd',
        'sign_type'   => 'sha1',
    ),



    /********************** 验证码服务提供商 ***********************/

    'VerCode' => array(
        'SubMail',
    ),	

    'VerCodeText' => array(
        'SubMail' => 'SubMail',
    ),	

    /********************** 电子票服务提供商 ***********************/
    'eTicket' => array(
        'SubMail'
    ), 

    'eTicketText' => array(
        'SubMail' => 'SubMail',
    ),

    'payMoney'    => array(
        '10', '20', '50', '100'
    ),

    'sender' => array(
        'meetelf' => 'http://send.smartlei.com/meetelf/',	
    ),
);
