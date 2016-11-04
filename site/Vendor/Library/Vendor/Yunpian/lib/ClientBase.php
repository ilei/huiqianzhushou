<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/1
 * Time: 下午12:14
 */


abstract class ClientBase{
    //url基地址
    protected  $baseUrl="http://yunpian.com/v1/";
    //短信Key
    protected $apikey="";

    //创建请求的Url
    protected function request($url,$post_data){

        if(is_array($post_data)){
            $post_data=http_build_query($post_data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);

        $output = trim($output, "\xEF\xBB\xBF");
        return json_decode($output,true);
    }


}
