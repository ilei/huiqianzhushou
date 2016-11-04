<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/1
 * Time: 下午6:26
 */

namespace Cli\Controller;
use Think\Controller;

vendor('YMPush.Sender.YunPianSender');

class TestController extends Controller{

    public  function  test()
    {
        var_dump( C('CODE_TYPE.api_verify_mobile'));

       $sender= new \YunPianSender();
        $sender->send(C('CODE_TYPE.api_verify_mobile'),'15222270365',array(
            '118888',
            '30'
        ));
    }

}