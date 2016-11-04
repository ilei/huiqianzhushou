<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/1
 * Time: 上午10:11
 */

vendor('YmPush.Sender.Sender');
vendor('YmPush.Content.YunPianContent');
vendor('Yunpian.main');

class YunPianSender extends Sender{


    public  function __construct()
    {
        parent::__construct(new \YunPianContent());
    }

    public  function send($type, $to_user, $msg_data, $args = array())
    {


        list($project, $vars) = $this->content->getContent($type, $msg_data, $args);



        //从配置中读取APIKey
         $apikey=C('YUNPIAN_CONFIG.apikey');

         return YunPian::singelSend($apikey,$to_user,$project,$vars);


        // TODO: Implement send() method.
    }

}
