<?php
/**
 *
 * 通用配置
 *
 **/

return array(
    'LOAD_EXT_CONFIG'      => 'db,domain,status,api,good,operation,activity,admin,upload',     // 分割配置文件
    'document_domain'      => 'smartlei.com',

    /**
     * ---------------------------------------------------
     *  Cache -> Redis
     * ---------------------------------------------------
     **/

    'DATA_CACHE_TYPE'      => 'redis',                  //使用redis作为缓存
    'DATA_CACHE_TIME'      => NULL,                     // 默认为永久存储
    'DATA_CACHE_PREFIX'    => '',
    'REDIS_HOST'           => 'localhost',              // redis主机
    'REDIS_PORT'           => 6379,                     // redis端口
    //'REDIS_AUTH'           => 'lewf3281zdi',          // redis密钥

    'SITE_HOST'            => 'hz.smartlei.com',        // 网站域名
    'SITE_HOST_URL'        => 'http://hz.smartlei.com', // 网站地址

    // 日志记录 (现只记录SQL)
    'LOG_RECORD'           => false,                    // 进行日志记录
    'LOG_EXCEPTION_RECORD' => false,                    // 是否记录异常信息日志
    'LOG_LEVEL'            => 'SQL',                    // 允许记录的日志级别
    'DB_SQL_LOG'           => true,                     // 记录SQL信息

    /**
     * ---------------------------------------------------
     *  原始配置
     * ---------------------------------------------------
     */

    'URL_MODEL'             => 2, 
    'LANG_SWITCH_ON'        => true,
    'LANG_LIST'             => 'zh-cn',


    //控制器层 
    'ACTION_SUFFIX'        => '',                         // 操作方法后缀
    'DEFAULT_MODULE'       => 'Home',                     // 默认模块
    'URL_HTML_SUFFIX'      => '',                         // 默认模板文件后缀
    'URL_CASE_INSENSITIVE' => true,                       // 默认false 表示URL区分大小写 true则表示不区分大小写

    // 视图层
    'DEFAULT_THEME'        => 'Default',
    'LAYOUT_ON'            => true, 
    'LAYOUT_NAME'          => 'layout',

    'COOKIE_PREFIX'        => 'hz_',

    'TMPL_ACTION_ERROR'    => APP_PATH . 'Common/View/Default/Tpl/dispatch_jump.tpl', 
    'TMPL_ACTION_SUCCESS'  => APP_PATH . 'Common/View/Default/Tpl/dispatch_jump.tpl',
    'TMPL_EXCEPTION_FILE'  => THINK_PATH . 'Tpl/think_exception.tpl',
    'URL_404_REDIRECT'     => APP_PATH . 'Common/View/Default/Tpl/404.html',
    'TMPL_CACHE_ON'        => false,


    'TMPL_PARSE_STRING'    => array(
        '__UPLOAD__' => '/Upload', // 增加新的上传路径替换规则
    ),

    'AUTOLOAD_NAMESPACE'   => array(
        'Pinq'     => THINK_PATH.'Library/Vendor/Pinq/timetoogo/pinq/Pinq'
    ),
    
    'MEDIA_CSS'                  => array(
        'BOOTSTRAP'       => '<link rel="stylesheet" type="text/css" href="/Public/common/bootstrap/css/bootstrap.css">',
        'FONT_AWESOME'    => '<link rel="stylesheet" type="text/css" href="/Public/common/font-awesome/css/font-awesome.css">',
        'JQUERYUI'        => '<link rel="stylesheet" type="text/css" href="http://apps.bdimg.com/libs/jqueryui/1.9.2/themes/redmond/jquery.ui.theme.css">',
        'DATETIMEPICKER'  => '<link rel="stylesheet" type="text/css" href="/Public/common/js/datetimepicker/DateTimePicker.css">',
        'BASE'            => '<link rel="stylesheet" type="text/css" href="/Public/meetelf/home/css/base.css">',
        'MODAL'           => '<link rel="stylesheet" type="text/css" href="/Public/meetelf/home/css/modal.css">',
    ),

       // 静态JS
    'MEDIA_JS'                   => array(
        'JQUERY'                     => '<script type="text/javascript" src="/Public/common/js/jquery.js"></script>',
        'JQUERYUI'                   => '<script type="text/javascript" src="/Public/common/js/jqueryui.js"></script>',
        'BOOTSTRAP'                  => '<script type="text/javascript" src="/Public/common/bootstrap/js/bootstrap.js"></script>',
        'DATETIMEPICKER'             => '<script type="text/javascript" src="/Public/common/js/datetimepicker/DateTimePicker.js"></script>',
        'BOOTSTRAP_DATETIMEPICKER'   => '<script type="text/javascript" src="/Public/common/js/bootstrap-datetimepicker.min.js"></script>',
        'JQUERY_VALIDATE'            => '<script type="text/javascript" src="/Public/common/js/jquery.validate.js"></script>',
        'JQUERY_VALIDATE_ADDITIONAL' => '<script type="text/javascript" src="/Public/common/js/additional-methods.js"></script>',
        'JQUERY172'                  => '<script type="text/javascript" src="http://apps.bdimg.com/libs/jquery/1.7.2/jquery.min.js"></script>',
        'JQUERY_LAZYLOAD'            => '<script type="text/javascript" src="http://cdnjscn.b0.upaiyun.com/libs/jquery.lazyload/1.9.1/jquery.lazyload.js"></script>',
        'JQUERY_AJAXUPLOAD'          => '<script type="text/javascript" src="/Public/common/js/jquery.ajaxupload.js"></script>',
        'IFRAME_BOX'                 => '<script type="text/javascript" src="/Public/common/js/showBox/FenBox.js"></script>',
        'ZERO_CLIPBOARD'             => '<script type="text/javascript" src="/Public/common/js/zeroclipboard/ZeroClipboard.js"></script>',
        'COMMON'                     => '<script type="text/javascript" src="/Public/common/js/common.js"></script>',
        'JQUERY_FORM'                => '<script type="text/javascript" src="http://cdnjscn.b0.upaiyun.com/libs/jquery.form/3.50/jquery.form.js"></script>',
        'ICHECK'                     => '<script type="text/javascript" src="http://cdnjscn.b0.upaiyun.com/libs/iCheck/1.0.1/icheck.min.js"></script>',
        'ICHECK_CUSTOM'              => '<script type="text/javascript" src="http://cdnjscn.b0.upaiyun.com/libs/iCheck/1.0.1/demo/js/custom.min.js"></script>',
        'JQPRINT'                    => '<script type="text/javascript" src="/Public/common/js/jquery.jqprint-0.3.js"></script>',
        'CHART'                      => '<script type="text/javascript" src="/Public/common/js/chart.min.js"></script>',
        'HTML5SHIV'                  => '<script type="text/javascript" src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>',
        'RESPOND'                    => '<script type="text/javascript" src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>',
    ),

);
