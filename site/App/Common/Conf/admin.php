<?php
return array(
    'ADMIN_GUID'        => '88B9EBACEC31F962EF3100SUPERADMIN', //酷客会签官方GUID
    'ADMIN_NAME'        => '酷客会签', // 酷客会签官方名称

    'APP_TYPE'          => array(
        1 => array(
            'name'          => '酷客会签-Android',
            'ext'           => 'apk',
            'name_tempfile' => 'huiqianzhushou-temp',
            'name_file'     => 'huiqianzhushou',
            'save_path'     => '/huiqianzhushou/android'
        ),
        2 => array(
            'name'          => '酷客会签-Android-beta',
            'ext'           => 'apk',
            'name_tempfile' => 'huiqianzhushou-beta-temp',
            'name_file'     => 'huiqianzhushou-beta',
            'save_path'     => '/huiqianzhushou/android'
        ),
        3 => array(
            'name'          => '酷客会签-PC端',
            'ext'           => 'zip',
            'name_tempfile' => 'huiqianzhushou-pc-temp',
            'name_file'     => 'huiqianzhushou-pc',
            'save_path'     => '/huiqianzhushou/pc'
        ),
        4 => array(
            'name'          => '酷客会签-PC端-beta',
            'ext'           => 'zip',
            'name_tempfile' => 'huiqianzhushou-pc-beta-temp',
            'name_file'     => 'huiqianzhushou-pc-beta',
            'save_path'     => '/huiqianzhushou/pc'
        ),
        5 => array(
            'name'          => '酷客会签-IOS端',
            'ext'           => 'ipa',
            'name_tempfile' => 'huiqianzhushou-ios-temp',
            'name_file'     => 'huiqianzhushou-ios',
            'save_path'     => '/huiqianzhushou/ios'
        ),
        6 => array(
            'name'          => '酷客会签-IOS端-beta',
            'ext'           => 'ipa',
            'name_tempfile' => 'huiqianzhushou-ios-beta-temp',
            'name_file'     => 'huiqianzhushou-ios-beta',
            'save_path'     => '/huiqianzhushou/ios'
        )
    ),

    //账号种类
    'COMMUNITY_SPECIES' => array(
        1 => '内部号',
        2 => '测试号',
        3 => '正式号',
        4 => '试用号'
    ),

    'COPYRIGHT'       => 'Copyright © 2014-2016 版权所有 天津酷客科技有限公司',
    'SERVICE_QQ'      => '624692151',
    'NUM_PER_PAGE'    => '10',
    'SERVICE_EMAIL'   => 'thinklang0917#gmail.com',
    'SERVICE_TEL'     => '',
    'MAX_SMS_NUM'     => '5',
    'MAX_CHATGROUP_NUM_PER_PERSON' => '50',
    'MAX_MEMBER_NUM_PER_CHATGROUP' => '2000',
    'VERCODE_SENDER'  => 'SubMail',
    'TICKET_SENDER'   => 'SubMail',
    'AUTO_ADD_TYPE'   => array('sms', 'email'),
    'AUTO_ADD_NUMS'   => 100,
);
