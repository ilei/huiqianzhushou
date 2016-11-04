<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/1
 * Time: 上午10:12
 */

vendor('YmPush.Content.Content');

class YunPianContent extends Content
{
    protected  $apikey;
    //模板ID
    protected  $template_id;


    public function  getContent($type, $msg_data = array(), $ext = array())
    {
        // TODO: Implement getContent() method.
        $callback = 'get' . ucfirst($type['hook_prefix']) . 'Content';
        $sender = substr(__CLASS__, 0,-7);
        $this->template_id = $type['template_id'][$sender];
        return $this->$callback($msg_data, $ext);
    }


    /**
     * api验证手机号验证码发送内容
     *
     * @access public
     * @param  array $msg_data
     * @param  array $ext
     * @return false or array
     **/

    public function getSmsApiVerMobileContent($msg_data, $ext = array()){

        list($code, $expire) = $msg_data;

        var_dump($code,$expire);


        if(!$code){
            return false;
        }
        $expire = $expire ? intval($expire) : 30;
        $template_id = isset($ext['template_id']) ? $ext['template_id'] : $this->template_id;
        return array($template_id, array('code' => $code, 'time' => $expire));
    }

    /**
     * web邀请注册验证码发送内容
     *
     * @access public
     * @param  array $msg_data
     * @param  array $ext
     * @return false or array
     **/

    public function getSmsSiteInvRegContent($msg_data, $ext = array()){
        $res = $this->getSmsApiVerMobileContent($msg_data, $ext);
        $template_id = isset($ext['template_id']) ? $ext['template_id'] : $this->template_id;
        return array($template_id, $res[1]);
    }

    /**
     * web找回密码验证码发送内容
     *
     * @access public
     * @param  array $msg_data
     * @param  array $ext
     * @return false or array
     **/

    public function getSmsSiteFindPwdContent($msg_data, $ext = array()){
        $res = $this->getSmsApiVerMobileContent($msg_data, $ext);
        $template_id = isset($ext['template_id']) ? $ext['template_id'] : $this->template_id;
        return array($template_id, $res[1]);
    }

    /**
     * api找回密码验证码发送内容
     *
     * @access public
     * @param  array $msg_data
     * @param  array $ext
     * @return false or array
     **/

    public function getSmsApiFindPwdContent($msg_data, $ext = array()){
        $res = $this->getSmsSiteFindPwdContent($msg_data, $ext);
    }

    /**
     * 发送电子票
     *
     * @access public
     * @param  array $msg_data
     * @param  array $ext
     * @return array
     **/

    public function getSmsTicketContent($data, $ext = array()){
        $aguid 	       = $ext['aguid'];
        $activity_name = html_entity_decode($ext['activity_name']);
        $ticket_url    = U($ext['app_url'] . '/Mobile/Activity/ticket', array('aid' => $aguid, 'iid' => $data['guid']), true, true);
        $ticket_short_url = get_short_url($ticket_url . '/source/1'); // 长度20
        $project = isset($ext['project']) ? trim($ext['project']) : $this->template_id;
        return array($project, array('events' => $activity_name, 'url' => $ticket_short_url));
    }
}
