<?php
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.4.0','<'))  die('require PHP > 5.4.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);

//设置系统根目录
define('BASE_PATH',__DIR__);

// 定义应用目录
define('APP_PATH',__DIR__.'/App/');

//设置公共文件路径
define('PUBLIC_PATH',BASE_PATH.'/Public');

//设置上传文件根路径
define('UPLOAD_PATH',BASE_PATH.'/Upload');

//设置运行时路径
define('RUNTIME_PATH', BASE_PATH.'/Runtime/');

// 重命名库路径
define('THINK_PATH', __DIR__.'/Vendor/');


// 引入发送邮件submail sdk
require_once(THINK_PATH.'Library/Vendor/submail/SUBMAILAutoload.php');

// 引入全局linq 业务操作库
require_once(THINK_PATH.'Library/Vendor/Pinq/autoload.php');

// 引入框架入口文件
require THINK_PATH.'ThinkPHP.php';
