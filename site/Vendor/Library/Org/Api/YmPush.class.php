<?php
namespace Org\Api;

use BaiduPush\Channel;

/**
 * 消息推送类
 * 
 * CT: 2014-09-19 12:00 by YLX
 */
class YmPush 
{
    protected $apiKey;
    protected $secretKey;
    protected $channel;
    
    /**
     * 获取apiKey
     * 
     * CT: 2014-09-19 12:00 by YLX
     */
    public function getApiKey()
    {
        $key = C('PUSH_API_KEY');
        
        if (!check_string_len($key, 1, 64)){
            die("invalid param - apiKey[$key], which must be a 1 - 64 length string");
        }
        
        return $key; 
    }

    /**
     * 获取secretKey
     *
     * CT: 2014-09-19 12:00 by YLX
     */
    public function getSecretKey()
    {
        $key = C('PUSH_SECRET_KEY');
        if (!check_string_len($key, 1, 64)){
            die("invalid param - secretKey[$key], which must be a 1 - 64 length string");
        }
        return $key;
    }

    /**
     * 构造方法, 初始化apiKey和secretKey
     *
     * CT: 2014-09-19 12:00 by YLX
     */
    public function __construct()
    {
        $this->apiKey = $this->getApiKey();
        $this->secretKey = $this->getSecretKey();
        
        $this->channel = new Channel($this->apiKey, $this->secretKey);
    }

    /**
     * 查询用户的绑定列表
     *
     * CT: 2014-09-25 10:00 by YLX
     */
    public function queryBindList ( $user_id ) 
    {
    	$channel = $this->channel;
    	$res = $channel->queryBindList ( $user_id ) ;
    	return $res;
    }
    
    /**
     * 推送android设备消息
     * 
     * $setting = array ($push_type = 1/2/3, $user_id=null, $tag_name=null, $msg_type=0/1)
     * 若push_type为1, user_id不能为空, 推送消息到某个user
     * 若push_type为2, tag_name不能为空, 推送消息到一个tag中的全部user
     * 若push_type为3, user_id和tag_name都可为空, 推送消息到该app中的全部user
     * 
     * msg_type, 0为消息, 消息内容为字符串; 1为通知, 消息内容为数组, 详见官方文档
     * 
     * CT: 2014-09-19 12:00 by YLX
     * UT: 2014-09-25 10:50 by YLX
     */
    public function pushMessage_android ($message, $message_key, $setting)
    {
        $channel = $this->channel;
        
        //推送消息类型, 默认为单播消息
    	$push_type = !empty($setting['push_type'])?$setting['push_type']:1; 

    	//如果推送单播消息，需要指定user
        if ($push_type == 1){
           if (empty($setting['user_id'])) return false;
    	   $optional[Channel::USER_ID] = $setting['user_id']; 
        }
        //如果推送tag消息，需要指定tag_name
        if ($push_type == 2){
           if (empty($setting['tag_name'])) return false;
    	   $optional[Channel::TAG_NAME] = $setting['tag_name'];  
        }
    
    	//指定发到android设备
//     	$optional[Channel::DEVICE_TYPE] = 3;
    	//指定消息类型为 消息;  0为消息, 1为通知
    	$optional[Channel::MESSAGE_TYPE] = !empty($setting['msg_type'])?$setting['msg_type']:0; //默认为消息
    	
        $res = $channel->pushMessage ( $push_type, $message, $message_key, $optional ) ;
        
        return $res;
    }

    /**
     * 根据 user_id 查询离线消息的个数
     * 
     * CT: 2014-09-25 11:31 by YLX
     */
    public function fetchMessageCount ($user_id)
    {
        $channel = $this->channel;
        $res = $channel->fetchMessageCount ( $user_id) ;
        return $res;
    }

    /**
     * 根据 user_id 查询离线消息
     * 
     * CT: 2014-09-25 11:31 by YLX
     */
    public function fetchMessage ( $user_id  )
    {
        $channel = $this->channel;
        $res = $channel->fetchMessage ( $user_id ) ;
        return $res;
    }

    /**
     * 根据 user_id, msg_ids 删除离线消息
     * 要删除消息 ID 列表，多个参数则用数组格式，或是由数组转换成的 json 字符串。
     * 
     * CT: 2014-09-25 11:31 by YLX
     */
    public function deleteMessage ( $user_id, $msg_ids )
    {
        $channel = $this->channel;
        $res = $channel->deleteMessage ( $user_id, $msg_ids ) ;
        return $res;
    }

    /**
     * 创建消息广播组
     * 
     * 当 user_id 被提交时，服务端将会完成用户和 tag 的绑定操作。
     * 
     * CT: 2014-09-25 11:11 by YLX
     */
    public function setTag($tag_name, $user_id = null)
    {
        $channel = $this->channel;
        
        $optional = array();
        if (!empty($user_id)){
            $optional[Channel::USER_ID] = $user_id;
        }
        $res = $channel->setTag($tag_name, $optional);
        
        return $res;
    }

    /**
     * 查询消息标签信息
     *
     * 若未指定tag_name, 则返回所有tag的信息
     *
     * CT: 2014-09-25 11:11 by YLX
     */
    public function fetchTag($tag_name = null)
    {
        $channel = $this->channel;
    	$optional[Channel::TAG_NAME] = $tag_name;
        $res = $channel->fetchTag($optional);
        
        return $res;
    
    }

    /**
     * 删除消息组
     *
     * 当 USER_ID 提交时，服务器会解除该用户与 tag 的绑定关系。
     *
     * CT: 2014-09-25 11:11 by YLX
     */
    public function deleteTag($tag_name, $user_id = null)
    {
        $channel = $this->channel;
        
        $optional = array();
        if (!empty($user_id)){
            $optional[Channel::USER_ID] = $user_id;
        }
        
        $res = $channel->deleteTag($tag_name, $optional);
        return $res;
    
    }

    /**
     * 查询属于用户的组列表
     *
     * CT: 2014-09-25 11:21 by YLX
     */
    function queryUserTags($user_id)
    {
        $channel = $this->channel;
        $res = $channel->queryUserTags($user_id);
        return $res;
    
    }
    
}
