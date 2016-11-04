<?php

/**
 * 活动相关配置
 * CT: 2014-09-12 15:00 by YLX
 * UT: 2015-05-14 14:51 by YLX
 * UT: 2015-08-10 14:32 by QY
 */

return array(
    'ADMIN_GUID'        => '88B9EBACEC31F962EF3100SUPERADMIN', //会签助手官方GUID
    'ADMIN_NAME'        => '会签助手', // 会签助手官方名称

    'APP_TYPE'          => array(
        1 => array('name'          => '会签助手-Android',
                   'ext'           => 'apk',
                   'name_tempfile' => 'meetelf-temp',
                   'name_file'     => 'meetelf',
                   'save_path'     => '/meetelf/android'
        ),
        2 => array('name'          => '会签助手-Android-beta',
                   'ext'           => 'apk',
                   'name_tempfile' => 'meetelf-beta-temp',
                   'name_file'     => 'meetelf-beta',
                   'save_path'     => '/meetelf/android'
        ),
        3 => array('name'          => '会签助手-PC端',
                   'ext'           => 'zip',
                   'name_tempfile' => 'meetelf-pc-temp',
                   'name_file'     => 'meetelf-pc',
                   'save_path'     => '/meetelf/pc'
        ),
        4 => array('name'          => '会签助手-PC端-beta',
                   'ext'           => 'zip',
                   'name_tempfile' => 'meetelf-pc-beta-temp',
                   'name_file'     => 'meetelf-pc-beta',
                   'save_path'     => '/meetelf/pc'
        ),
        5 => array('name'          => '会签助手-IOS端',
                   'ext'           => 'ipa',
                   'name_tempfile' => 'meetelf-ios-temp',
                   'name_file'     => 'meetelf-ios',
                   'save_path'     => '/meetelf/ios'
        ),
        6 => array('name'          => '会签助手-IOS端-beta',
                   'ext'           => 'ipa',
                   'name_tempfile' => 'meetelf-ios-beta-temp',
                   'name_file'     => 'meetelf-ios-beta',
                   'save_path'     => '/meetelf/ios'
        )
    ),

    //大后台文件上传分类
    'ARTICLE_SIGN'      => array(
        1 => '新闻',
        2 => '文章',
        3 => '公告',
        4 => '常见问题_ANDROID',
        5 => '常见问题_IOS'
    ),
    //账号类型
    'COMMUNITY_TYPE'    => array(
        1 => '个人',
        2 => '企业',
        3 => '公益组织'
    ),
    //账号种类
    'COMMUNITY_SPECIES' => array(
        1 => '内部号',
        2 => '测试号',
        3 => '正式号',
        4 => '试用号'
    ),
);
