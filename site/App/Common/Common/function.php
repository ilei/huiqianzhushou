<?php

/**
 * 生成GUID
 * @return string
 *
 * CT: 2014-09-13 15:00 by YLX
 * UT: 2014-09-17 10:30 by YLX
 */
function create_guid()
{
    mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
    $charid = strtoupper(md5(uniqid(rand(), true)));
    //         $hyphen = chr(45);// "-"real_name_verify
    $uuid = substr($charid, 0, 8)
        . substr($charid, 8, 4)
        . substr($charid, 12, 4)
        . substr($charid, 16, 4)
        . substr($charid, 20, 12);
    return $uuid;
}

function create_guid_with_dash()
{
    mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
    $charid = strtoupper(md5(uniqid(rand(), true)));
    $hyphen = chr(45);// "-"
    $uuid   = substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12);
    return $uuid;
}

/**
 * 生成基础票号，后续需要循环加1
 * @param $id 业务ID
 * @return int
 * ct: 2015-03-12 17:30 by ylx
 */
function generate_ticket_num($id)
{
    return intval($id . date('YmdH') . '0000');
}

/**
 *
 * 检查参数是否是一个大于等于$min且小于等于$max的字符串
 *
 * @access protected
 * @param string $str 要检查的字符串
 * @param int $min 字符串最小长度
 * @param int $max 字符串最大长度
 * @return 成功：true；失败：false
 *
 * CT: 2014-09-19 11:33 by YLX
 *
 */
function check_string_len($str, $min, $max)
{
    if (is_string($str) && strlen($str) >= $min && strlen($str) <= $max) {
        return true;
    }
    return false;
}


/**
 * java hashcode
 *
 * @param $s
 * @return int
 * CT: 2014-11-05 15:33 by YLX
 */
function hashCode($s)
{
    $len  = strlen($s);
    $hash = 0;
    for ($i = 0; $i < $len; $i++) {
        //一定要转成整型
        $hash = (int)($hash * 31 + ord($s[$i]));
        //64bit下判断符号位
        if (($hash & 0x80000000) == 0) {
            //正数取前31位即可
            $hash &= 0x7fffffff;
        } else {
            //负数取前31位后要根据最小负数值转换下
            $hash = ($hash & 0x7fffffff) - 2147483648;
        }
    }
    return $hash;
}

/**
 * 生成环信密码
 * @param $pwd 用户在酷客会签数据库中的密码
 * @return int
 */
function generateEasemobPwd($pwd)
{
    return hashCode($pwd);
}

/**
 * php异步访问URL
 *
 * $host: 要访问的域名
 * $url: 要访问的域名后部分
 *
 * 如要访问http://example.com/home, $host=example.com, $url=/home
 *
 * CT: 2014-09-28 09:33 by YLX
 */
function exec_url($host, $url)
{
    $fp = fsockopen($host, 80, $errno, $errstr, 30);
    if (!$fp) {
        echo "$errstr ($errno)<br />\n";
    } else {
        $out = "GET $url  / HTTP/1.1\r\n";
        $out .= "Host:$host\r\n";
        $out .= "Connection: Close\r\n\r\n";

        fwrite($fp, $out);
        fclose($fp);
    }
}

/**
 * 闪消息, 存储时间短, 用于提示成功或错误信息
 * $type : success:成功消息, error:失败消息, notice:通知消息
 * $msg: 消息内容
 * $name: 消息名称, 默认为flash
 * $expire: 消息存在时间, 默认为3秒
 *
 * CT: 2014-10-08 14:33 by YLX
 */
function set_flash_msg($type, $msg, $name = 'flash', $expire = 3)
{
    session($name, null);
    $data = array('type' => $type, 'msg' => $msg);
    session($name, $data);
}

/**
 * 获取闪消息
 * $name: 消息名称, 默认为flash
 *
 * CT: 2014-10-08 16:43 by YLX
 */
function get_flash_msg($name = 'flash')
{
    $res = session($name);
    session($name, null);
    return $res;
}


/**
 * 获取图片地址
 * @param $name 图片名称
 * @param string $size 头像大小，默认为原图
 * @param string $placeholder 默认图片
 * $type: 头像类别: 1为社团LOGO, 2为社团认证图片, 3为普通手机用户头像, 4社团logo原始版, 5待续
 * @return string
 * CT: 2014-10-10 16:43 by YLX
 * ut: 2015-07-20 11:00 by YLX
 */
// function get_image_path_common($name, $size = 'origin', $placeholder = 'noportrait.png')
// {
//     $path = get_placeholder($placeholder);

//     if (empty($name)) {
//         return $path;
//     }

//     $file = UPLOAD_PATH . $name;
//     if (file_exists($file)) {
//         if ($size == 'origin') { // 返回原图
//             $path = '/Upload' . $name;
//         } else {
//             //获取文件后缀
//             $ext      = getFileExt($name);
//             $endPos   = strrpos($name, '.');
//             $filePath = substr($name, 0, $endPos);
//             $path     = '/Upload' . $filePath . '_' . $size . '.' . $ext;
//         }
//     }
//     return $path;
// }

/**
 * 获取头像地址
 * @param $name 图片名称
 * @param string $size 头像大小，默认为原图
 * @param string $placeholder 默认图片
 * $type: 头像类别: 1为社团LOGO, 2为社团认证图片, 3为普通手机用户头像, 4社团logo原始版, 5待续
 * @return string
 * CT: 2014-10-10 16:43 by YLX
 * ut: 2015-07-20 11:00 by YLX
 */
// function get_image_path($name, $size = 'origin', $placeholder = 'noportrait.png')
// {
//     $alen=strlen($name);
//     if ($alen < 25) {
//         $path = $name;
//         return $path;
//     }
//     $path = get_image_path_common($name, $size, $placeholder);
//     return $path.'?'.time();
// }

/**
 * 获取头像地址
 * @param $name 图片名称
 * @param string $size 头像大小，默认为原图
 * @param string $placeholder 默认图片
 * $type: 头像类别: 1为社团LOGO, 2为社团认证图片, 3为普通手机用户头像, 4社团logo原始版, 5待续
 * @return string
 * CT: 2014-10-10 16:43 by YLX
 * ut: 2015-07-20 11:00 by YLX
 */
function get_image_path($name, $size = 'origin', $placeholder = 'noportrait.png')
{
    if($size != 1) {
        $size = 'origin';
        $placeholder = $size;
    }
    // echo $name;
    $path = get_placeholder($placeholder);

    if (empty($name)) {
        return $path;
    }
    
     $str = substr($name,1,6);
     if ($str == 'Public') {
            $path = $name;
            return $path;
     }

    $alen=strlen($name);
    if ($alen < 25) {
        $path = $name;
        return $path;
    }

    $file = UPLOAD_PATH . $name;
    if (file_exists($file)) {
        if ($size == 'origin') { // 返回原图
            //$path = '/Upload' . $name . '?' . time();
            $path = '/Upload' . $name;
        } else {
            //获取文件后缀
            $ext      = getFileExt($name);
            $endPos   = strrpos($name, '.');
            $filePath = substr($name, 0, $endPos);
            //$path     = '/Upload' . $filePath .  '.' . $ext . '?' . time();
            $path     = '/Upload' . $filePath .  '.' . $ext;
        }
    }
    return $path;
}

/**
 * API接口专供
 * @param $name
 * @param string $size
 * @param string $placeholder
 * @return string
 */
function getImagePathForAPI($name, $size = 'origin', $placeholder = 'noportrait.jpg')
{

    $path = get_placeholder($placeholder);

    if (empty($name)) {
        return $path;
    }

    $file = UPLOAD_PATH . $name;
    if (file_exists($file)) {
        if ($size == 'origin') { // 返回原图
            $path = $name . '?' . time();
        } else {
            //获取文件后缀
            $ext      = getFileExt($name);
            $endPos   = strrpos($name, '.');
            $filePath = substr($name, 0, $endPos);
            $path     = $filePath . '_' . $size . '.' . $ext . '?' . time();
        }
    }
    return $path;
}

/**
 * 获取placeholder
 * CT: 2014-10-16 10:43 by YLX
 * @return string
 */
function get_placeholder($filename = 'noportrait.png')
{
    if (file_exists(PUBLIC_PATH . '/common/images/' . $filename)) {
        return PUBLIC_URL . '/common/images/' . $filename;
    } else {
        return PUBLIC_URL . '/common/images/portraitup.png';
    }
}

function getFileExt($file)
{
    $ext = substr($file, strrpos($file, '.') + 1);
    return $ext;
}

/**
 * 获取区域名称
 * @param unknown $areaid_1 一级区域id
 * @param unknown $areaid_2 二级区域id
 *
 * CT: 2014-10-14 15:13 by YLX
 */
function get_full_area($areaid_1, $areaid_2)
{
    $area_1 = get_area($areaid_1);
    $area_2 = get_area($areaid_2);
    return $area_1 . ' ' . $area_2;
}

function get_area($area_id)
{
    if (empty($area_id)) return '';
    $res = M('Area')->find($area_id);
    if (!empty($res['name'])) {
        return $res['name'];
    }
    return '';
}

function api_get_full_area($area_id)
{
    return $area_id . ',' . get_area($area_id);
}

function get_sex($sex)
{
    $arr = array('0' => '男', '1' => '女');
    return $arr[$sex];
}

/**
 * 把APP端传来的json串转成数组
 *
 * CT: 2014-10-16 17:53 by YLX
 */
function api_json_explode($json)
{
    $json = str_replace('&quot;', '"', $json);
    return json_decode($json, true);
}

/**
 * 生成绝对路径的地址
 *
 * @param $url
 * @param string $vars
 * @param bool $suffix
 * @return string
 *
 * CT: 2014-11-01 17:53 by YLX
 */
function u_abs($url, $vars = '', $suffix = true)
{
    return U($url, $vars, $suffix, true);
}

/**
 * 时间格式 现实几分钟前 几秒前 几天前
 *
 * CT: 2014-10-31 16:53 by Qiu
 */
function mdate($time = NULL)
{

    $text = '';

    $time = $time === NULL || $time > time() ? time() : intval($time);

    $t = time() - $time; //时间差 （秒）

    $y = date('Y', $time) - date('Y', time());//是否跨年

    switch ($t) {

    case $t == 0:

        $text = '刚刚';

        break;

    case $t < 60:

        $text = $t . '秒前'; // 一分钟内

        break;

    case $t < 60 * 60:

        $text = floor($t / 60) . '分钟前'; //一小时内

        break;

    case $t < 60 * 60 * 24:

        $text = floor($t / (60 * 60)) . '小时前'; // 一天内

        break;

    case $t < 60 * 60 * 24 * 3:

        $text = floor($time / (60 * 60 * 24)) == 1 ? '昨天 ' . date('H:i', $time) : '前天 ' . date('H:i', $time); //昨天和前天

        break;

    case $t < 60 * 60 * 24 * 30:

        $text = date('m月d日 H:i', $time); //一个月内

        break;

    case $t < 60 * 60 * 24 * 365 && $y == 0:

        $text = date('m月d日', $time); //一年内

        break;

    default:

        $text = date('Y年m月d日', $time); //一年以前

        break;

    }


    return $text;

}

/**
 * 根据生日计算年龄
 *
 * @param $date format: yyyy-mm-dd
 * @return int
 *
 * CT: 2014-11-10 11:01 by YLX
 */
function calc_age($date)
{
    return date_diff(date_create($date), date_create('today'))->y;
}


/**
 * 格式化发送日期
 *
 * @param $timestamp timestamp 时间戳
 * @return string
 *
 * CT: 2014-11-17 18:41 by qxl
 */
function formatDate($timestamp)
{
    $callBackText   = '';
    $timeDifference = time() - $timestamp;
    if ($timeDifference < 60) {
        $callBackText = $timeDifference . '秒前';
    } elseif ($timeDifference >= 60 && $timeDifference < 60 * 60) {
        $callBackText = floor(($timeDifference / 60)) . '分钟前';
    } else if ($timeDifference >= 60 * 60 && $timeDifference < 60 * 60 * 24) {
        $callBackText = floor(($timeDifference / (60 * 60))) . '小时前';
    } else if ($timeDifference >= 60 * 60 * 24) {
        $callBackText = floor(($timeDifference / (60 * 60 * 24))) . '天前';
    }
    return $callBackText;
}

/**
 * 获取社团两个虚拟分组的GUID: 0全部成员 & 1未分组成员
 * @param $org_guid
 * @return string
 */
function get_org_all_member_group_guid($org_guid)
{
    //    return md5($org_guid.'asjdofnwer9023l4234');
    return '0';
}

function get_org_other_member_group_guid($org_guid)
{
    //    return md5($org_guid.'-0pomyu-tkup567567');
    return '1';
}

/**
 * JSON串化
 * @param mixd $arr
 * @return string
 */
function jsonEncode($arr)
{
    $json = json_encode($arr);
    //return $json;
    $json = str_replace(array('\r', '\n', '\t'), '', $json);
    //$json = str_replace(array('"{','}"'), array('{','}'), $json);
    return preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $json);
}

/**
 * 获取HTTP REQUEST HEADERS
 * CT: 2014-12-19 09:21 BY YLX
 */
function get_request_headers()
{
    if (!function_exists('getallheaders')) {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
    return getallheaders();
}

/**
 * 获取手机验证码
 * CT: 2014-12-30 11:35 BY QXL
 */
function get_mobile_code()
{
    $num = '0123456789';
    return substr(str_shuffle($num), 0, 6);
}

function creat_QRcode($condition)
{
    return 'http://qr.liantu.com/api.php?' . http_build_query($condition);
}

/**
 * 生成二维码
 * @param $qr_path 二维码保存路径
 * @param $qr_name 二维码图片名称
 * @param string $text 二维码要转存的内容
 * @param bool $logo 二维码中间logo
 * @param string $size 图片每个黑点的像素,默认5
 * @param string $level //纠错级别， 纠错级别越高，生成图片会越大
 * //L水平    7%的字码可被修正
 * //M水平    15%的字码可被修正
 * //Q水平    25%的字码可被修正
 * //H水平    30%的字码可被修正
 * @param int $padding 图片外围空白大小，默认2
 * @param bool $header 是否调用header函数
 * @return bool
 */
function qrcode($text = '', $qr_path = false, $qr_name = false, $logo = false, $size = '5', $level = 'L', $padding = 2, $header = true,$contentType=null)
{
    if (empty($text)) {
        $text = C('SITE_HOST_URL');
    }
    if (!is_dir($qr_path)) {
        $result = mkdir($qr_path, 0777, true);
        if (!$result) {
            return false;
        }
    }
    $QR = $qr_path . '/' . $qr_name;
    if (!is_file($QR)) {
        vendor("phpqrcode.phpqrcode");
        \QRcode::png($text, $QR, $level, $size, $padding);
    }


    if ($logo !== false) {
        $QR             = imagecreatefromstring(file_get_contents($QR));
        $logo           = imagecreatefromstring(file_get_contents($logo));
        $QR_width       = imagesx($QR);
        $QR_height      = imagesy($QR);
        $logo_width     = imagesx($logo);
        $logo_height    = imagesy($logo);
        $logo_qr_width  = $QR_width / 5;
        $scale          = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width     = ($QR_width - $logo_qr_width) / 2;
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
    } else {
        $QR = imagecreatefromstring(file_get_contents($QR));
    }


    if((!empty($qr_path))&&(!empty($qr_name))&&(!$header)){
        return imagepng($QR,$qr_path.$qr_name);
    }else {
        if($contentType){
            header("Content-Disposition: attachment; filename=" . $qr_name);
            header("Content-Type:".$contentType);
        }else{
            header("Content-Type:image/jpg");
        }

        return imagepng($QR);
    }
}


/**
 * 通过查找元素值删除数组元素
 * @param $array
 * @param $value
 */
function unset_array_value($array, $value)
{
    if (($key = array_search($value, $array)) !== false) {
        unset($array[$key]);
    }
}

/**
 * 获取短域名
 * @param $url
 */
function get_short_url($url)
{
    $api      = "http://api.t.sina.com.cn/short_url/shorten.json?source=3180327085&url_long=$url";
    $res      = file_get_contents($api);
    $url_data = json_decode($res, true);
    return $url_data[0]['url_short'];
}

/**
 * 检查是否为正确格式邮箱
 * @param $email
 * @return mixed
 * CT: 2015-03-24 10:34 by ylx
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * 发送电子邮箱
 * @param $to_emails 邮件地址数组
 * @param $from_name 发送人姓名
 * @param $subject 邮件标题
 * @param $content 邮件内容
 * @return mixed
 * CT: 2015-03-24 10:34 by ylx
 */
function send_email($to_emails, $from_name, $subject, $content)
{


    vendor('submail.lib.mailsend');
    $submail = new \MAILSend(C('SUBMAIL_CONFIG'));
    if (!is_array($to_emails)) {
        $submail->AddTo($to_emails);
    } else {
        foreach ($to_emails as $email) {
            $submail->AddTo($email);
        }
    }
    $submail->SetSender(C('MAIL_SENDER'), $from_name);
    $submail->SetSubject($subject);
    $submail->SetText($content);
    $submail->SetHtml($content);
    return $submail->send();
}

/**
 * 生成活动电子票, 总长度为19位
 * @param string $prefix 电子票前缀, 必须为4位, 否则会取前4位
 * @return string
 */
function generate_ticket_code($prefix = '')
{
    if (strlen($prefix) != 4) {
        $prefix = substr($prefix, 0, 4);
    }
    $today_date = date("dm");
    $today_time = date("Hs");
    $rand       = mt_rand(12010, 98710);
    $rand2      = mt_rand(10, 90);

    return $prefix . $rand2 . $today_date . $today_time . $rand;
}

/**
 * 格式化显示textarea, 替换折行回车等
 * @param $text 要格式化的内容
 * @param string $replace 要替换成的内容
 * CT: 2015-05-06 11:00 by ylx
 */
function ym_str_replace_textarea($text, $replace = '<br />')
{
    $search = array("\r\n", "\n", "\r");
    echo str_replace($search, $replace, $text);
}

/**
 * 截取中文字符串
 * @param $str 要截取的字符串
 * @param $length 要截取的长度
 * @param string $replace 截取后要替换成的内容
 * @return string
 * CT: 2015-05-06 11:00 by ylx
 */
function ym_mb_substr($str, $length, $replace = '...')
{
    if (mb_strlen($str, 'UTF-8') > $length) {
        return mb_substr($str, 0, $length, 'UTF-8') . $replace;
    }
    return $str;
}

//在线交易订单支付处理函数
//函数功能：根据支付接口传回的数据判断该订单是否已经支付成功；
//返回值：如果订单已经成功支付，返回true，否则返回false；
function checkorderstatus($sn)
{
    $Ord       = M('Order');
    $ordstatus = $Ord->where('sn=' . $sn)->getField('status');
    if ($ordstatus == 1) {
        return true;
    } else {
        return false;
    }
}

//处理订单函数
//更新订单状态，写入订单支付后返回的数据
function orderhandle($parameter)
{
    $sn                           = $parameter['out_trade_no'];
    $data['payment_trade_no']     = $parameter['trade_no'];
    $data['payment_trade_status'] = $parameter['trade_status'];
    $data['payment_notify_id']    = $parameter['notify_id'];
    $data['payment_notify_time']  = $parameter['notify_time'];
    $data['payment_buyer_email']  = $parameter['buyer_email'];
    $data['ordstatus']            = 1;
    $Ord                          = M('Order');
    $Ord->where('sn=' . $sn)->save($data);
}

/**
 * 生成订单号
 * @return string
 */
function generate_order_sn()
{
    $Ord     = M('Order');
    $numbers = range(10, 99);
    shuffle($numbers);
    $code    = array_slice($numbers, 0, 4);
    $sn      = date('YmdHis') . $code[0] . $code[1] . $code[2] . $code[3];
    $oldcode = $Ord->where("sn='" . $sn . "'")->getField('sn');
    if ($oldcode) {
        getordcode();
    } else {
        return $sn;
    }
}


/**
 * 下载文件
 * 可以指定下载显示的文件名，并自动发送相应的Header信息
 * 如果指定了content参数，则下载该参数的内容
 * @static
 * @access public
 * @param string $filename 下载文件名
 * @param string $showname 下载显示的文件名
 * @param string $content 下载的内容
 * @param integer $expire 下载内容浏览器缓存时间
 * @return void
 */
function download($filename, $showname = '', $content = '', $expire = 180, $file_type = '')
{
    if (is_file($filename)) {
        $length = filesize($filename);
    } elseif (is_file(UPLOAD_PATH . $filename)) {
        $filename = UPLOAD_PATH . $filename;
        $length   = filesize($filename);
    } elseif ($content != '') {
        $length = strlen($content);
    } else {
        E($filename . L('下载文件不存在！'));
    }
    if (empty($showname)) {
        $showname = $filename;
    }
    $showname = basename($showname);
    $type     = $file_type ? $file_type : "application/octet-stream";
    //发送Http Header信息 开始下载
    header("Pragma: public");
    header("Cache-control: max-age=" . $expire);
    //header('Cache-Control: no-store, no-cache, must-revalidate');
    header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expire) . "GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()) . "GMT");
    header("Content-Disposition: attachment; filename=" . $showname);
    header("Content-Length: " . $length);
    header("Content-type: " . $type);
    header('Content-Encoding: none');
    header("Content-Transfer-Encoding: binary");
    if ($content == '') {
        readfile($filename);
    } else {
        echo($content);
    }
    exit();
}

/**
 * 转换字节数为其他单位
 *
 *
 * @param string $filesize 字节大小
 * @return string 返回大小
 */
function sizecount($filesize)
{
    if ($filesize >= 1073741824) {
        $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
    } elseif ($filesize >= 1048576) {
        $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
    } elseif ($filesize >= 1024) {
        $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
    } else {
        $filesize = $filesize . ' Bytes';
    }
    return $filesize;
}

/**
 * 生成电子票的二维码
 *
 * @access string $ticket_url 电子票地址
 * @access string $ticket_guid 电子票的id
 * @return void
 **/
function create_ticket_qrcode($ticket_url, $ticket_guid)
{
    $qr_path = '/org/qrcode/activity/ticket';
    $qr_name = $ticket_guid . '.png';
    return qrcode($ticket_url, UPLOAD_PATH . $qr_path, $qr_name, false, 5, 'L', 2, false);
    die();
}


/**
 * 取得redis对象
 *
 * @param string $host
 * @param string $port
 * @return object
 **/
function & get_redis($host = '127.0.0.1', $port = '6379')
{
    static $_redis = null;
    if (!$_redis) {
        $_redis = new Redis();
        $_redis->connect($host, $port);
    }
    return $_redis;
}

/**
 * 取得限制词
 *    1、直接从文件中读取
 *    2、从redis缓存中读取
 *
 * @param  void
 * @return array
 **/
function get_black_words()
{
    $words = M('BannedWords')->getField('id,words');
    if (!$words) {
        $excel = D('Excel');
        $words = $excel->import(PUBLIC_PATH . '/data/emaybannedwords.xlsx');
        $words = array_unique($words);
        foreach($words as $key => $value){
            $data = array(
                'guid'         => create_guid(),
                'words'        => $value,	
                'status'       => 1,
                'created_time' => time(),
                'updated_time' => time(),
            );
            M('BannedWords')->data($data)->add();	
        }
    }
    return $words;
}

/**
 * 判断字符串中是否包含限制词
 *
 * @param string $content 要判断的字符串
 * @param array $words 限制词数组
 * @return mixed
 **/

function censor_words($content, $words = array())
{
    $words = $words ? $words : get_black_words();
    foreach ($words as $word) {
        if (strpos($content, $word) !== false) {
            return $word;
        }
    }
    return false;
}

/**
 * 单位转换，元到分 分到元 
 *
 * @param  int $nums 
 * @param  bool $direction true 元到分 false 分到元
 * @return int 
 **/ 

function yuan_to_fen($nums, $direction = true){
    if(!$nums || !is_numeric($nums) || $nums <= 0){
        return 0;
    }
    if(!$direction){
        if($nums % 100 == 0){
            return $nums / 100;	
        }	
        return sprintf("%.2f", $nums/100);
    }
    return intval(floatval($nums)*100);
}


/**
 * 生成订单号 28位 
 * 	当前时间到秒+4位微妙+7位随机数
 * 
 * @param void 
 * @return 订单号
 **/ 

function create_order_id(){
    $time = explode('.', microtime(true));
    $esc  = current($time);
    $mesc = next($time);
    $res  = date('YmdHis', $esc) . str_pad($mesc, 4, '0') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
    return substr($res, 0, 25);
}

/**
 * 格式IP 
 *
 * @param  string $ip 
 * @return array 
 **/ 

function format_ip($ip = ''){
    $ip = $ip ? $ip : get_client_ip();
    $ip = explode('.', $ip);
    return array(
        'ip1' => $ip[0],
        'ip2' => $ip[1],
        'ip3' => $ip[2],
        'ip4' => $ip[3],
    );

}

/**
 *
 * 用户充值操作log 
 *
 * @param   string $user_guid 操作者guid
 * @param   string $operatioln 操作码 
 * @param   array  $args  发送操作时涉及到的数据
 * @return  void 
 **/ 

function operation_log($user_guid, $operation, $args = array(), $desc = ''){
return true;
    $data = array(
        'user_guid'      => $user_guid,
        'operation_code' => $operation['code'],
        'operation_desc' => $operation['text'] ? $operation['text'] : $desc,
        'args'           => $args,
        'created_time'   => time(),
    );
    $model = D('OperationLog');	
    $res   =   $model->add($data);
    return $res;
}

/**
 *
 * 电子票消费操作log 
 *
 * @param   string $user_guid  操作者guid
 * @param   string $activity_guid 活动guid
 * @param   string $operatioln 操作码 
 * @param   array  $data      发送操作时涉及到的数据
 * @param   array  $res       发送结果
 * @param   string $desc      自定义描述
 * @return  void 
 **/ 

function ticket_send_log($user_guid, $activity_guid, $operation, $data = array(), $res = array(), $desc = ''){
return true;
    $data = array(
        'user_guid'      => $user_guid,
        'activity_guid'  => $activity_guid,
        'operation_code' => $operation['code'],
        'operation_desc' => $operation['text'] ? $operation['text'] : $desc,
        'data'           => $data,
        'res'            => $res,
        'created_time'   => time(),
    );
    $model = D('TicketLog');	
    $res   =   $model->add($data);
    return $res;
}


/**
 * 检测post数据 
 **/ 

function validate_post($key, $default = null){
    return (isset($_POST[$key]) && $_POST[$key]) ? trim($_POST[$key]) : $default;	
}

/**
 * 检测get数据
 **/ 

function validate_get($key, $default = null){
    return (isset($_GET[$key]) && $_GET[$key]) ? trim($_GET[$key]) : $default;	
}

/**
 * 检测数组数据
 **/ 

function validate_data($arr, $key, $default = false){
    if($default === false){
        return 	isset($arr[$key]) && $arr[$key];
    }
    return (isset($arr[$key]) && $arr[$key]) ? trim($arr[$key]) : $default;	
}

/**
 *
 * 订单操作log 
 *
 * @param   string $user_guid 操作者guid
 * @param   string $operatioln 操作码 
 * @param   array  $args  发送操作时涉及到的数据
 * @return  void 
 **/ 

function order_operation_log($order_id, $operation, $args = array(), $desc = ''){
return true;
    $data = array(
        'order_id'       => $order_id, 
        'operation_code' => $operation['code'],
        'operation_desc' => $operation['text'] ? $operation['text'] : $desc,
        'args'           => $args,
        'created_time'   => time(),
    );
    $model = D('OrderOperationLog');	
    $res   =   $model->add($data);
    return $res;
}


/**
 *
 * 商品操作log 
 *
 * @param   string $goods_guid  商品guid
 * @param   string $operatioln 操作码 
 * @param   array  $args  发送操作时涉及到的数据
 * @return  void 
 **/ 

function goods_operation_log($goods_guid, $operation, $args = array(), $desc = ''){
return true;
    $data = array(
        'goods_id'       => $goods_guid, 
        'operation_code' => $operation['code'],
        'operation_desc' => $operation['text'] ? $operation['text'] : $desc,
        'args'           => $args,
        'created_time'   => time(),
    );
    $model = D('GoodsOperationLog');	
    $res   =   $model->add($data);
    return $res;
}


/**
 * 创建电子票
 *
 * @param  array $order 订单信息
 * @return true or false
 **/ 

function create_user_eticket($order, $ticket_guid = '', $userinfo = array()){
    $time = time();
    $user = D('ActivityUserinfo')->find_one(array('guid' => $order['buyer_guid']));
    if(!$user){
        return false;
    }
    $data_ticket = array(
        'guid'          => create_guid(),
        'user_guid'     => $order['user_guid'],
        'activity_guid' => $order['target_guid'],
        'userinfo_guid' => $order['buyer_guid'],
        'mobile'        => $user['mobile'],
        'ticket_guid'   => $ticket_guid,
        'goods_guid'    => $order['goods_guid'],
        'ticket_name'   => $order['goods_name'],
        'created_at'    => $time,
        'updated_at'    => $time,
        'status'        => isset($userinfo['user_ticket_status']) ? intval($userinfo['user_ticket_status']) : 0,
        'signin_status' => isset($userinfo['signin_status']) ? intval($userinfo['signin_status']) : 0,
        'ticket_code'   => generate_ticket_code(substr($order['target_guid'], 0, 4)),
        'real_name'     => $order['buyer_name'],
    );
    $price = D('Goods')->field('price')->where(array('guid' => $order['goods_guid']))->find()['price'];
    if($price > 0){
        $data_ticket['is_free'] = 0;//区分免费和收费票
    }else{
        $data_ticket['is_free'] = 1;
    }

    D('ActivityUserTicket')->create($data_ticket);
    $res = D('ActivityUserTicket')->add($data_ticket);
    if($res){
        return $data_ticket; 
    }
    return $res;
}

/**
 * 取得数组的维数
 *
 * @param  array 
 * @return int 
 **/ 
function get_arr_level($arr) {
    if (is_array($arr)) {       
        #递归将所有值置NULL，目的1、消除虚构层如array("array(\n  ()")，2、print_r 输出轻松点，
        array_walk_recursive($arr, function(&$val){ $val = NULL; });

        $ma = array();
        #从行首匹配[空白]至第一个左括号，要使用多行开关'm'
        preg_match_all("'^\(|^\s+\('m", print_r($arr, true), $ma);
        #回调转字符串长度
        //$arr_size = array_map('strlen', current($ma));
        #取出最大长度并减掉左括号占用的一位长度
        //$max_size = max($arr_size) - 1;
        #数组层间距以 8 个空格列，这里要加 1 个是因为 print_r 打印的第一层左括号在行首
        //return $max_size / 8 + 1;
        return (max(array_map('strlen', current($ma))) - 1) / 8 + 1;
    } else {
        return 0;
    }
}

/**
 * 限制访问次数通用函数 
 *
 * @param string $key  保存的key，保证唯一 
 * @param int    $nums 限制的次数 
 * @param int    $expire 超时时间
 * @return  boolean 
 **/ 

function request_nums_limit($key, $nums = 6, $expire = 10){
    $where   = "permanet_id = '{$key}'";
    $record  = M('Limit')->where($where)->find();
    if(!$record){
        $data = array(
            'permanet_id' => trim($key),
            'nums'        => 1,
            'max_nums'    => intval($nums),
            'expire'      => time() + $expire,
            'created_time' => time(),
            'updated_time' => time(),
            'status'       => 1,
        );	
        M('Limit')->data($data)->add();
        return true; 
    }
    $expired = intval($record['expire']) > time() ? false : true;
    if($expired){
        $data = array(
            'nums'     => 1,
            'max_nums' => intval($nums),
            'expire'   => time() + $expire,
            'updated_time' => time(),
        );	
        M('Limit')->where($where)->save($data);
        return true;
    }
    $max_nums =  intval($record['max_nums']);
    $nums     =  intval($record['nums']) + 1; 
    if($nums < $max_nums){
        $data['nums'] = $nums;	
        M('Limit')->where($where)->save($data);
        return true;	
    }
    return false;
}

function download_img($source, $filename = ''){
    ob_start();
    readfile($source);
    $img = ob_get_contents();
    ob_end_clean();
    $ext = '.jpg';
    if(strlen(strrchr($source, '.')) <= 4){
        $ext = substr($source, strrpos($source, '.'));	
    }
    $dst_file = '';
    if(strlen($img)){
        $filename  = $filename ? $filename : md5($source) . $ext;
        $dst_file = '/user/' . date('Y_m_d') . '/' . substr($filename, 0, strpos($filename, '.')) . '/'; 
        check_path(UPLOAD_PATH . $dst_file);
        $fp = fopen(UPLOAD_PATH . $dst_file . $filename, 'w');	
        if($fp){
            fwrite($fp, $img);
            fclose($fp);
        }
    }
    return $dst_file . $filename;
}

/**
 * 检测目录，如不存在则创建  
 *
 * @param  string $path 路径 
 * @param  string $mode 权限
 * @return void 
 **/ 

function check_path($path, $mode = 0755){
    if(!is_dir($path)){
        check_path(dirname($path), $mode);
    }
    return @mkdir($path, $mode);
}

/**
 * 发送短信验证码 
 *
 * @param  int    $type   验证码类型，不同的类型使用不同的模板 参看C('CODE_TYPE');
 * @param  string $mobile 要发送的验证码的手机 
 * @param  array  $code   array(123453, 30)  第一个是验证码，第二个是有效时间
 * @param  array  $args   array('type' => 1 or 2) 1是短信验证码 2是语音验证码 
 * @return array 
 **/ 

function send_sms($type, $mobile, $data = array(), $args = array()){
    $curl = new Org\Api\YmCurl();
    $post = array($type, $mobile, $data, $args);
    $header = array('Content-Type' => 'application/x-www-form-urlencoded');
    $res = $curl->post(C('sender.meetelf') . 'sendSMS', $post, $header);
    return $res;
}

/**
 * 发送电子票(单发) 
 *
 * @param  array $data 电子票信息 
 * @param  array $args 其他扩展信息
 * @return array
 **/ 

function send_ticket($data, $args){
    $post = array($data, $args);
    $curl = new Org\Api\YmCurl();
    return $curl->post(C('sender.meetelf') . 'sendTicket', $post);
}

/**
 * 发送电子票(群发)异步发送 
 *
 * @param  array $data 电子票信息 
 * @param  array $args 其他扩展信息
 * @param  int   $level 发送等级,等级越高发送越及时 
 * @return array
 **/ 
function send_list_ticket($data, $args, $level = 1){
    $post = array('meetelf', 'ticket', $data, $args, $level);
    $curl = new Org\Api\Curl();
    $res  = $curl->postRequest(C('sender.meetelf') . 'setList', $post); 
    return $res; 
}

/**
 * 储存或者获取验证码 
 *
 * @param   string $key 
 * @param   mixed  $code  $code === false 获取验证码 
 * @param   int    $expire 过期时间
 **/ 

function mobile_code($key, $code = false, $expire = 1800){
    $now   = time();
    $where = "guid ='{$key}'";	
    $res   = M('CodeCheck')->where($where)->order('created_time DESC')->find();	
    if($code === false){
        return (!$res || $res['expire'] <= $now || $res['status'] != 1) ? false : $res['code'];
    }	
    if($res){
        M('CodeCheck')->where($where)->save(array('status' => 0, 'updated_time' => time()));	
    }
    $data  = array(
        'guid' => $key,
        'code' => $code,
        'expire' => $now+$expire, 
        'created_time' => time(),
        'updated_time' => time(),
        'status'       => 1,	
    );
    return	M('CodeCheck')->data($data)->add();	
}

/**
 * 获得全部主办方信息
 *
 * @param  sring $user_guid 
 * @return array
 **/

function get_organizer_info($user_guid = ''){
    if(!$user_guid){
        return false;
    }
    return M('OrganizerInfo')->where("user_guid = '{$user_guid}' AND status = 1")->getField('id,guid,name');
}

/**
 * 获得主办方名称 
 *
 * @param  string $guid 
 * @return string 
 **/ 

function get_organizer_name($guid = ''){
    if(!$guid){
        return false;
    }
    return M('OrganizerInfo')->where("guid = '{$guid}'")->getField('name');
}

/**
 * 调用多级语言
 * 
 * @param  string $name aaa.bbb.c aa.bb.0
 * @return mixed
 **/

function meetelf_lang($name){
    $keys  = explode('.', trim($name, '.'));
    $lang  = L(array_shift($keys));
    while(!is_null($key = array_shift($keys))) {
        if(is_array($lang) && array_key_exists($key, $lang)) {
            $lang = $lang[$key];
        } else {
            return FALSE;
        }
    }
    return $lang;
} 
/**
 * 日期截取 
 *
 * @param  array $time  包含开始和结束时间 
 * @param  string $format 要以空格隔开 
 * @param  string $ds     分隔符
 * @return string 
 **/ 


function weekday($time = array(), $format = 'Y年m月d日 星期{w}  H:i:s', $ds = '~'){
    list($start, $end) = $time;
    $start = $start ? $start : time();
    if(!$end){
        return weekday_ch($start, $format);
    } 
    $res = $end-$start;
    if($res < 0){
        $tmp = $end;
        $end = $start;
        $start = $tmp;
    }
    $res = $res >= 0 ? $res : -$res;
    list($ymd, $week, $his) = array_values(array_filter(explode(' ', $format)));
    if($res == 0){
        return weekday_ch($start, $format); 
    }elseif($res < 24*3600){
        return weekday_ch($start, $format) . ' ' . $ds . ' ' . date($his, $end); 
    }else{
        return weekday_ch($start, $format) . ' ' . $ds . ' ' . weekday_ch($end, $format); 
    }
}

/**
 * 返回汉语星期 
 *
 * @param string $time 
 * @param string $format 
 * @return string 星期几
 **/ 

function weekday_ch($time, $format = '星期{w}'){
    $time = $time ? date($format, $time) : date($format, time());
    return preg_replace_callback('/\{([0-6]{1})\}/', function($match){
        $a = array('日', '一', '二' , '三', '四' , '五', '六');
        return $a[$match[1]]; 
    }, $time);

}
/**
 * 创建和array_column函数相似的功能,由于低版本php不支持array_column 
 *
 * @param  array  $array 
 * @param  string $column_key 
 * @param  string $index_key 
 * @return array
 **/ 

function array_columns($array, $column_key = null, $index_key = null){
    return array_reduce($array, function ($result, $item) use ($column_key, $index_key){
        if($column_key && $index_key){
            $result[$item[$index_key]] = $item[$column_key];
        }elseif($column_key === null && $index_key){
            $result[$item[$index_key]] = $item; 
        }elseif (null === $index_key && $column_key) {
            $result[] = $item[$column_key];
        }
        return $result;
    }, array());
}

/**
 * 作为array_columns的补充 将key=>value形式的数组  以map[primarykey,value(array)]的形式进行分组
 * @param $array array 数据源数组
 * @param $primary_key string
 * @param null $column_key string
 * @return array
 */
function array_to_map($array,$primary_key,$column_key=null){
    $result=array();

    if(!empty($array)) {
        foreach ($array as $value) {
            $map_key = $value[$primary_key];
            $map_value = empty($column_key) ? $value : $value[$column_key];
            if (array_key_exists($map_key,$result)) {
                array_push($result[$map_key], $map_value);
            } else {
                $result[$map_key] = array($map_value);
            }
        }
    }
    return $result;
}


/**
 * 编码ID为五位数
 * @param $id
 */
function event_id_encode($id){
    $id=intval($id);
    if(is_nan($id)){
        return 9999;
    }else{
        return 9999+$id;
    }
}

/**
 * 解码ID
 * @param $id
 */
function event_id_decode($id){
    $id=intval($id);
    if(is_nan($id)){
        return 0;
    }
    else{
        return $id-9999;
    }

}

/**
 * 检测是否是手机访问 
 *
 * @param void 
 * @return bool 
 **/ 

function is_mobile_request() { 
    $res = false;
    $_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : ''; 
    $pattern = '/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i';
    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4)); 
    $mobile_agents = array( 
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac', 
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno', 
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-', 
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-', 
        'newt','noki','oper','palm','pana','pant','phil','play','port','prox', 
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar', 
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-', 
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp', 
        'wapr','webc','winw','winw','xda','xda-' 
    ); 
    if(preg_match($pattern, strtolower($_SERVER['HTTP_USER_AGENT']))){
        $res = true;
    }elseif(isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false){
        $res = true;
    }elseif(isset($_SERVER['HTTP_X_WAP_PROFILE'])){
        $res = true;
    }elseif(isset($_SERVER['HTTP_PROFILE'])){
        $res = true;
    }elseif(in_array($mobile_ua, $mobile_agents)){
        $res = true;
    }elseif(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false){ 
        $res = true;
    }elseif(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false) {
        $res = true;
    } 
    return $res;
}


/**
 * Respose A Http Request
 *
 * @param string $url
 * @param array $post
 * @param string $method
 * @param bool $returnHeader
 * @param string $cookie
 * @param bool $bysocket
 * @param string $ip
 * @param integer $timeout
 * @param bool $block
 * @return string Response
 */
function httpRequest($url,$post='',$method='GET',$limit=0,$returnHeader=FALSE,$cookie='',$bysocket=FALSE,$ip='',$timeout=30,$block=TRUE) {
    $return = '';
    $matches = parse_url($url);

    !isset($matches['host']) && $matches['host'] = '';
    !isset($matches['path']) && $matches['path'] = '';
    !isset($matches['query']) && $matches['query'] = '';
    !isset($matches['port']) && $matches['port'] = '';

    $host = $matches['host'];
    $path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
    $port = !empty($matches['port']) ? $matches['port'] : 80;

    if(strtolower($method) == 'post') {
        $post = (is_array($post) and !empty($post)) ? http_build_query($post) : $post;
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= 'Content-Length: '.strlen($post)."\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cache-Control: no-cache\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
        $out .= $post;
    } else {
        $out = "GET $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Cookie: $cookie\r\n\r\n";
    }

    $fp = fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);

    if(!$fp) return ''; else {
        $header = $content = '';

        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        fwrite($fp, $out);
        $status = stream_get_meta_data($fp);

        if(!$status['timed_out']) {//未超时
            while (!feof($fp)) {
                $header .= $h = fgets($fp);
                if($h && ($h == "\r\n" ||  $h == "\n")) break;
            }

            $stop = false;
            while(!feof($fp) && !$stop) {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $content .= $data;
                if($limit) {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }
        fclose($fp);

        return $returnHeader ? array($header,$content) : $content;
    }
}

/**
 * 活动状态html显示 
 *
 * @param $status 
 * @return html
 **/ 

function activity_status($status = 0){
    $start = '<span class="elf-act-';
    $end   = '">' . C('act_text.' . $status) . '</span>';
    switch($status){
        case 0:
            $class = 'audit';
            break;
        case 1:
            $class = 'go';
            break;
        case 2:
        case 3:
            $class = 'end';
            break;
        default:
            $class = 'audit';
    }
    return $start . $class . $end;
}

/**
 * 日期格式化函数 
 *  务必遵守格式，否则可能显示不正确
 *
 * @param  array   $time   array('start', 'end')
 * @param  array   $format array('ymd' => 'Y-m-d', 'week' => 'w', 'his' => 'H:i:s')
 * @param  string  $ds     分隔符
 **/

function mtf_date_format($time, $format, $ds = '~' ){
    if(!$time ||!$format){
        return false; 
    }
    list($start, $end) = $time;
    $start  = $start ? $start : time();
    if(!$end){
        $format =  implode(' ', (array_filter($format)));
        return date($format, $start);
    }
    $res   = $end-$start;
    if($res < 0){
        $tmp = $end;
        $end = $start;
        $start = $tmp;
    }
    $res = $res >= 0 ? $res : -$res;
    extract($format);
    $format =  implode(' ', (array_filter($format)));


    //计算天数差 自然日 绝对值解决跨天/跨月等各种跨引起的计算问题 只要有差值 无论正负皆是跨天了
    $day_diff=abs(date('d',$end)-date('d',$start));

    if($res == 0){
        return date($format, $start);
    }elseif($day_diff<1){
        $date = date($format, $start);
        if(isset($his) && $his){
           $date .= ' ' . $ds . ' ' . date($his, $end); 
        }elseif(isset($week) && $week){
           $date .= ' ' . $ds . ' ' . date($week, $end); 
        }
        return $date;
    }else{
        return date($format, $start) . ' ' . $ds . ' ' . date($format, $end); 
    }
}

/**
 * @param $url string 资源URL
 * @return string Fixed Url
 */
function fixed_resource_url($url){
    // 确定协议
    $protocol_string=$_SERVER['HTTPS'] !='on' ?'http://':'https://' ;
    //获取域名
    $host_string=$_SERVER['HTTP_HOST'];

    return $protocol_string.$host_string.$url;
}

/**
 * 添加短信内容 
 *
 * @param  array $activity
 * @param  array $userinfo
 * @return mixed
 **/

function create_msg_content($ticket_guid, $userinfo, $app_name = ''){
    if($ticket_guid && $userinfo){
        $activity = M('Activity')->where(array('guid' => $userinfo['activity_guid']))->find();
         $ticket_url = U(C('meetelf_url') . '/Mobile/Activity/ticket', array('aid' => $activity['guid'], 'iid' => $userinfo['guid']), true, true);
         $sms_url    = get_short_url($ticket_url . '/source/1');
         $email_url  = get_short_url($ticket_url . '/source/2'); 
        $data = array(
            'guid' => create_guid(),
            'title'=> $activity['name'], 
            'sms_url'  => $sms_url,
            'email_url' => $email_url, 
            'mobile'    => $userinfo['mobile'],
            'email'    => $userinfo['email'],
            'status'   => 1,
            'ticket_guid' => $ticket_guid, 
            'activity_guid' => $activity['guid'],
            'user_guid' => $userinfo['user_guid'],
            'account_guid' => $activity['user_guid'],
            'app_name' => $app_name ? $app_name : C('APP_NAME'),
        ); 
         if($data['email']){
             $curl = new Org\Api\Curl();
             $post = array($ticket_guid, $activity, $userinfo);
             $url = U('Sender/Meetelf/get_email_content', '', true, true);
             $res = $curl->postRequest($url, $post);
             $data['content'] = $res[$url]; 
         }
        return M('MsgContent')->data($data)->add();
    }
}

/**
 * @ $filename string 文件名称
 */
function export_csv($filename, $data){
    header("Content-type:text/csv");
    header("Content-Disposition:attachment;filename=".$filename);
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
    header('Expires:0');
    header('Pragma:public');
    echo $data;
    exit;

}
