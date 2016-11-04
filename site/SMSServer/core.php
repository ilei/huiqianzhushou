<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 上午10:20
 *
 *
 * 核心
 */

//第三方库
//require_once("Libs/submail/lib/mailxsend.php");
require_once("Libs/submail/lib/mailsend.php");
require_once("Libs/submail/lib/messagemultixsend.php");
require_once("Libs/Curl.php");

//公有
require_once("constract.php");
require_once("loader.php");

//核心库
require_once(COMMON_PATH."common.php");
require_once(COMMON_PATH."DBFactory.php");
require_once(COMMON_PATH."RedisQueue.php");




