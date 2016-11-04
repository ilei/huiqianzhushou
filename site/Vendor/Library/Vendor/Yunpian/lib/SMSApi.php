<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/1
 * Time: 下午12:29
 */

require_once "ClientBase.php";

class SMSApi extends ClientBase{

    public  function  __construct($apikey)
    {
        $this->apikey=$apikey;
    }


    //单发
    public  function singleSend($mobile,$text){
        $url=$this->baseUrl."sms/send.json";

        $params=array(
            'apikey'=>$this->apikey,
            'mobile'=>$mobile,
            'text'=>$text
        );

        $res=$this->request($url,$params);

        var_dump($res);

        if($res!=0){
            return array(
                'mobile'=>$mobile,
                'status'=>'error'
            );
        }
        else{
            return array(
                'mobile'=>$mobile,
                'status'=>'success'
            );
        }
    }

}
