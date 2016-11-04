<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/1
 * Time: 下午12:19
 */

require_once "ClientBase.php";


//模板操作API
class TemplateApi extends  ClientBase{

    public  function  __construct($apikey)
    {
        $this->apikey=$apikey;
    }

    //根据模板ID获取模板内容
    public function getTemplateByTmplID($tpl_id){
        $url=$this->baseUrl."tpl/get.json";

        $params=array(
            'apikey'=>$this->apikey,
            'tpl_id'=>$tpl_id
        );

        //判断结果
        $res = $this->request($url,$params);


        var_dump($res);

        if($res['code']!=0){

            //无效的调用
            return false;
        }else{
            //模板内容
            return $res['template']['tpl_content'];
        }
    }
}
