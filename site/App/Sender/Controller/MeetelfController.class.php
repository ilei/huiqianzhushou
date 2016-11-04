<?php
namespace Sender\Controller;
use       Sender\Controller\BaseController;

class MeetelfController extends BaseController{

    public function __construct(){
        parent::__construct();
        $this->changedb('MEETELF');
    }

    /**
     * 发送短信 
     * post数据格式  
     * 	array('发送短信的类型：找回密码，注册验证', $mobile, array(6位验证码, 有效期), array(type = '短信|语音'))
     *
     * @access public 
     * @param  void 
     * return  mixed 
     **/ 

    public function sendSMS(){
        list($type, $mobile, $code, $args) = I('post.');
        $args['app_name'] = C('meetelf_name');
        $args['app_url']  = C('meetelf_url');
        return $this->send($type, $mobile, $code, $args);
    }

    /**
     *
     * 发送电子票 
     * 
     * @access public 
     * @param  void 
     * @return mixed 
     **/ 

    public function sendTicket(){
        list($data, $args) = I('post.');
        if(!$data || !$args){
            return false;
        }
        $send_way = $args['send_way'];
        $aguid 	  = $args['aguid'];
        $time     = time();
        $auth     = $args['auth'];
        $m        = $data;
        $acvitity_name = $args['activity_name'];
        // 获取票务信息
        $ticket_info = M('ActivityUserTicket')
            ->field('status, ticket_code, guid')
            ->where(array('user_guid' => $m['user_guid'], 'activity_guid' => $aguid))
            ->find();
        // 当票已验证则跳过
        if($ticket_info['status'] == '4') {
            return;
        }
        $code_prefix = substr($aguid, 0, 4);
        // 判断是否电子票已发, 若已发则维持原电子票号
        if(!empty($ticket_info['ticket_code']) && strlen($ticket_info['ticket_code']) == 19) {
            $ticket_code = $ticket_info['ticket_code'];
        } else {
            $ticket_code = generate_ticket_code($code_prefix);
        }
        $ticket_url = U(C('meetelf_url') . '/Mobile/Activity/ticket', array('aid' => $aguid, 'iid' => $m['guid']), true, true);
        $acvitity_name = html_entity_decode($acvitity_name);
        // 发送SMS
        if(in_array('sms', $send_way) || $send_way == 'sms') {
            $is_send_sms = true;
            $ticket_short_url = get_short_url($ticket_url . '/source/1'); // 长度20
            $content     = '【' . C('meetelf_name') . '】您申请参加的『' . ym_mb_substr($acvitity_name, 10, '...') . '』已通过审核,时间地点详见电子票：' . $ticket_short_url;
            $content_length = mb_strlen($content, 'utf8');
            if ($content_length > 500) {
                return;
            }
            $args['app_name'] = C('meetelf_name');
            $args['app_url'] = C('meetelf_url');
            vendor('YmPush.TicketInfo');
            $sms_result = \TicketInfo::send(C('TICKET_TYPE.sms_ticket'), $m['mobile'], $m, $args);
            if($sms_result['code'] == '0') { // 短信发送成功
                if($sms_result['status'] == 'success') { // 短信发送成功
                    $data_user_ticket = array('ticket_code' => $ticket_code, 'status'=>2, 'updated_at' => $time);
                    $sms_status = 1;
                    $is_send = true;
                    ticket_send_log($auth['user_guid'], $aguid, C('send_sms_ticket_succ'), $ticket_info, $sms_result);
                } else { // 短信发送失败
                    $data_user_ticket = array('ticket_code' => $ticket_code, 'status'=>1, 'updated_at' => $time);
                    ticket_send_log($auth['user_guid'], $aguid, C('send_sms_ticket_fail'), $ticket_info, $sms_result);
                }
            }

            // 发送邮件
            if(in_array('email', $send_way) || $send_way == 'email') {
                $is_send_email = true;
                if(!empty($m['email'])) { // 此为单个重新发送时，可以自定义模板
                    $email = $m['email'];
                } else { // 或者从用户报名填写信息里获取第一个邮箱
                    $email = M('ActivityUserinfoOther')
                        ->where(array('activity_guid' => $aguid, 'userinfo_guid' => $m['guid'], 'ym_type' => 'email'))->order('id asc')->getField('value');
                }
                if(!empty($email) && is_valid_email($email)) {
                    $args['ticket_info'] = $ticket_info;
                    $args['app_name']    = C('meetelf_name'); 
                    $args['app_url']     = C('meetelf_url');
                    $this->views         = $this->view;
                    $args['obj']         = serialize($this);
                    vendor('YmPush.TicketInfo');
                    \TicketInfo::setSender('Mail');
                    $email_result = \TicketInfo::send(C('TICKET_TYPE.mail_ticket'), $email, $m, $args);
                    unset($args['ticket_info']);
                    if($email_result['status'] == 'success') { // 邮件发送成功
                        $data_user_ticket = array(
                            'ticket_code' => $ticket_code, 
                            'status'      =>'2', 
                            'updated_at'  => $time
                        );
                        $email_status = 1;
                        $is_send      = true;
                        ticket_send_log($auth['user_guid'], $aguid, C('send_mail_ticket_succ'), $ticket_info, $email_result);
                    } else { // 邮件发送失败
                        $data_user_ticket = array(
                            'ticket_code' => $ticket_code, 
                            'status'      => '1', 
                            'updated_at'  => $time
                        );
                        ticket_send_log($auth['user_guid'], $aguid, C('send_mail_ticket_fail'), $ticket_info, $email_result);
                    }
                }
            }
            M('ActivityUserTicket')
                ->where(array('user_guid' => $m['user_guid'], 'activity_guid' => $aguid))
                ->save($data_user_ticket);
            return true;
        }
    }

    public  function get_email_content(){
        list($ticket_guid, $acvitity_info, $userinfo) = I('post.');
        vendor('YmPush.Content.MailContent'); 
        $content = new \MailContent(); 
        $args['ticket_info'] = array('guid' => $ticket_guid);
        $args['app_name']    = C('meetelf_name'); 
        $args['app_url']     = C('meetelf_url');
        $this->views         = $this->view;
        $args['obj']         = serialize($this);
        $args['auth']        = session('auth');
        $args['aguid']       = $acvitity_info['guid'];
        $args['activity_name'] = $acvitity_info['name'];
        list($a, $b, $content) = $content->getMailTicketContent($userinfo, $args);
        echo $content;
    }

}
