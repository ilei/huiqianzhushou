<?php
namespace Admin\Controller;

use Org\Api\YmChat;

/**
 * 环信数据修复
 * CT: 2015-06-11 17:00 by YLX
 */
class EasemobController extends BaseController
{

    /**
     * 查询会员是否在环信注册成功，若没有注册成功，则重新注册
     * CT: 2015-06-12 10:00 by YLX
     */
    public function ajax_repaire_single_user()
    {
        if(IS_AJAX) {
            $guid = I('post.guid');
            if(strlen($guid) != 32) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '参数错误，请重试。'));
            }

            $chat = new YmChat();
            // 检查用户是否已经注册，但没保存成功
            $check = $chat->userDetails($guid);
            if($check['status'] == 200) { // 若注册成功，则获取环信id
                $easemob_id = $check['entities'][0]['uuid'];
            } else { // 若未注册，则重新注册
                $password = M('User')->where(array('guid' => $guid))->getField('password');
                $reg = $chat->accreditRegister(array('username' => $guid, 'password' => generateEasemobPwd($password)));
                if($reg['status'] == 200) { // 注册成功
                    $easemob_id = $reg['entities'][0]['uuid'];
                } else { // 注册失败
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '操作超时，请重试。'));
                }
            }

            if(!empty($easemob_id)) {
                $data = array('easemob_id' => $easemob_id, 'updated_at' => $easemob_id);
                $result = M('User')->where(array('guid' => $guid))->save($data);
                if($result) { // 保存环信id成功
                    $this->ajaxReturn(array('status' => 'ok', 'msg' => '修复成功', 'easemob_id' => $easemob_id));
                } else { // 保存环信ID失败
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '修复失败, 请重试.'));
                }
            } else {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '操作超时，请重试'));
            }

        } else {
            $this->ajaxReturn(array('status' => 'ko', 'msg' => '页面不存在.'));
        }
    }

    /**
     * 单个修复成员到环信
     * ct: 2015-06-15 12:20 by ylx
     */
    public function ajax_repaire_group_user()
    {
        if(IS_AJAX) {
            $user_guid = I('post.user_guid');
            $chat_group_id = I('post.chat_group_id');
            if(strlen($user_guid) != 32 || empty($chat_group_id)) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '参数错误，请重试。'));
            }

            $chat = new YmChat();
            // 重新注册成员到环信群组
            $result = $chat->addGroupsUser($chat_group_id, $user_guid);

            if($result['status'] == 200) { // 保存环信id成功
                $this->ajaxReturn(array('status' => 'ok', 'msg' => '修复成功'));
            } else { // 保存环信ID失败
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '修复失败, 请重试.'));
            }

        } else {
            $this->ajaxReturn(array('status' => 'ko', 'msg' => '页面不存在.'));
        }
    }

    /**
     *查询该群组是否在环信注册成功，若没有注册成功，则重新注册
     * CT: 2015-06-15 10:00 by YLX
     */
    public function ajax_repaire_single_group()
    {
        if(IS_AJAX) {
            $guid = I('post.guid');
            if(strlen($guid) != 32) {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '参数错误，请重试。'));
            }
            // 获取群组成员
            $member_guids = D('GroupUserDiscMembers')->where(array('group_disc_guid' => $guid, 'is_del' => 0))
                ->getField('user_guid', true);

            $chat = new YmChat();
            // 检查群组是否已经注册，但没保存成功
            $check = $chat->chatGroupsDetails($guid);
            if($check['status'] == 200) { // 若注册成功，则获取环信id
                $chat_group_id = $check['data']['id'];
            } else { // 若未注册，则重新注册
                $group_info = D('GroupUserDisc')->where(array('guid' => $guid))->find();
                if(empty($group_info)) {
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '群组不存在'));
                }
                $option = array(
                    'groupname' => $guid,
                    'desc'      => $group_info['name'],
                    'public'    => false,
                    'owner'     => $group_info['creater_guid'],
                    'members'   => $member_guids
                );
                $reg = $chat->createGroups($option);
                if($reg['status'] == 200) { // 注册成功
                    $chat_group_id = $reg['data']['groupid'];
                } else { // 注册失败
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '操作超时，请重试。'));
                }
            }

            if(!empty($chat_group_id)) {
                $data = array('chat_group_id' => $chat_group_id, 'updated_at' => time());
                $result = M('GroupUserDisc')->where(array('guid' => $guid))->save($data);
                if($result) { // 保存环信id成功
                    $push = new \YmPush();
                    $msg = array(
                        'from_id'  => C('ADMIN_GUID'),
                        'from_name'  => C('ADMIN_NAME'),
                        'from_iconID' => '',
                        'to_id'    => $guid, // 群组guid
                        'to_name'    => '',
                        'to_iconID'    => '',
                        'content'    => $chat_group_id, // 环信id
                        'send_time'  => time(),
                        'msg_type'  => '11101',
                        'type' => C('MESSAGE.FIX_GROUP'), // 11011
                        'is_read' => 0
                    );
                    $push->pushList($member_guids, $msg);
                    $this->ajaxReturn(array('status' => 'ok', 'msg' => '修复成功', 'chat_group_id' => $chat_group_id));
                } else { // 保存环信ID失败
                    $this->ajaxReturn(array('status' => 'ko', 'msg' => '修复失败, 请重试.'));
                }
            } else {
                $this->ajaxReturn(array('status' => 'ko', 'msg' => '操作超时，请重试'));
            }

        } else {
            $this->ajaxReturn(array('status' => 'ko', 'msg' => '页面不存在.'));
        }
    }
}
