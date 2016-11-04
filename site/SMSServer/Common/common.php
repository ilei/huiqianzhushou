<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 下午4:11
 *
 * 公有函数
 */


/**
 * 循环检测目录是否存在，如不存在则创建
 *
 * @param  string $path 目录
 * @param  int $mode 目录权限
 * @return boolean
 **/
function check_path($path, $mode = 0755)
{
    if (!is_dir($path)) {
        check_path(dirname($path), $mode);
    }
    return @mkdir($path, $mode);
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

            if(isset($item[$index_key])){
                $result[$item[$index_key]] = $item;
            }
        } elseif (null === $index_key && $column_key) {
            if(isset($item[$column_key])) {

                $result[] = $item[$column_key];
            }
        }
        return $result;
    }, array());
}




/**
 * -------------------------------------------
 *  实在懒得改 老王这堆屌炸天的逻辑 直接扔这里算了
 * -------------------------------------------
 */


/**
 * submail 多条短信发送
 *
 *
 * @param string $contacts
 * @param string $project 短信模板
 **/

    function submail_multixsend($config, $data)
    {
        $contacts = array();
        //拼装数据
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
        $sender = new MESSAGEMultiXsend($config);
        $project = $config['template_id'];


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


    function yunpian_multixsend($config,$data)
    {

        //获取模板
        $template = $config["tpl_id"] ;
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



/**
 * 发送电子邮箱
 * @param $to_emails 邮件地址数组
 * @param $from_name 发送人姓名
 * @param $subject 邮件标题
 * @param $content 邮件内容
 * @return mixed
 * CT: 2015-03-24 10:34 by ylx
 */
function send_email($config,$to_emails, $from_name, $subject, $content)
{

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