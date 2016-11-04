<?php
header("Content-Type: text/html; charset=utf-8");

require_once(dirname(__FILE__) . '/' . 'IGt.Push.php');
require_once(dirname(__FILE__) . '/' . 'igetui/IGt.AppMessage.php');
require_once(dirname(__FILE__) . '/' . 'igetui/template/IGt.BaseTemplate.php');

class Push{

    protected $appkey = '';
    protected $appid = '';
    protected $master_secret = '';
    protected $device_token = '';
    protected $host = '';

    public function __construct() {
        $config_str = (APP_DEBUG==true) ? 'IGETUI_CONFIG_DEBUG' : 'IGETUI_CONFIG';  // 若APP_DEBUG开启, 则调用beta版个推
        $config = C($config_str);
        $this->appkey = $config['appkey'];
        $this->appid = $config['appid'];
        $this->master_secret = $config['master_secret'];
        $this->device_token = $config['device_token'];
        $this->host = $config['host'];
    }

    /**
     * ClientID与别名绑定
     * 一个ClientID只能绑定一个别名，若已绑定过别名的ClientID再次绑定新别名，则认为与前一个别名自动解绑，绑定新别名。
     * @param $cid
     * @param $alias
     * @throws Exception
     */
    public function aliasBind($alias, $cid) {
        $igt = new IGeTui($this->host, $this->appkey, $this->master_secret);
        $rep = $igt->bindAlias($this->appid, $alias, $cid);
        var_dump($rep);die();
    }

    /**
     * 多个ClientID，使用同一别名绑定
     * 允许将多个ClientID和一个别名绑定，如用户使用多终端，则可将多终端对应的ClientID绑定为一个别名，目前一个别名最多支持绑定10个ClientID。
     * @param $alias
     * @param array $cids 最多10个
     * @throws Exception
     */
    public function aliasBatch($alias, $cids) {
        $igt = new IGeTui($this->host, $this->appkey, $this->master_secret);

        $targetList = array();
        foreach($cids as $c) {
            $target = new IGtTarget();
            $target->set_clientId($c);
            $target->set_alias($alias);
            $targetList[] = $target;
        }

        $rep = $igt->bindAliasBatch($this->appid, $targetList);
        var_dump($rep);die();
    }

    /**
     * 根据别名获取ClientID信息
     * @param $alias
     * @throws Exception
     */
    public function queryCID($alias){
        $igt = new IGeTui($this->host, $this->appkey, $this->master_secret);
        $rep = $igt->queryClientId($this->appid, $alias);
        if($rep['result'] == 'ok') {
            return $rep['cidlist'];
        } else {
            return false;
        }
    }

    /**
     * 根据ClientId查询别名
     * @param $cid
     * @throws Exception
     */
    public function queryAlias($cid){
        $igt = new IGeTui($this->host, $this->appkey, $this->master_secret);
        $rep = $igt->queryAlias($this->appid, $cid);
        var_dump($rep);die();
    }

    /**
     * 单个ClientID和别名解绑
     * @param $alias
     * @param $cid
     * @throws Exception
     */
    public function aliasUnBind($alias, $cid){
        $igt = new IGeTui($this->host, $this->appkey, $this->master_secret);
        $rep = $igt->unBindAlias($this->appid, $alias, $cid);
        var_dump($rep);
        echo("<br><br>");
    }

    /**
     * 绑定别名的所有ClientID解绑
     * @param $alias
     */
    public function aliasUnBindAll($alias){
        $igt = new IGeTui($this->host, $this->appkey, $this->master_secret);
        $rep = $igt->unBindAliasAll($this->appid, $alias);
        return $rep;
    }


//getUserTags($this->appid,CID);
//pushMessageToSingle();
//pushMessageToList();
//pushMessageToApp();
//pushAPN();
//setdurationTest();
//mktime(hour, minute, second, month, day, year)


    public function getUserTags($cid) {
        $igt = new IGeTui($this->host,$this->appkey,$this->master_secret);
        $rep = $igt->getUserTags($this->appid, $cid);
        //$rep.connect();
        var_dump($rep);
        echo ("<br><br>");
    }




    public function getPushResult($task_id) {
        $igt = new IGeTui($this->host,$this->appkey,$this->master_secret);
        $rep = $igt->getPushResult($task_id);
        return $rep;
    }



    public function setspeedtest()
    {  $igt=new IGtAppMessage();
        $igt->set_speed(10);
        var_dump($igt->get_speed());

    }


    public function setdurationTest()
    {

        $begin = "2015-01-31 00:11:22";
        $end = "2015-02-28 15:31:24";
         date_default_timezone_set('asia/shanghai');
        //1422634282000
        $hour =substr($begin,11,2);
        $minute =substr($begin,14,2);
        $second=substr($begin,17,2);
        $month=substr($begin,5,2);
        $day=substr($begin,8,2);
        $year=substr($begin,0,4);
    //var_dump($hour,$minute,$second,$month,$day,$year);
        $ss=mktime($hour,$minute,$second,$month,$day,$year)*1000;
        $hour =substr($end,11,2);
        $minute =substr($end,14,2);
        $second=substr($end,17,2);
        $month=substr($end,5,2);
        $day=substr($end,8,2);
        $year=substr($end,0,4);
        $e = mktime($hour,$minute,$second,$month,$day,$year)*1000;;
        if ($ss<=0 || $e<=0)
            throw new Exception("DateFormat: yyyy-MM-dd HH:mm:ss");
        if ($ss>$e)
            throw new Exception("startTime should be smaller than endTime");

        var_dump($ss."-".$e);
        var_dump(mktime(15, 26, 22, 2, 28, 2015));

        echo ("<br><br>");

    }

    public function getUserStatus() {
        $igt = new IGeTui($this->host,$this->appkey,$this->master_secret);
        $rep = $igt->getClientIdStatus($this->appid,CID);
        var_dump($rep);
        echo ("<br><br>");
    }

    public function pushAPN(){
        $igt = new IGeTui($this->host,$this->appkey,$this->master_secret);
        $template = new IGtAPNTemplate();
        $template->set_pushInfo("a", 4, "b", "com.gexin.ios.silence", "DDDD", "近日。", "", "");
        $message = new IGtSingleMessage();

        $message->set_data($template);
        $ret = $igt->pushAPNMessageToSingle($this->appid, $this->device_token, $message);
        var_dump($ret);
    }

    //
    //服务端推送接口，支持三个接口推送
    //1.PushMessageToSingle接口：支持对单个用户进行推送
    //2.PushMessageToList接口：支持对多个用户进行推送，建议为50个用户
    //3.pushMessageToApp接口：对单个应用下的所有用户进行推送，可根据省份，标签，机型过滤推送
    //消息模版：
    // 1.TransmissionTemplate:透传功能模板
    // 2.LinkTemplate:通知打开链接功能模板
    // 3.NotificationTemplate：通知透传功能模板
    // 4.NotyPopLoadTemplate：通知弹框下载功能模板

    /**
     * 单推接口案例
     * @param int $msg_type 消息模版：
     *           1.TransmissionTemplate:透传功能模板
     *           2.LinkTemplate:通知打开链接功能模板
     *           3.NotificationTemplate：通知透传功能模板
     *           4.NotyPopLoadTemplate：通知弹框下载功能模板
     * @param array $msg_data 消息内容
     * @param string $alias 别名
     */
    public function pushMessageToSingle($alias, $msg_data, $msg_type = 1) {
        $igt = new IGeTui($this->host, $this->appkey, $this->master_secret);

        // 判断消息类型
        switch($msg_type) {
            case 1:
                $template = $this->IGtTransmissionTemplateDemo($msg_data);
                break;
            case 2:
                $template = $this->IGtLinkTemplateDemo();
                break;
            case 3:
                $template = $this->IGtNotificationTemplateDemo();
                break;
            case 4:
                $template = $this->IGtNotificationTemplateDemo();
                break;
        }

        //个推信息体
        $message = new IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送

        //接收方
        $target = new IGtTarget();
        $target->set_appId($this->appid);
//        $target->set_clientId('edaa93721c64f16b201a82bbfdebfaf6');
        $target->set_alias($alias);

        $rep = $igt->pushMessageToSingle($message,$target);
		return $rep;
    }

    /**
     * 对指定列表用户推送消息
     * @param array $aliases 别名列表
     * @param int $msg_type 消息模版：
     *           1.TransmissionTemplate:透传功能模板
     *           2.LinkTemplate:通知打开链接功能模板
     *           3.NotificationTemplate：通知透传功能模板
     *           4.NotyPopLoadTemplate：通知弹框下载功能模板
     * @param array $msg_data 消息内容
     */
    public function pushMessageToList($aliases, $msg_data, $msg_type = 1){
        try {
            $taskGroupName = null;
            // $taskGroupName = null;
            $igt = new IGeTui($this->host,$this->appkey,$this->master_secret);
            //putenv("needDetails=true");

            // 判断消息类型
            switch($msg_type) {
                case 1:
                    $template = $this->IGtTransmissionTemplateDemo($msg_data);
                    break;
                case 2:
                    $template = $this->IGtLinkTemplateDemo();
                    break;
                case 3:
                    $template = $this->IGtNotificationTemplateDemo();
                    break;
                case 4:
                    $template = $this->IGtNotificationTemplateDemo();
                    break;
            }
            //个推信息体
            $message = new IGtListMessage();
            $message->set_isOffline(true);//是否离线
            $message->set_offlineExpireTime(3600*12*1000);//离线时间
            $message->set_data($template);//设置推送消息类型
            if (is_null($taskGroupName)){
                $contentId = $igt->getContentId($message);
            } else {
                $contentId = $igt->getContentId($message, $taskGroupName);
            }

            //接收方1
            $targetList = array();
            foreach($aliases as $a) {
                $target = new IGtTarget();
                $target->set_appId($this->appid);
				//$target->set_clientId(CID);
                $target->set_alias($a);
                $targetList[] = $target;
            }

            $igt->needDetails = true;
            $rep = $igt->pushMessageToList($contentId, $targetList);
			return $rep;
        } catch (Exception $e) {
            echo ($e);
        }
        echo ("<br><br>");

    }

    /**
     * 对指定应用群推接口
     * @param int $msg_type 消息模版：
     *           1.TransmissionTemplate:透传功能模板
     *           2.LinkTemplate:通知打开链接功能模板
     *           3.NotificationTemplate：通知透传功能模板
     *           4.NotyPopLoadTemplate：通知弹框下载功能模板
     * @param array $msg_data 消息内容
     * @return bool
     */
    public function pushMessageToApp($msg_data, $msg_type = 1)
    {
        $taskGroupName = null;

        $igt = new IGeTui($this->host, $this->appkey, $this->master_secret);

        // 判断消息类型
        switch($msg_type) {
            case 1:
                $template = $this->IGtTransmissionTemplateDemo($msg_data);
                break;
            case 2:
                $template = $this->IGtLinkTemplateDemo();
                break;
            case 3:
                $template = $this->IGtNotificationTemplateDemo();
                break;
            case 4:
                $template = $this->IGtNotificationTemplateDemo();
                break;
        }

        //个推信息体
        //基于应用消息体
        $message = new IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(3600*12*1000);
        $message->set_data($template);
        $message->set_speed(100);

        $message->set_appIdList(array($this->appid));
//        $message->set_phoneTypeList(array('ANDROID'));
        //$message->set_provinceList(array('浙江','北京','河南'));
        //$message->set_tagList(array('tag1','tag2'));

        if (is_null($taskGroupName)){
            $rep = $igt->pushMessageToApp($message);
        } else {
            $rep = $igt->pushMessageToApp($message, $taskGroupName);
        }
        if($rep['result'] == 'ok') {
            return $rep;
        } else {
            return false;
        }
    }

//  $hosts=array("http://code.google.com/p/pb4php/downloads/list",
//              "http://sdk.open.api.getui.net/serviceex",
//               "http://code.google.com/p/pb4php/downloads/list");

    public function getfasthost_nt($hosts)
    {   
		if (count($hosts)==1)
            $host= $hosts[0];
		else{
            $mint=60.0;
            $s_url="";
            for ($i=0;$i<count($hosts);$i++)
            {
                $start = array_sum(explode(" ",microtime()));

                $opts = array(
                   'http'=>array(
                      'method'=>"GET",
                   'timeout'=>10,
                  )  );
                $context = stream_context_create($opts);
                try {
                $homepage = file_get_contents($hosts[$i], false, $context);
                    } catch (Exception $e) {
                throw new Exception("host:[" . $host);
                     }
                $ends = array_sum(explode(" ",microtime()));

                if ($homepage==NULL ||$homepage="")
                  $diff=60.0;
                else
                  $diff=$ends-$start;
                var_dump($diff);
                if ($mint > $diff)
                {$mint=$diff;
                    $s_url=$hosts[$i];}
             }

                $host =$s_url;
                var_dump( $host);
            }
            
    }


    // ---------------------------------------------------------
    //所有推送接口均支持四个消息模板，依次为通知弹框下载模板，通知链接模板，通知透传模板，透传模板
    //注：IOS离线推送需通过APN进行转发，需填写pushInfo字段，目前仅不支持通知弹框下载功能


    public function IGtTransmissionTemplateDemo($data){
        $template =  new IGtTransmissionTemplate();
        $template->set_appId($this->appid);//应用appid
        $template->set_appkey($this->appkey);//应用appkey
        $template->set_transmissionType(2);//透传消息类型
        $template->set_transmissionContent($data);//透传内容
        //iOS推送需要设置的pushInfo字段
//        $template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        $template ->set_pushInfo("", 1, "您收到一条新消息，请点击查看。", "", "", "", "", "");
        return $template;
    }
    public function IGtNotyPopLoadTemplateDemo(){
        $template =  new IGtNotyPopLoadTemplate();

        $template ->set_appId($this->appid);//应用appid
        $template ->set_appkey($this->appkey);//应用appkey
        //通知栏
        $template ->set_notyTitle("个推");//通知栏标题
        $template ->set_notyContent("个推最新版点击下载");//通知栏内容
        $template ->set_notyIcon("");//通知栏logo
        $template ->set_isBelled(true);//是否响铃
        $template ->set_isVibrationed(true);//是否震动
        $template ->set_isCleared(true);//通知栏是否可清除
        //弹框
        $template ->set_popTitle("弹框标题");//弹框标题
        $template ->set_popContent("弹框内容");//弹框内容
        $template ->set_popImage("");//弹框图片
        $template ->set_popButton1("下载");//左键
        $template ->set_popButton2("取消");//右键
        //下载
        $template ->set_loadIcon("");//弹框图片
        $template ->set_loadTitle("地震速报下载");
        $template ->set_loadUrl("http://dizhensubao.igexin.com/dl/com.ceic.apk");
        $template ->set_isAutoInstall(false);
        $template ->set_isActived(true);

        return $template;
    }

    public function IGtLinkTemplateDemo(){
        $template =  new IGtLinkTemplate();
        $template ->set_appId($this->appid);//应用appid
        $template ->set_appkey($this->appkey);//应用appkey
        $template ->set_title("请输入通知标题");//通知栏标题
        $template ->set_text("请输入通知内容");//通知栏内容
        $template ->set_logo("");//通知栏logo
        $template ->set_isRing(true);//是否响铃
        $template ->set_isVibrate(true);//是否震动
        $template ->set_isClearable(true);//通知栏是否可清除
        $template ->set_url("http://www.igetui.com/");//打开连接地址
        // iOS推送需要设置的pushInfo字段
        //$template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        $template ->set_pushInfo("", 1, "您收到一条新消息，请点击查看。", "", "", "", "", "");
        return $template;
    }

    public function IGtNotificationTemplateDemo(){
        $template =  new IGtNotificationTemplate();
        $template->set_appId($this->appid);//应用appid
        $template->set_appkey($this->appkey);//应用appkey
        $template->set_transmissionType(2);//透传消息类型
        $template->set_transmissionContent("测试离线");//透传内容
        $template->set_title("个推");//通知栏标题
        $template->set_text("个推最新版点击下载");//通知栏内容
        $template->set_logo("http://wwww.igetui.com/logo.png");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        // iOS推送需要设置的pushInfo字段
        //$template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        $template ->set_pushInfo("", 1, "您收到一条新消息，请点击查看。", "", "", "", "", "");
        return $template;
    }


}
