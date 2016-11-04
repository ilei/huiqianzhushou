<?php
// +----------------------------------------------------------------------
// | Meetelf Framework [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011-2014 Meetelf Team (http://www..com)
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author wangleiming<wangleiming@yunmai365.com>
// +----------------------------------------------------------------------

/**
 * 循环检测目录是否存在，如不存在则创建
 *
 * @param  string $path 目录
 * @param  int $mode 目录权限
 * @return boolean
 **/

if (!function_exists('check_path')) {
    function check_path($path, $mode = 0755)
    {
        if (!is_dir($path)) {
            check_path(dirname($path), $mode);
        }
        return @mkdir($path, $mode);
    }
}

/**
 * 初始化redis并连接
 *
 * @param  void
 * @return resource $redis
 **/

if (!function_exists('init_redis')) {
    function init_redis($config = array())
    {
//        static $_redis = array();
//        $key = !$config ? md5('config') : md5(implode('', $config));
//        if (isset($_redis[$key]) && $_redis[$key]) {
//            return $_redis[$key];
//        }
        //加载redis配置
        $config = $config ? $config : Loader::load_config('redis');
        $redis = new Redis();
        $redis->connect($config['host'], $config['port']);

        $redis->auth($config['pwd']);


        return $redis;
    }
}

if (!function_exists('my_mysql_query')) {
    function my_mysql_query($sql, $db = null)
    {
        //$db = $db ? $db : init_db();
        $db = init_db();
        $result = mysql_query($sql, $db);
        $data = array();
        if ($result) {
            while ($row = mysql_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
}

if (!function_exists('my_mysql_insert')) {
    function my_mysql_insert($sql, $db = null)
    {
        //$db = $db ? $db : init_db();
        $db = init_db();
        $result = mysql_query($sql, $db);
        return mysql_insert_id($db);
    }
}

if (!function_exists('my_mysql_update')) {
    function my_mysql_update($sql, $db = null)
    {
        //$db = $db ? $db : init_db();
        //占时先用着
        $db = init_db();
        $result = mysql_query($sql, $db);
        return mysql_affected_rows($db);
    }
}

if (!function_exists('init_db')) {
    function & init_db($config = array())
    {
        $config = empty($config) ? Loader::load_config('db') : $config;
        if (empty($config)) {
            return false;
        }
//        static $_db = null;
//        if ($_db) {
//            return $_db;
//        }


        //获取Host
        $host = $config['port'] ? $config['host'] . ':' . $config['port'] : $config['host'];
        $user=$config['username'];
        $pwd=$config['password'];
        $db_name=$config['dbname'];

//
//        var_dump($host);
//        var_dump($user);
//        var_dump($pwd);
//        var_dump($db_name);


//        $host = 'www0.meetelf.com:3306';
//        $user = 'root';
//        $pwd = '72b74b5d09';
//        $db_name = 'meetelf';



        $conn = mysql_connect($host, $user, $pwd);
        if (empty($conn)) {
            echo 'Connect to mysql server error';
            return null;
        }

        mysql_select_db($db_name, $conn);
        mysql_query('SET NAMES utf8', $conn);
        return $conn;


        //$host = $config['port'] ? $config['host'] . ':' . $config['port'] : $config['host'];

//        var_dump($host);
//        echo "\r\n";
//        var_dump($config);
//        echo "\r\n";

//        if($_db = mysql_connect($host, $config['username'], $config['password'])){
//            echo date('Y-m-d H:i:s') . 'Fatal Error : Could not connect to mysql server.';
//        }
//        mysql_select_db($config['dbname'], $_db);
//        mysql_query("SET NAMES utf8", $_db);
//        return $_db;

        //改为长连接
        //if($_db = mysql_connect($host, $config['username'], $config['password'])){

        //  echo date('Y-m-d H:i:s') . 'Fatal Error : Could not connect to mysql server.';
        //}
        //mysql_select_db($config['dbname'], $_db);
        // mysql_query("SET NAMES utf8", $_db);

        // return $_db;
    }
}

/**
 * submail 单条短信发送
 *
 * @param string $mobile 用户
 * @param string $project 短信模板
 * @param array $vars 参数
 **/

if (!function_exists('submail_xsend')) {
    function submail_xsend($mobile, $vars, $project = '')
    {
        Loader::load('lib.submail.lib.messagexsend');
        $config = Loader::load_config('submail');
        $project = $project ? $project : $config['template_id'];
        $sender = new MESSAGEXsend($config);
        $sender->setTo($mobile);
        $sender->SetProject($project);
        foreach ($vars as $key => $value) {
            $sender->AddVar($key, $value);
        }
        return $sender->xsend();
    }
}

/**
 * submail 多条短信发送
 *
 *
 * @param string $contacts
 * @param string $project 短信模板
 **/

if (!function_exists('submail_multixsend')) {
    function submail_multixsend($data, $project = '')
    {
        $contacts = array();
        //拼装数据
        /***************测试********************/
        /* foreach($data as $key => $value){
             $contacts[$value['mobile']] = array(
                 'to' => $value['mobile'],
                 'vars'   => array(
                     'events' => $value['title'],
                     'url'    => $value['sms_url'],
                 ),
                 'id'            => $value['id'],
                 'activity_guid' => $value['activity_guid'],
                 'ticket_guid'   => $value['ticket_guid'],
             );
         }

         //修改为要发送的格式
         $postData = array(
             'appid'=>'test_multi_send',
             'project'=>'default_tempale',
             'signature'=>'b817cd117b99c08918b6423029d761de',
             'multi'=> json_encode(array_values($contacts)),
         );
         //使用CURL发送数据到测试服务器 并返回JSON
         Loader::load("lib.Curl");
         $curl=new Curl();
         $url = "http://www0.meetelf.com:8080/message/multixsend.json";
         $res = $curl->postRequest($url,$postData);
         return $res[$url];
     */
        /***************测试********************/
        foreach ($data as $key => $value) {
            $contacts[$value['mobile']] = array(
                'to' => $value['mobile'],
                'vars' => array(
                    'events' => $value['title'],
                    'url' => $value['sms_url'],
                ),
                'id' => $value['id'],
                'activity_guid' => $value['activity_guid'],
                'ticket_guid' => $value['ticket_guid'],
            );
        }
        Loader::load('lib.submail.lib.messagemultixsend');
        Loader::load('lib.submail.lib.multi');
        $config = Loader::load_config('submail');
        $sender = new MESSAGEMultiXsend($config);
        $project = $project ? $project : $config['template_id'];
        $sends = array();
        foreach ($contacts as $key => $value) {
            $multi = new Multi();
            $multi->setTo($value['to']);
            foreach ($value['vars'] as $k => $v) {
                $multi->addVar($k, $v);
            }
            $sender->addMulti($multi->build());
        }
        $sender->SetProject($project);
        $res = $sender->multixsend();
        return json_decode($res, true);
    }
}

if (!function_exists('yunpian_multixsend')) {
    function yunpian_multixsend($data, $project = '')
    {
        //加载配置
        $config = Loader::load_config('yunpian');
        Loader::load("lib.Curl");


        //获取模板
        $template = empty($project) ? $config["tpl_id"] : $project;
        //获取apikey
        $apikey = trim($config['apikey']);


        $curl = new Curl();

        //依据模板id和apikey 获取模板

        $response = $curl->postRequest(
            $config['get_template_url'],
            array(
                'apikey' => $apikey,
                'tpl_id' => trim($template)
            )
        );
        $response = $response[$config['get_template_url']];
        if (!is_array($response)) {
            $response = json_decode($response, true);
        }
        if ($response["code"] != 0) {

            echo 'Get Template Error:' . json_encode($response) . "\r\n";


            $res = array();
            //模板获取失败 将当前短信全部设置为发送error
            foreach ($data as $v) {
                $res[] = array(
                    'to' => $v['mobile'],
                    'status' => 'error'
                );
            }
            return $res;
        }
        //获取模板的有效内容
        $tempalteStr = $response["template"]["tpl_content"];


        $mobiles = array(); //电话号码 ,连接
        $messages = array();//消息内容

        //遍历数据 创建
        foreach ($data as $v) {
            $mobiles[] = trim($v['mobile']);

            $repl = str_replace('#events#', $v['title'], $tempalteStr);
            $repl = str_replace('#url#', $v['sms_url'], $repl);
            $messages[] = urlencode(trim($repl));
        }
        //获取要发送的文本
        $mobileStr = implode(',', $mobiles);
        $messageStr = implode(',', $messages);

        $response = $curl->postRequest(
            $config['send_mutli_url'],
            array(
                'apikey' => $apikey,
                'mobile' => $mobileStr,
                'text' => $messageStr
            )
        );

        $response = $response[$config['send_mutli_url']];
        if (!is_array($response)) {
            $response = json_decode($response, true);
        }

        //结果集
        $res = array();
        $i = 0;
        foreach ($data as $k => $v) {
            $item = array();
            $response_data = $response[$i];
            if ($response_data['code'] == 0) {
                $item['status'] = 'success';
            } else {
                $item['status'] = 'error';
            }
            $item['to'] = $k;
            $res[] = $item;
            $i++;
        }
        return $res;
    }

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

    /*
    Loader::load("lib.Curl");
    $curl=new Curl();
    $contacts = array(
        'appid' => '121322131313',  
        'to'    => $to_emails,
        'project' => '1111',
        'vars' => '1111',
        'signature' => '1111',
    );
    $url = "http://www0.meetelf.com:8080/mail/xsend.json"; 
    $res = $curl->postRequest($url,$contacts);
    return $res[$url];
    */
    Loader::load('lib.submail.lib.mailsend');
    $config = Loader::load_config('submail_mail');
    $submail = new MAILSend($config);
    if (!is_array($to_emails)) {
        $submail->AddTo($to_emails);
    } else {
        foreach ($to_emails as $email) {
            $submail->AddTo($email);
        }
    }
    $submail->SetSender($config['mail'], $from_name);
    $submail->SetSubject($subject);
    $submail->SetText($content);
    $submail->SetHtml($content);
    return $submail->send();
}

/**
 * 创建和array_column函数相似的功能,由于低版本php不支持array_column
 *
 * @param  array $array
 * @param  string $column_key
 * @param  string $index_key
 * @return array
 **/

function array_columns($array, $column_key = null, $index_key = null)
{
    return array_reduce($array, function ($result, $item) use ($column_key, $index_key) {
        if ($column_key && $index_key) {
            $result[$item[$index_key]] = $item[$column_key];
        } elseif ($column_key === null && $index_key) {
            $result[$item[$index_key]] = $item;
        } elseif (null === $index_key && $column_key) {
            $result[] = $item[$column_key];
        }
        return $result;
    }, array());
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
function httpRequest($url, $post = '', $method = 'GET', $limit = 0, $returnHeader = FALSE, $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 60, $block = TRUE)
{
    $return = '';
    $matches = parse_url($url);

    !isset($matches['host']) && $matches['host'] = '';
    !isset($matches['path']) && $matches['path'] = '';
    !isset($matches['query']) && $matches['query'] = '';
    !isset($matches['port']) && $matches['port'] = '';

    $host = $matches['host'];
    $path = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
    $port = !empty($matches['port']) ? $matches['port'] : 80;

    if (strtolower($method) == 'post') {
        $post = (is_array($post) and !empty($post)) ? http_build_query($post) : $post;
        $out = "POST $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        //$out .= "Referer: $boardurl\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= 'Content-Length: ' . strlen($post) . "\r\n";
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

    if (!$fp) return ''; else {
        $header = $content = '';

        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        fwrite($fp, $out);
        $status = stream_get_meta_data($fp);

        if (!$status['timed_out']) {//未超时
            while (!feof($fp)) {
                $header .= $h = fgets($fp);
                if ($h && ($h == "\r\n" || $h == "\n")) break;
            }

            $stop = false;
            while (!feof($fp) && !$stop) {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $content .= $data;
                if ($limit) {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }
        fclose($fp);

        return $returnHeader ? array($header, $content) : $content;
    }
}
