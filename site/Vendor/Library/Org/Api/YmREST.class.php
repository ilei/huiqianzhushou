<?php
/**
 * Created by PhpStorm.
 * User: T430
 * Date: 2014/11/6
 * Time: 12:10
 */

namespace Org\Api;
use Org\Api\YmCurl;

class YmREST {

//    const API_URL = 'http://api.yunmai365.com';
    const API_URL = 'http://192.168.0.155/api';

    public function __constract()
    {
    }

    /**
     * 聊天API 用户注册 || 批量注册
     *
     * @param $data array 两种格式:
     *  1单个注册. array('username'=>'', 'password'=>'');
     *  2批量注册. array(array('username'=>'', 'password'=>''), array('username'=>'', 'password'=>''), ...);
     *
     * @return array
     *
     * CT: 2014-11-06 14:30 by YLX
     */
    public function chatRegister($data)
    {
        $curl = new YmCurl();
        return $curl->post(self::API_URL.'/chat/register', $data);
    }

    /**
     * 发送消息
     *
     * @param string $from_user
     *        	发送方用户名
     * @param array $to_user
     *        	array('1','2')
     * @param array $type
     *        	txt, img, audio, cmd ...
     * @param string $content
     * @param string $target_type
     *        	默认为：users 描述：给一个或者多个用户(users)或者群组发送消息(chatgroups)
     * @param array $ext
     *        	自定义参数
     *
     * @return string
     *
     * CT: 2014-11-06 16:30 by YLX
     */
    public function sendMsg($from_user, $to_user, $type, $content, $target_type = "users", $ext = array())
    {
        $data = array(
            'from_user' => $from_user,
            'to_user' => $to_user,
            'type'    => $type,
            'content' => $content,
//            'content' => array('type'=>$type, 'msg'=>$content),
            'target_type' => $target_type,
            'ext' => $ext
        );

        $curl = new YmCurl();
        return $curl->post(self::API_URL.'/chat/msg', $data);
    }

    /**
     * 创建群组
     *
     * @param $option['groupname'] //群组名称,
     *        	此属性为必须的
     * @param $option['desc'] //群组描述,
     *        	此属性为必须的
     * @param $option['public'] //是否是公开群,
     *        	此属性为必须的 true or false
     * @param $option['approval'] //加入公开群是否需要批准,
     *        	没有这个属性的话默认是true, 此属性为可选的
     * @param $option['owner'] //群组的管理员,
     *        	此属性为必须的
     * @param $option['members'] //群组成员,此属性为可选的
     *
     * @return string
     */
    public function register_group($options)
    {
        $curl = new YmCurl();
        return $curl->post(self::API_URL.'/chat/register_group', $options);
    }

    /**
     * 删除群组
     *
     * @param
     *        	$group_id
     */
    public function delete_group($group_id)
    {
        $curl = new YmCurl();
//        return $curl->get(u_abs('Api/Chat/delete_group', array('group_id'=>$group_id)));
        return $curl->get(self::API_URL.'/chat/delete_group/group_id/'.$group_id);
    }
    /**
     * 群组添加成员 qiu
     *
     * @param $group_id
     * @param $usernames 添加单个(string)或多个(array), 单个为字符串, 多个为数组
     * @param $to_type 1为增加一个人, 2为增加多个人
     * @return array
     */
    public function add_group_users($group_id, $usernames, $to_type)
    {
        $options = array(
            'grouop_id' => $group_id,
            'usernames' => $usernames,
            'to_type' => $to_type
        );
        $curl = new YmCurl();
//        return $curl->post(u_abs('Api/Chat/add_group_users'), $options);
        return $curl->post(self::API_URL.'/chat/add_group_users', $options);
    }

    /**
     * 群组删除成员
     *
     * @param $group_id string
     * @param $usernames array
     * @return array
     */
    public function del_group_users($group_id, $usernames)
    {
        $data = array(
            'group_id' => $group_id,
            'usernames' => $usernames
        );
        $curl = new YmCurl();
//        return $curl->delete(u_abs('Api/Chat/del_group_users'), $data);
        return $curl->post(self::API_URL.'/chat/del_group_users', $data);
    }
} 