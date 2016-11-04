<?php

/**
 * API设置
 *
 **/

return array(

    /**
     * submail设置
     * ----------------------------------- start
     */
    'SUBMAIL_CONFIG' => array(
        'appid' => '10605', // Mail 应用ID
        'appkey' => '3531c50a617909272d58285b8aee624d', // Mail 应用密匙
        'sign_type' => 'sha1' //  Mail  验证模式, md5=md5 签名验证模式（推荐）, sha1=sha1 签名验证模式（推荐), normal=密匙明文验证
    ),

    'MAIL_SENDER' => 'service@mail.net',

    /**
     * submail设置
     * ----------------------------------- end
     */



    /**
     * 个推设置
     * ----------------------------------- start
     */
    'IGETUI_CONFIG' => array( // release版
        'appkey' => '7V6B8UyDJvAQGTPYMcvMN3',
        'appid'  => 'xmjVEW2Sq48aJQIXNUtHu1',
        'master_secret' => 'T91pAayMqx9n8Dw6AVPWw9',
        'device_token' => '',
        'host' => 'http://sdk.open.api.igexin.com/apiex.htm',
    ),
    'IGETUI_CONFIG_DEBUG' => array( // beta版
        'appkey' => 'sEJuXYPcJS8qk1nOSafSi4',
        'appid'  => 'MmyFcWBoPu9EkLN0w96XH6',
        'master_secret' => '98086KnlyS8rX6NMWKy36',
        'device_token' => '',
        'host' => 'http://sdk.open.api.igexin.com/apiex.htm',
    ),
    /**
     * 个推设置
     * ----------------------------------- end
     */


    /**
     * ----------------------------------- start
     * 支付宝配置参数
     */
    'ALIPAY_CONFIG'=>array(
        'partner'       => '2088611885779723',   //这里是你在成功申请支付宝接口后获取到的PID；//合作身份者id，以2088开头的16位纯数字
        'key'           => 't984a0ke7dzw6jer7yufhgdbexx3yqi9',//这里是你在成功申请支付宝接口后获取到的Key
        'sign_type'     => strtoupper('MD5'),
        'input_charset' => strtolower('utf-8'),
        'cacert'        => getcwd() . '\\cacert.pem',
        'transport'     => 'http',

        'seller_email'  => 'service@yunmai365.com', //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
        'successpage'   => 'Info/myorder?status=1', //支付成功跳转到的页面，我这里跳转到项目的Org控制器，myorder方法，并传参1（已支付列表）
        'errorpage'     => 'Info/myorder?status=0', //支付失败跳转到的页面，我这里跳转到项目的Org控制器，myorder方法，并传参0（未支付列表）
        'msuccesspage'   => 'Info/myorder?status=1&m=1', //支付成功跳转到的页面，我这里跳转到项目的Org控制器，myorder方法，并传参1（已支付列表）
        'merrorpage'     => 'Info/myorder?status=0&m=1', //支付失败跳转到的页面，我这里跳转到项目的Org控制器，myorder方法，并传参0（未支付列表）
    ),

    'PAY_CONFIG' => array(
        //'notify_url'    => 'http://mp.shetuanbang.net/pay/notify_url', //这里是异步通知页面url，提交到项目的Pay控制器的notify方法；
        //'return_url'    => 'http://mp.shetuanbang.net/pay/return_url', //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'notify_url'    => 'http://www.meetelf.com/paymsg/notify_url', //这里是异步通知页面url，提交到项目的Pay控制器的notify方法；
        'return_url'    => 'http://www.meetelf.com/paymsg/return_url', //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；

    ),

    'ORDER_PAY_CONFIG' => array(
        //'notify_url'    => 'http://mp.shetuanbang.net/payment/notify_url', //这里是异步通知页面url，提交到项目的Pay控制器的notify方法；
        //'return_url'    => 'http://mp.shetuanbang.net/payment/return_url', //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
        'notify_url'    => 'http://www.meetelf.com/payment/notify_url', //这里是异步通知页面url，提交到项目的Pay控制器的notify方法；
        'return_url'    => 'http://www.meetelf.com/payment/return_url', //这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；

    ),
    /**
     * 支付宝配置参数
     * ----------------------------------- end
     */

    /**
     * 短信配置参数
     * ----------------------------------- start
     */
    'YmSMS'=>array(
        'cdkey' => '3SDK-EMY-0130-JJUOT',//序列号序列号 特服号546797 , 
        'password'=> '409645',//用户密码
    ),

    /**
     * 短信配置参数
     * ----------------------------------- end
     */

    /**
     * 云之讯配置 
     */

    'UcPaas' => array(
        'accountsid' => '0e5ac4ac1680d243985e6ad0c993a848',
        'token'      => '70c948431be5b403044b9a1d77ab693c',		
        'appid'      => 'd8bf640dbbc34cd59efd3203ed6d979f',
    ),

    'SubMail' => array(
        'appid'       => '10285',
        'appkey'      => 'b817cd117b99c08918b6423029d761de',
        'sign_type'   => 'sha1',
    ),

    /**
     * 验证码服务提供商
     * 第一个为默认设置
     **/ 
    'VerCode' => array(
        'SubMail',
        'Ucpaas',
        'EMay',
    ),	

    'VerCodeText' => array(
        '云之讯'  => 'Ucpaas',
        'SubMail' => 'SubMail',
        '亿美'    => 'EMay', 
    ),	

    /**
     * 电子票服务提供商
     **/ 

    'eTicket' => array('SubMail', 'EMay', 'Mail'), 

    'eTicketText' => array(
        'SubMail' => 'SubMail',
        '亿美'    => 'EMay',	
    ),

    'payMoney'    => array(
        '10', '20', '50', '100'
    ),

    'sender' => array(
        'meetelf' => 'http://send.smartlei.com/meetelf/',	
    ),

    //微信登录
    'WEIXIN_APPID'         => 'wx7e04cae61d2599af',
    'WEIXIN_APPSECRET'     => '015209cab5520b37843add8b7b43d23d',
    'WEIXIN_REDIRECT_URI'  => 'http://www.meetelf.com/home/weixin/callback',
    'WEIXIN_RESPONSE_TYPE' => 'code',
    'WEIXIN_CODE_URI'      => 'https://open.weixin.qq.com/connect/qrconnect?',
    'WEIXIN_TOKEN_URI'     => 'https://api.weixin.qq.com/sns/oauth2/access_token?',
    'WEIXIN_REFRESH_TOKEN_URI'  => 'https://api.weixin.qq.com/sns/oauth2/refresh_token?',
    'WEIXIN_CHECK_TOKEN'   => 'https://api.weixin.qq.com/sns/auth?',
    'WEIXIN_USERINFO_URI'  => 'https://api.weixin.qq.com/sns/userinfo?',

    'WAP_ALIPAY' => array(
        'partner'       => '2088611885779723',   //这里是你在成功申请支付宝接口后获取到的PID；//合作身份者id，以2088开头的16位纯数字
        'seller_id'     => '2088611885779723',
        'private_key_path' => '/alidata/www/meetelf/site/Public/key/rsa_private_key.pem',
        'ali_public_key_path' => '/alidata/www/meetelf/site/Public/key/alipay_public_key.pem',
        'sign_type' => strtoupper('RSA'),
        'input_charset' => strtolower('utf-8'),
        'cacert' => getcwd().'\\cacert.pem',
        'transport' => 'http',

        'successpage'   => 'Index/order?status=1', //支付成功跳转到的页面，我这里跳转到项目的Org控制器，myorder方法，并传参1（已支付列表）
        'errorpage'     => 'Index/order?status=0', //支付失败跳转到的页面，我这里跳转到项目的Org控制器，myorder方法，并传参0（未支付列表）
    ),
    'WAP_ALIPAY_EXT' => array(
        'notify_url' => 'http://m.meetelf.com/pay/notify_url',
        'return_url' => 'http://m.meetelf.com/pay/return_url', 
    ),

);
