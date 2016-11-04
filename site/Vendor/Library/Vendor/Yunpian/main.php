<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/1
 * Time: 下午5:06
 */


//主入口点

require_once("lib/SMSApi.php");
require_once("lib/TemplateApi.php");

class YunPian{

    /**
     * 单发 $apikey string api的唯一标示
     *      $mobile string 目标手机
     *      $tpl_id string 模板ID
     *      $params array 模板参数
     */
    public static  function singelSend($apikey,$mobile,$tpl_id,$params){

        $tplApi=new TemplateApi($apikey);

        //模板内容
        $text=$tplApi->getTemplateByTmplID($tpl_id);


        //模板获取失败
        if(!$text){

            var_dump("云片模板获取失败");
            return array(
                'mobile'=>$mobile,
                'status'=>'error'
            );
        }

        //替换模板内容

        foreach($params as $k=>$v){
            //替换占位符
            $text=str_replace('#'.$k.'#',$v,$text);
        }


        $smsApi=new SMSApi($apikey);

        //发送短信
        return $smsApi->singleSend(trim($mobile),trim($text));
    }

}
