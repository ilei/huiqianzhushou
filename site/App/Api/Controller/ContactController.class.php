<?php
namespace Api\Controller;
use Think\Crypt;

vendor('getui.YmPush');

/**
 * 人脉控制器
 *
 * CT: 2014-09-28 11:00 by YLX
 *
 */
class ContactController extends BaseUserController
{
    /**
     * 一度人脉 获取&更新
     *
     * user_guid: 通过登陆token获取
     * ut: 表示app端的更新时间, 0或者未传表是获取全部一度人脉列表, 大于0时, 表示取更新时间大于所给时间的人脉信息
     *
     * CT: 2014-09-28 11:00 by YLX
     * UT: 2015-03-23 12:06 by YLX
     */
    public function one() {
        $this->check_request_method('get');
        $user_guid = $this->user_info['guid'];
        $res = D('Contacts')->getOnceContactsByUser($user_guid, I('get.ut', 0));
        if(empty($res)) $this->output_error('10013','未找到符合要求的数据.'); // APP端要求
        $this->output_data(array('total' => count($res['list_c']), 'list' => $res, 'updated_at' => time()));
    }

    /**
     * 人脉详细信息
     *
     * CT: 2014-09-28 11:51 by YLX
     */
    public function detail()
    {
        $this->check_request_method('get');
        $user_guid = I('get.guid');
        if (empty($user_guid)) $this->output_error('10003'); // 参数错误

        // 获取用户信息
        $res = D('User')->getUserInfo($user_guid);
        if (empty($res)) $this->output_error('10009');

        // 判断用户信息是否最新
        if ($res['updated_at'] <= I('get.ut')) {
            $this->output_error('10013');
        }

        // 获取用户详细信息
        $info = D('User')->getDetail($user_guid, $this->user_info['guid']);
        if (empty($info)) $this->output_error('10009');

        // html字符反转
        foreach($info['company'] as $key=>$value){
            foreach($value as $k=>$v) {
                $info['company'][$key][$k] = htmlspecialchars_decode($v);
            }
        }

        $this->output_data($info);
    }

    /**
     * 搜索好友
     *
     * 只支持好友real_name
     *
     * CT: 2014-10-27 17:20 by YLX
     */
    public function search_contact()
    {
        $this->check_request_method('get');
        $user_guid = $this->user_info['guid'];
        $keyword = trim(I('get.k'));
        if (empty($keyword)) $this->output_error('10003');
        $r = D('User')->searchBy('real_name', $keyword, I('get.p', 1), $user_guid, true);

        if (empty($r)) $this->output_error('10009');
        else $this->output_data(array('list'=>$r));
    }

    /**
     * 发送好友申请
     *
     * 参数: $tid 目标用户GUID
     *
     * CT: 2014-10-27 17:20 by YLX
     */
    public function apply_contact()
    {
        $this->check_request_method('post');
        $tid = I('post.tid');
        $mid = $this->user_info['guid'];
        if (empty($tid)) $this->output_error('10003');
        $time = time();
        $model_contacts = D('Contacts');

//        $where = "(user_guid_1='$tid' AND user_guid_2='$mid') OR (user_guid_1='$mid' AND user_guid_2='$tid')";

        // 检查是否已经发出过申请
        $where_a2b = array(
            'user_guid_1' => $mid,
            'user_guid_2' => $tid
        );
        $a2b = $model_contacts->where($where_a2b)->getField('status');
        // 检查是否已收到过请求
        $where_b2a = array(
            'user_guid_1' => $tid,
            'user_guid_2' => $mid
        );
        $b2a = $model_contacts->where($where_b2a)->getField('status');
        // 当B同意A时, 判断$a2b是否存在, 若不存在, 创建一个同意的行, 若存在, 更新status为2, 并返回两者已经是好友.
        if ($b2a == '2') {
            if(empty($a2b)) {
                $data = array(
                    'guid'        => create_guid(),
                    'user_guid_1' => $mid,
                    'user_guid_2' => $tid,
                    'created_at'  => $time,
                    'updated_at'  => $time,
                    'status'      => '2'
                );
                $res = $model_contacts->data($data)->add();
            } else {
                $res = $model_contacts->where($where_a2b)->setField('status', '2');
            }
            if (!empty($res)) {
                $this->output_error('10029', 'friend add success.');
            } else {
                $this->output_error('10011', 'add failed');
            }
        } else { // 若B对A的status不为2, 则更新A对B的申请状态
            switch($a2b)
            {
                case '1':
//                    $this->output_error('10019', 'you have sent the request.');
                    $res = true;
                    break;
                case '2':
                    $this->output_error('10021', 'you have already added him.');
                    break;
                case '3':
                    // 创建申请数据
                    $data = array(
                        'updated_at' => $time,
                        'status' => '1'
                    );
                    $res = M('Contacts')->where($where_a2b)->data($data)->save();
                    break;
                case '4':
                    $this->output_error('10020', 'you are in his black list.');
                    break;
                default:
                    // 创建申请数据
                    $data = array(
                        'guid'        => create_guid(),
                        'user_guid_1' => $mid,
                        'user_guid_2' => $tid,
                        'created_at'  => $time,
                        'updated_at'  => $time,
                        'status'      => '1'
                    );
                    $res = M('Contacts')->data($data)->add();
                    break;
            }

            if (empty($res)) {
                $this->output_error('10011', 'op failed');
            }else {
                $me = $this->user_info;
                $he = D('User')->getUserInfo($tid);
                $json = array(
                    'from_id'  => $mid,
                    'from_name'  => $me['real_name'],
                    'from_iconID' => $me['photo_'.C('API_PHOTO.user_list', null, 120)],
                    'to_id'    => $tid,
                    'to_name'    => isset($he['real_name'])?$he['real_name']:$he['email'],
                    'to_iconID'   => $he['photo_'.C('API_PHOTO.user_list', null, 120)],
                    'content'    => '我申请加你为好友.',
                    'send_time'  => time(),
                    'msg_type'  => '11101',
                    'type'      => '11007'
                );

                $chat = new \YmPush();
                $chat->pushMessageToSingle($tid, json_encode($json));
                $this->output_data();
            }
        }
    }

    /**
     * 回复好友申请
     *
     * 参数: $tid 目标用户GUID
     *       $type 2为通过, 3为拒绝
     *       $reason 拒绝原因
     *
     * CT: 2014-10-27 17:20 by YLX
     */
    public function confirm_contact()
    {
        $this->check_request_method('put');
        $params = $this->_request_params;
        $mid = $this->user_info['guid'];
        $tid = $params['tid'];
        $type = $params['type'];
        $reason = trim($params['reason']);
        if (empty($mid) || empty($tid) || empty($type)) $this->output_error('10003');

        $model_contacts = D('Contacts');
        // 检查申请是否存在
        $where_b2a = array('user_guid_1'=>$tid, 'user_guid_2'=>$mid, 'status' => '1');
        $check_b2a = $model_contacts->where($where_b2a)->find();
        if(!$check_b2a) $this->output_error('10003');

        // A对B条件
        $where_a2b = array(
            'user_guid_1' => $mid,
            'user_guid_2' => $tid
        );

        $time = time();
        $data = array('status'=>$type, 'reply'=>$reason, 'updated_at'=>$time);
        switch($type) {
            case '2':  // 同意
                // 更新b2a状态
                $model_contacts->where($where_b2a)->save($data);
                // 叛断a2b是否存在, 不存在则他建一条, 存在则更新状态
                $a2b = $model_contacts->where($where_a2b)->getField('status');
                if(empty($a2b)) {
                    $data = array(
                        'guid'        => create_guid(),
                        'user_guid_1' => $mid,
                        'user_guid_2' => $tid,
                        'created_at'  => $time,
                        'updated_at'  => $time,
                        'status'      => '2'
                    );
                    $r = $model_contacts->add($data);
                } else {
                    $r = $model_contacts->where($where_a2b)->save(array('status' => '2', 'updated_at' => $time));
                }

                //时间轴-添加好友
                $t_name = M('User')->field('real_name')->where(array('guid'=>$tid))->getField('real_name');
                $m_name = M('User')->field('real_name')->where(array('guid'=>$mid))->getField('real_name');
                D('UserTimeline')->record($mid, '4', $tid, $t_name);
                D('UserTimeline')->record($tid, '4', $mid, $m_name);

                break;
            case '3': // 拒绝
                // 删除B对A的请求
                $model_contacts->where($where_b2a)->delete();//->save($data);
                // 更新A对B的状态为拒绝
                $model_contacts->where($where_a2b)->save($data);
                // 返回操作成功, 不作任务处理
                $this->output_data();
                break;
            default:
                $this->output_error('10003');
                break;
        }

        if (empty($r)) {
            $this->output_error('10011');
        }else {
            $me = $this->user_info;
            $he = D('User')->getUserInfo($tid);
            $json = array(
                'from_id'     => $mid,
                'from_name'   => $me['real_name'],
                'from_iconID' => $me['photo_'.C('API_PHOTO.user_list', null, 120)],
                'to_id'       => $tid,
                'to_name'     => isset($he['real_name']) ? $he['real_name'] : $he['email'],
                'to_iconID'   => $he['photo_'.C('API_PHOTO.user_list', null, 120)],
                'content'     => '我通过了你的好友验证请求, 现在我们可以开始聊天了',
                'send_time'   => time(),
                'msg_type'    => '11101',
                'type'        => '11005'
            );
			/*
            $chat = new YmChat();
            $chat->sendMsg($mid, array($tid), 'txt', $json['content'], 'users', array('content' => $json));
			*/
			$chat = new \YmPush();
			$chat->pushMessageToSingle($tid, json_encode($json));

            $this->output_data(array('remark'=>trim($he['remark'])));
        }
    }

    /**
     * 好友申请列表
     *
     * CT: 2014-10-27 17:45 by YLX
     */
    public function list_apply()
    {
        $mid = $this->user_info['guid'];
        if (empty($mid)) $this->output_error('10003');

        $where = array('c.user_guid_2'=>$mid, 'status'=>array('IN', array('1', '2', '3')));
        $r = M('Contacts')->alias('c')
            ->join('ym_user u ON u.guid = c.user_guid_1')
            ->field('u.real_name, u.guid, u.photo_'.C('API_PHOTO.user_list', null, 120).' as photo, u.remark, c.status, u.edu, u.home_areaid_2, u.main_industry_guid')
            ->where($where)
            ->order('c.updated_at DESC')
            ->select();

        // 统计好友共同信息
        $r = $this->process_pepole($r);

        if (empty($r)) $this->output_error('10009');
        else $this->output_data(array('list'=>$r));
    }

    /**
     * 删除好友
     *
     * CT: 2014-10-27 17:45 by YLX
     */
    public function del()
    {
        $this->check_request_method('delete');
        $mid = $this->user_info['guid'];
        $tid = I('get.tid');
        if (empty($mid) || empty($tid)) $this->output_error('10003');

        $r = M('Contacts')->where('user_guid_1="'.$tid.'" AND user_guid_2="'.$mid.'"')->delete();

        //时间轴-删除好友
        $tid_info = M('User')->field('real_name')->where(array('guid'=>$tid))->find();
        D('UserTimeline')->record($mid, '5', $tid, $tid_info['real_name']);

        if (empty($r)) $this->output_error('10011');
        else $this->output_data();
    }

    /**
     * 获取用户所参加社团的GUID
     *
     * 请求参数: user_guid 当前用户guid
     * CT: 2014-10-24 15:51 by YLX
     */
    private function get_user_org_guid_list($user_guid)
    {
        $list = D('User')->get_org_guid_list($user_guid);
        return !empty($list)?$list:array('ym365');
    }

    /**
     * 获取用户一度人脉GUID列表
     *
     * 请求参数: user_guid 当前用户guid
     * CT: 2014-10-24 15:51 by YLX
     */
    private function get_user_one_guid_list($user_guid)
    {
        $list = D('Contacts')->getOneGuid($user_guid);
        return !empty($list)?$list:array('ym365');
    }

    /**
     *  获取相同社团下所有非好友的用户guid列表
     * @param $user_guid
     * @return array|bool
     */
    private function get_user_guids_in_same_org($user_guid)
    {
        // 获取当前用户所参加的社团GUID列表
        $org_guid_list = $this->get_user_org_guid_list($user_guid);
        if (empty($org_guid_list)) return false;

        // 获取一度人脉GUID
        $one = $this->get_user_one_guid_list($user_guid);
        // 获取同社团的user guid列表
        $user_guids = M('OrgGroupMembers')
            ->where(array('org_guid' => array('IN', $org_guid_list),
                          'user_guid' => array(array('NEQ', $user_guid), array('NOT IN', $one), 'AND'))
                    )
            ->group('user_guid')
            ->getField('user_guid', true);

        return !empty($user_guids)?$user_guids:array('ym365');
    }

    /**
     * 计算当前用户与二度人脉的相关性
     *
     * $list 二度人脉列表
     *
     * CT: 2014-10-31 10:11 by YLX
     */
    private function process_pepole($list)
    {
        if(empty($list)) return false;

        // 当前用户信息
        $model_user = D('User');
        $me = $this->user_info;
        $mid = $me['guid'];

        foreach ($list as $k => $l){
            // 计算是否同校
            $list[$k]['same_school'] = ($l['edu'] == $me['edu'])?'1':'0';

            // 计算是否同乡
            $list[$k]['same_home'] = ($l['home_areaid_2'] == $me['home_areaid_2'])?'1':'0';

            // 是否同行业
            $list[$k]['same_industry'] = ($l['main_industry_guid'] == $me['main_industry_guid'])?'1':'0';

            // 是否同兴趣
            $m_interest_ids = $model_user->getInterestIds($mid);
            $t_interest_ids = $model_user->getInterestIds($l['guid']);
            $c_interest_ids = array_intersect($m_interest_ids, $t_interest_ids);
            $list[$k]['same_interest'] = !empty($c_interest_ids)?strval(count($c_interest_ids)):'0';

            // 是否同社团
            $m_org_ids = $model_user->get_org_guid_list($mid);
            $t_org_ids = $model_user->get_org_guid_list($l['guid']);
            $c_org_ids = array_intersect($m_org_ids, $t_org_ids);
            $list[$k]['same_org'] = !empty($c_org_ids)?strval(count($c_org_ids)):'0';
        }

        return $list;

    }


    /**
     * 获取同社团下二度人脉: by 某一属性
     *
     * 请求参数: user_guid 当前用户guid, p 当前页数
     *
     * 目前支持 main_industry_guid, edu
     * CT: 2014-10-24 15:51 by YLX
     */
    private function get_two_by_attr($attr, $value)
    {
        if (empty($attr)) $this->output_error('10003');
        $mid = $this->user_info['guid'];
        $num_per_page = C('NUM_PER_PAGE', null, 10);
        // 获取一度人脉GUID
        $one = $this->get_user_one_guid_list($mid);

        $type = I('get.type', 'org');
        switch($type) {
            case 'all': //返回所有同属性下的全部二度人脉
                $where = array(
                    $attr => $value,
                    'guid' => array('NOT IN', $one),
                    'is_active' => '1',
                    'type' => '1',
                    'is_del' => '0'
                );
                if(empty($value) || $value == 'null'){
                    $where[$attr] = array('exp', 'is null');
                }
                $res = M('User')->field('guid, real_name, photo_'.C('API_PHOTO.user_list', null, 120).' as photo, remark, edu, home_areaid_2, main_industry_guid')
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . $num_per_page)
                    ->select();
                break;
            case 'org': // 返回所有同属性下的同社团二度人脉
                // 获取当前用户所参加的社团列表
                $org_guid_list = $this->get_user_org_guid_list($mid);
                if (empty($org_guid_list)) $this->output_error('10009');

                $where = array(
                    'm.org_guid' => array('IN', $org_guid_list),
                    'u.guid' => array(array('NOT IN', $one), array('neq', $mid)),
                    'u.' . $attr => $value,
                    'u.is_active' => '1',
                    'u.type' => '1',
                    'u.is_del' => '0'
                );
                if(empty($value) || $value == 'null'){
                    $where['u.'.$attr] = array('exp', 'is null');
                }
                $res = M('User')->alias('u')
                    ->join('ym_org_group_members m ON m.user_guid = u.guid')
                    ->field('u.guid, u.real_name, u.photo_'.C('API_PHOTO.user_list', null, 120).' as photo, u.remark, u.edu, u.home_areaid_2, u.main_industry_guid')
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . $num_per_page)
                    ->group('u.guid')
                    ->select();
                break;
            case 'activity':
                // 获取同会场下所有非好友的用户guid列表
                $user_guids = $this->get_user_guids_in_same_activity($mid);
                $where = array(
                    $attr => $value,
                    'guid' => array('IN', $user_guids),
                    'is_active' => '1',
                    'type' => '1',
                    'is_del' => '0'
                );
                if(empty($value) || $value == 'null'){
                    $where[$attr] = array('exp', 'is null');
                }
                $res = M('User')->field('guid, real_name, photo_'.C('API_PHOTO.user_list', null, 120).' as photo, remark, edu, home_areaid_2, main_industry_guid')
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . $num_per_page)
                    ->select();
                break;
            default:
                $this->output_error('10003');
                break;
        }

        // 计算当前用户与二度人脉的相关性
        $res = $this->process_pepole($res);
        //RTH
        if(empty($user_guids)) {
            $user_guids = array();
            foreach($res as $r) {
                $user_guids[] = $r['guid'];
            }
        }
        $res = $this->_check_apply_status($res, $mid, $user_guids);
        //RTH

        if (empty($res)) {
            $this->output_error('10009');
        } else {
            $this->output_data($res);
        }
    }

    /**
     * 添加人脉列表的申请状态标识
     * @param $res 人脉 用户好友返回列表
     * @param $mid 用户guid
     * @param $user_guids 用户一度二度人脉
     * @return $res 人脉 用户好友列表（新增状态一项 status）
     */
    private function _check_apply_status($res, $mid, $user_guids)
    {
        //获取好友申请提交状态
        $contacts_apply_guids = D('Contacts')
            ->field('user_guid_2')
            ->where(array('user_guid_1' => $mid,
                          'user_guid_2' => array('in',$user_guids),
                          'status' => '1',
                          'is_del' => '0'))
            ->getField('user_guid_2', true);
        foreach($res as $k=>$v){
            if(in_array($v['guid'], $contacts_apply_guids)){
                $res[$k]['status'] = '1'; // 1  已发送申请
            }else{
                $res[$k]['status'] = '0'; // 0  未发送申请
            }
        }
        return $res;
    }

    /**
     * 获取二度人脉: 同社团/同会场
     *
     * 请求参数: user_guid 当前用户guid
     * CT: 2014-10-24 15:51 by YLX
     * ut: 2015-06-11 11:40 by ylx
     */
    public function org_two()
    {
        $this->check_request_method('get');
        $mid = $this->user_info['guid'];
        $type = I('get.type', 'org'); // 获取类型， org社团的二度人脉， activity会场的二度人脉

        switch($type) {
            case 'org':
                // 获取同社团下的所有用户guid列表
                $user_guids = $this->get_user_guids_in_same_org($mid);
                break;
            case 'activity':
				$activity_id = I('get.aid');
                // 获取同会场下所有非好友的用户guid列表
                $user_guids = $this->get_user_guids_in_same_activity($mid, true, $activity_id);
                break;
            default:
                $this->output_error('10003');
                break;
        }
        if(empty($user_guids) || in_array('ym365', $user_guids)) {
            $this->output_error('10009');exit();
        }

        // 获取同社团下所有非好友的用户guid列表
//        $user_guids = $this->get_user_guids_in_same_activity($mid);
//        if(empty($user_guids)) {
//            $this->output_error('10009');
//        }
//        // 获取同社团下所有非好友的用户guid列表
//        $user_guids = $this->get_user_guids_in_same_org($mid);

        // 二度人脉列表
        $res = D('User')
            ->field('guid, real_name, photo_'.C('API_PHOTO.user_list', null, 120).' as photo, remark, edu, home_areaid_2, main_industry_guid')
            ->where(array('guid' => array('IN', $user_guids),
                          'is_active' => '1',
                          'type' => '1',
                          'is_del' => '0'))
            ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE', null, 10))
            ->select();

        //RTH
        $res = $this->_check_apply_status($res, $mid, $user_guids);
        //RTH

        // 计算当前用户与二度人脉的相关性
        $res = $this->process_pepole($res);

        if (empty($res)) $this->output_error('10009');
        else $this->output_data($res);
    }

    /**
     * 会场/社团的人脉 – 统计：field =  行业industry,  教育edu， 兴趣interest,  地区area
     *
     * CT: 2015-01-20 12:20 BY YLX
     * ut: 2015-06-11 11:40 by ylx
     */
    public function org_two_stat() {
        $this->check_request_method('get');
        $mid = $this->user_info['guid'];
		$field = I('get.field');
        if(empty($field)) {
            $this->output_error('10003');
        }
        $type = I('get.type', 'org'); // 获取类型， org社团的二度人脉， activity会场的二度人脉

        switch($type) {
            case 'org':
                // 获取同社团下的所有用户guid列表
                $user_guids = $this->get_user_guids_in_same_org($mid);
                break;
            case 'activity':
				$activity_id = I('get.aid');
                // 获取同社团下所有非好友的用户guid列表
                $user_guids = $this->get_user_guids_in_same_activity($mid, true, $activity_id);
                break;
            default:
                $this->output_error('10003');
                break;
        }
        if(empty($user_guids) || in_array('ym365', $user_guids)) {
            $this->output_error('10009');exit();
        }

        // 获取同社团下非一度人脉查询条件
        $where = array(
            'u.guid' => array('IN', $user_guids),
            'u.is_active' => '1',
            'u.is_del' => '0',
            'u.type' => '1'
        );

        // 根据field信息给出统计详情
        switch($field) {
            case 'industry':
                $result = M('User')->alias('u')
                    ->join('ym_industry i ON u.main_industry_guid = i.guid', 'left')
                    ->field('u.main_industry_guid as industry_guid, i.name, COUNT(u.guid) as num')
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE', '', 10))
                    ->group('u.main_industry_guid')
                    ->select();
                break;
            case 'edu':
                $result = M('User')->alias('u')
                    ->join('ym_school s ON u.edu = s.id', 'left')
                    ->field('u.edu as edu_id, s.name, COUNT(u.guid) as num')
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE', '', 10))
                    ->group('u.edu')
                    ->select();
                break;
            case 'interest':
                // 获取拥有兴趣的用户guid
                $has_interest_user_guids = M('UserInterest')
                    ->where(array('user_guid' => array('IN', $user_guids)))
                    ->group('user_guid')
                    ->getField('user_guid', true);
                $result = M('User')->alias('u')
                    ->join('ym_user_interest ui ON ui.user_guid = u.guid', 'left')
                    ->join('ym_interest i ON ui.interest_id = i.id')
                    ->field('ui.interest_id, i.name, COUNT(ui.interest_id) as num')
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE', '', 10))
                    ->group('ui.interest_id')
                    ->select();
                // 当页数为第一页时, 增加为选择兴趣用户统计
                if(I('get.p', 1) == 1){
                    // 获取没有选择兴趣的用户guid
                    $no_interest_user_guids = array_diff($user_guids, $has_interest_user_guids);
                    $count_no_interest = count($no_interest_user_guids);
                    if($count_no_interest > 0) {
                        $nul_interest_stat = array('interest_id' => null, 'name' => null, 'num' => $count_no_interest);
                        array_unshift($result, $nul_interest_stat);
                    }
                }
                break;
            case 'area':
                $area_field = I('get.area_field', 'areaid_1');
                $parent_area_id = I('get.parent_id');

                // 若客户端传来为其它， 即没有地区， 则直接跳转二度人脉列表
                if($parent_area_id == 'null'){
                    $this->get_two_by_attr($area_field, $parent_area_id);
                }

                if (!empty($parent_area_id) && is_numeric($parent_area_id)) {
                    $where['a.parent_id'] = $parent_area_id;
                }
                $result = M('User')->alias('u')
                    ->join('ym_area a ON a.id = u.' . $area_field, 'left')
                    ->field("u.$area_field, a.name, COUNT(u.guid) as num")
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE', '', 10))
                    ->group('u.'.$area_field)
                    ->select()
                ;
                break;
            default:
                $this->output_error('10003');
                break;
        }

        if (empty($result)) {
            $this->output_error('10009');
        } else {
            $this->output_data($result);
        }
    }

    /**
     * 社团的人脉 – 列表：field =  行业industry,  教育edu， 兴趣interest,  地区area
     *
     * CT: 2015-01-20 12:20 BY YLX
     */
    public function org_two_list() {
        $this->check_request_method('get');
		$field = I('get.field');
        if(empty($field)) {
            $this->output_error('10003');
        }

        switch($field) {
            case 'industry':
                $industry_guid = I('get.industry_guid');
                if(empty($industry_guid)) {
                    $this->output_error('10003');
                }
                $this->get_two_by_attr('main_industry_guid', $industry_guid);
                break;
            case 'edu':
                $edu_id = I('get.edu_id');
                if(empty($edu_id)) {
                    $this->output_error('10003');
                }
                $this->get_two_by_attr('edu', $edu_id);
                break;
            case 'area':
                $area_id = I('get.area_id');
                $area_field = I('get.area_field');
                if(empty($area_field)) {
                    $this->output_error('10003');
                }
                $this->get_two_by_attr($area_field, $area_id);
                break;
            case 'interest':
                $interest_id = I('get.interest_id');
                $mid = $this->user_info['guid'];

                $num_per_page = C('NUM_PER_PAGE');
                // 获取一度人脉GUID
                $one = $this->get_user_one_guid_list($mid);

                $type = I('get.type', 'org');
                switch($type) {
                    case 'all': //返回所有同兴趣下的全部二度人脉
                        $where = array(
                            'ui.interest_id' => $interest_id,
//                            'ui.user_guid' => array('NOT IN', $one),
                            'ui.user_guid' => array(array('NOT IN', $one), array('neq', $mid)),
                            'u.is_active' => '1',
                            'u.type' => '1',
                            'u.is_del' => '0'
                        );
                        if(empty($interest_id) || $interest_id == 'null'){
                            $where['ui.interest_id'] = array('exp', 'is null');
                        }
                        $res = M('User')->alias('u')
                            ->join('ym_user_interest ui ON ui.user_guid = u.guid', 'left')
                            ->field('u.guid, u.real_name, u.photo_'.C('API_PHOTO.user_list', null, 120).' as photo, u.remark, u.edu, u.home_areaid_2, u.main_industry_guid')
                            ->where($where)
                            ->page(I('get.p', '1') . ',' . $num_per_page)
                            ->select();
                        break;
                    case 'org':// 返回所有同兴趣下的同社团二度人脉
                        // 获取当前用户所参加的社团列表
                        $org_guid_list = $this->get_user_org_guid_list($mid);
                        if (empty($org_guid_list)) $this->output_error('10009');

                        if(empty($interest_id) || $interest_id == 'null') {
                            $user_guids_in_same_org = $this->get_user_guids_in_same_org($mid);
                            $has_interest_user_guids = M('UserInterest')->where(array('user_guid' => array('IN', $user_guids_in_same_org)))->group('user_guid')->getField('user_guid', true);
                            $no_interest_user_guids = array_diff($user_guids_in_same_org, $has_interest_user_guids);
                            $res = M('User')->field('guid, real_name, photo_'.C('API_PHOTO.user_list', null, 120).' as photo, remark, edu, home_areaid_2, main_industry_guid')
                                ->where(array('guid' => array('in', $no_interest_user_guids)))
                                ->page(I('get.p', 1), $num_per_page)
                                ->select();
                        } else {
                            $where = array(
                                'm.org_guid'     => array('IN', $org_guid_list),
//                                'u.guid'         => array('NOT IN', $one),
                                'u.guid' => array(array('NOT IN', $one), array('neq', $mid)),
                                'ui.interest_id' => $interest_id,
                                'u.is_active'    => '1',
                                'u.type'         => '1',
                                'u.is_del'       => '0'
                            );
                            $res   = M('User')->alias('u')
                                ->join('ym_org_group_members m ON m.user_guid = u.guid', 'left')
                                ->join('ym_user_interest ui ON ui.user_guid = u.guid', 'left')
                                ->field('u.guid, u.real_name, u.photo_'.C('API_PHOTO.user_list', null, 120).' as photo, u.remark, u.edu, u.home_areaid_2, u.main_industry_guid')
                                ->where($where)
                                ->page(I('get.p', '1') . ',' . $num_per_page)
                                ->group('u.guid')
                                ->select();
                        }
                        break;
                    case 'activity': //返回所有同兴趣下的同会场全部二度人脉
                        // 获取同会场下所有非好友的用户guid列表
						$aid = I('get.aid');
                        $user_guids = $this->get_user_guids_in_same_activity($mid, true, $aid);
                        if(empty($interest_id) || $interest_id == 'null'){
                            $has_interest_user_guids = M('UserInterest')
                                ->where(array('user_guid' => array('IN', $user_guids)))
                                ->group('user_guid')
                                ->getField('user_guid', true);
                            $no_interest_user_guids = array_diff($user_guids, $has_interest_user_guids);
                            $res = M('User')->field('guid, real_name, photo_'.C('API_PHOTO.user_list', null, 120).' as photo, remark, edu, home_areaid_2, main_industry_guid')
                                ->where(array('guid' => array('in', $no_interest_user_guids)))
                                ->page(I('get.p', 1), $num_per_page)
                                ->select();
                        } else {
                            $where = array(
                                'ui.interest_id' => $interest_id,
                                'ui.user_guid' => array('IN', $user_guids),
                                'u.is_active' => '1',
                                'u.type' => '1',
                                'u.is_del' => '0'
                            );
                            $res = M('User')->alias('u')
                                ->join('ym_user_interest ui ON ui.user_guid = u.guid', 'left')
                                ->field('u.guid, u.real_name, u.photo_'.C('API_PHOTO.user_list', null, 120).' as photo, u.remark, u.edu, u.home_areaid_2, u.main_industry_guid')
                                ->where($where)
                                ->page(I('get.p', '1') . ',' . $num_per_page)
                                ->select();
                        }
                        break;
                    default:
                        $this->output_error('10003');
                        break;
                }

                // 计算当前用户与二度人脉的相关性
                $res = $this->process_pepole($res);
                //RTH
                if(empty($user_guids)) {
                    $user_guids = array();
                    foreach($res as $r) {
                        $user_guids[] = $r['guid'];
                    }
                }
                $res = $this->_check_apply_status($res, $mid, $user_guids);
                //RTH

                if (empty($res)) {
                    $this->output_error('10009');
                } else {
                    $this->output_data($res);
                }
                break;
            default:
                $this->output_error('10003');
                break;
        }
    }

    /**
     * 云友的云友GUID列表
     * @param $mid
     * @return array
     */
    private function get_user_friend_two_guid_list($mid) {
        // 获取一度人脉GUID
        $one = $this->get_user_one_guid_list($mid);
        if (empty($one)) return false;

        // 获取所有一度人脉的好友guid
        $one_guids = implode("','", $one);
        $where = "(user_guid_1 in('$one_guids') and user_guid_2 != '$mid' and user_guid_2 not in('$one_guids')) or "
            ."(user_guid_2 in('$one_guids') and user_guid_1 != '$mid' and user_guid_1 not in('$one_guids'))";
        $map = array(
            '_string' => $where,
            'is_del'    => '0',
            'status'    => '2'
        );
        $res = D('Contacts')->where($map)->select();
        if(empty($res)) return false;

        $two_guids = array();
        $i = 0;
        foreach ($res as $r) {
            if (in_array($r['user_guid_1'], $one)) {
                $two_guids[$i] = $r['user_guid_2'];
            } elseif (in_array($r['user_guid_2'], $one)) {
                $two_guids[$i] = $r['user_guid_1'];
            }
            $i++;
        }

        if (empty($two_guids)) return false;

        return array_unique($two_guids);
    }

    /**
     *
     * 根据云友的好友, 获取二度人脉: by 某一属性
     *
     * @param $attr 属性名
     * @param $value 属性值
     *
     * CT: 2014-10-31 16:11 by YLX
     */
    private function get_friend_two_by_attr($attr, $value)
    {
        $user_guid = $this->user_info['guid'];
        // 获取所有一度人脉的好友guid
        $two_guids = $this->get_user_friend_two_guid_list($user_guid);

        $num_per_page = C('NUM_PER_PAGE', null, 10);
        $where = array(
            'guid' => array(array('IN', $two_guids), array('NEQ', $user_guid)),
            $attr => $value,
            'is_active' => '1',
            'type' => '1',
            'is_del' => '0'
        );
        if(empty($value) || $value == 'null' || $value == ''){
            $where[$attr] = array('exp', 'is null');
        }
        $res = M('User')->alias('u')
            ->field('guid, real_name, photo_'.C('API_PHOTO.user_list', null, 120).' as photo, remark, edu, home_areaid_2, main_industry_guid')
            ->where($where)
            ->page(I('get.p', '1') . ',' . $num_per_page)
            ->select();

        // 计算当前用户与二度人脉的相关性
        $res = $this->process_pepole($res);

        //RTH
        $res = $this->_check_apply_status($res, $user_guid, $two_guids);
        //RTH
        if (empty($res)) {
            $this->output_error('10009');
        } else {
            $this->output_data($res);
        }
    }

    /**
     * 获取二度人脉: 云友的人脉
     *
     * 请求参数: user_guid 当前用户guid
     * CT: 2014-10-31 11:51 by YLX
     */
    public function friend_two()
    {
        $this->check_request_method('get');
        $user_guid = $this->user_info['guid'];

        // 获取云友的云友guid
        $two_guids = $this->get_user_friend_two_guid_list($user_guid);
        if(empty($two_guids)) {
            $this->output_error('10009', 'no data found');
        }

        // 二度人脉列表
        $where = array(
            'guid' => array(array('IN', $two_guids), array('neq', $user_guid)),
            'is_active' => '1',
            'is_del' => '0',
            'type' => '1'
        );
        $res = D('User')
            ->field('guid, real_name, photo_'.C('API_PHOTO.user_list', null, 120).' as photo, remark, edu, home_areaid_2, main_industry_guid')
            ->where($where)
            ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE'))
            ->select();

        // 计算当前用户与二度人脉的相关性
        $res = $this->process_pepole($res);

//        //RTH
       $res = $this->_check_apply_status($res,$user_guid,$two_guids);
//        //RTH

        if (empty($res)) $this->output_error('10009');
        else $this->output_data($res);
    }

    /**
     * 云友的人脉 – 统计：field =  行业industry,  教育edu， 兴趣interest,  地区area
     *
     * CT: 2015-01-20 12:20 BY YLX
     */
    public function friend_two_stat() {
        $this->check_request_method('get');
        $mid = $this->user_info['guid'];
        $field = $this->_request_params['field'];
        if(empty($field)) {
            $this->output_error('10003');
        }
        // 获取所有一度人脉的好友guid
        $two_guids = $this->get_user_friend_two_guid_list($mid);
        if(empty($two_guids)) {
            $this->output_error('10009', 'data not found');
        }

        // 获取云友的人脉按行业统计
        $where = array(
            'u.guid' => array(array('IN', $two_guids), array('neq', $mid)),
            'u.is_active' => '1',
            'u.is_del' => '0',
            'u.type' => '1'
        );

        // 根据field信息给出统计详情
        switch($field) {
            case 'industry':
                $result = M('User')->alias('u')
                    ->join('ym_industry i ON u.main_industry_guid = i.guid', 'left')
                    ->field('u.main_industry_guid as industry_guid, i.name, COUNT(u.guid) as num')
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE', '', 10))
                    ->group('u.main_industry_guid')
                    ->select();
                break;
            case 'edu':
                $result = M('User')->alias('u')
                    ->join('ym_school s ON u.edu = s.id', 'left')
                    ->field('u.edu as edu_id, s.name, COUNT(u.guid) as num')
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE', '', 10))
                    ->group('u.edu')
                    ->select();
                break;
            case 'interest':
                // 获取
                $has_interest_user_guids = M('UserInterest')
                    ->where(array('user_guid' => array('IN', $two_guids)))
                    ->group('user_guid')
                    ->getField('user_guid', true);
                $result = M('User')->alias('u')
                    ->join('ym_user_interest ui ON ui.user_guid = u.guid', 'left')
                    ->join('ym_interest i ON ui.interest_id = i.id')
                    ->field('ui.interest_id, i.name, COUNT(ui.user_guid) as num')
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE', '', 10))
                    ->group('ui.interest_id')
                    ->select();
                // 当页数为第一页时, 增加为选择兴趣用户统计
                if(I('get.p', 1) == 1){
                    // 获取没有选择兴趣的用户guid
                    $no_interest_user_guids = array_diff($two_guids, $has_interest_user_guids);
                    $count_no_interest = count($no_interest_user_guids);
                    if($count_no_interest > 0) {
                        $nul_interest_stat = array('interest_id' => null, 'name' => null, 'num' => $count_no_interest);
                        array_unshift($result, $nul_interest_stat);
                    }
                }
                break;
            case 'area':
                $area_field = I('get.area_field', 'areaid_1');
                $parent_area_id = I('get.parent_id');

                // 若客户端传来为其它， 即没有地区， 则直接跳转二度人脉列表
                if($parent_area_id == 'null'){
                    $this->get_friend_two_by_attr($area_field, $parent_area_id);
                }

                // 获取同行业统计
                if (!empty($parent_area_id) && is_numeric($parent_area_id)) {
                    $where['a.parent_id'] = $parent_area_id;
                }
                $result = M('User')->alias('u')
                    ->join('ym_area a ON a.id = u.' . $area_field, 'left')
                    ->field("u.$area_field, a.name, COUNT(u.guid) as num")
                    ->where($where)
                    ->page(I('get.p', '1') . ',' . C('NUM_PER_PAGE', '', 10))
                    ->group('u.'.$area_field)
                    ->select()
                ;
                break;
            default:
                $this->output_error('10003');
                break;
        }

        if (empty($result)) {
            $this->output_error('10009');
        } else {
            $this->output_data($result);
        }
    }

    /**
     * 云友的人脉 – 列表：field =  行业industry,  教育edu， 兴趣interest,  地区area
     *
     * CT: 2015-01-21 12:20 BY YLX
     */
    public function friend_two_list() {
        $this->check_request_method('get');
        $field = $this->_request_params['field'];
        if(empty($field)) {
            $this->output_error('10003');
        }

        switch($field) {
            case 'industry':
                $industry_guid = I('get.industry_guid');
                $this->get_friend_two_by_attr('main_industry_guid', $industry_guid);
                break;
            case 'edu':
                $edu_id = I('get.edu_id');
                $this->get_friend_two_by_attr('edu', $edu_id);
                break;
            case 'area':
                $area_id = I('get.area_id');
                $field = I('get.area_field');
                if(empty($field)) {
                    $this->output_error('10003');
                }
                $this->get_friend_two_by_attr($field, $area_id);
                break;
            case 'interest':
                $interest_id = I('get.interest_id');
                $mid = $this->user_info['guid'];
                if (empty($interest_id)) $this->output_error('10003');

                // 获取所有一度人脉的好友guid
                $two_guids = $this->get_user_friend_two_guid_list($mid);
                $num_per_page = C('NUM_PER_PAGE', null, 10);

                if(empty($interest_id) ||  $interest_id == 'null'){
                    $has_interest_user_guids = M('UserInterest')->where(array('user_guid' => array('IN', $two_guids)))->group('user_guid')->getField('user_guid', true);
                    $no_interest_user_guids = array_diff($two_guids, $has_interest_user_guids);
                    $res = M('User')->field('guid, real_name, photo_'.C('API_PHOTO.user_list', null, 120).' as photo, remark, edu, home_areaid_2, main_industry_guid')
                        ->where(array('guid' => array('in', $no_interest_user_guids)))
                        ->page(I('get.p', 1), $num_per_page)
                        ->select();
                } else {
                    $where = array(
                        'u.guid'         => array(array('IN', $two_guids), array('NEQ', $mid)),
                        'ui.interest_id' => $interest_id,
                        'u.is_active'    => '1',
                        'u.type'         => '1',
                        'u.is_del'       => '0'
                    );
                    $res   = M('User')->alias('u')
                        ->join('ym_user_interest ui ON ui.user_guid = u.guid', 'left')
                        ->field('u.guid, u.real_name, u.photo_'.C('API_PHOTO.user_list', null, 120).' as photo, u.remark, u.edu, u.home_areaid_2, u.main_industry_guid')
                        ->where($where)
                        ->page(I('get.p', '1') . ',' . $num_per_page)
                        ->group('u.guid')
                        ->select();
                }
                // 计算当前用户与二度人脉的相关性
                $res = $this->process_pepole($res);

                //RTH
                $res = $this->_check_apply_status($res, $mid, $two_guids);
                //RTH
                if (empty($res)) $this->output_error('10009');
                else $this->output_data($res);
                break;
            default:
                break;
        }
    }

    /**
     *  获取参加相同活动下所有非好友的用户guid列表
     * @param $user_guid
     * @param $one  是否只获得最新活动的用户的guids
	 * @param $activity_id 指定活动的ID
     * @return array|bool
     */
    private function get_user_guids_in_same_activity($user_guid, $one = false, $activity_id = '')
    {
        $activity_type = C('ACTIVITY_TYPE')['ACTIVITY_SIGNUP'];
		if(!$activity_id){
			// 获取当前时间段所有活动GUID
			$aids = D('Activity')->getCurrentActivity($activity_type, 'guid', true);
			if(empty($aids)) {
				return false;
			}
			$aids = D('ActivityUserTicket')->getActivityGuidsByUserGuid($aids, $user_guid);
			if($one){
				$aids = array_slice($aids, 0, 1);
			}
		}else{
			$aids = array($activity_id);
		}
		// 获取参加当前活动的用户guid列表
		$two = D('ActivityUserTicket')->getUserGuidsByActivity($aids);
		if(empty($two)) {
			return false;
		}
        // 获取一度人脉GUID
        $one = $this->get_user_one_guid_list($user_guid);
        $one[] = $user_guid;

        // 去重获取同参加活动的二度人脉guid
        $user_guids = array_diff($two, $one);

        return !empty($user_guids)?$user_guids:array('ym365');
    }

	/**
	 * 客户端上传通讯录
	 *
	 * @access public
	 * @param  void
	 * @return json
	 * @author wangleiming<wangleiming@yunmai365.com>
	 **/

    public function contacts_list(){
        $this->check_request_method('post');
        $user_guid = $this->user_info['guid'];
        $mobiles   = I('post.contacts');
        $mobiles   = api_json_explode($mobiles);
        if(!(isset($mobiles, $mobiles['contacts']) && $mobiles['contacts'])){
            $this->output_error('10003');
        }
        $time = time();
        $save = array(
            'user_guid'  => $user_guid,
            'created_at' => $time,
            'updated_at' => $time,
        );
        foreach($mobiles['contacts'] as $key => $mobile){
            if(!(isset($mobile['phone']) && $mobile['phone'])){
                continue;
            }
            $save['contact_mobile'] = str_replace(' ', '', trim($mobile['phone']));
			$save['contact_mobile'] = Crypt::encrypt($save['contact_mobile'], C('CONTACT_MOBILE_ENCRYPT_KEY'));
            $save['contact_name']   = isset($mobile['name']) && $mobile['name'] ? trim($mobile['name']) : '';
            $save['guid']		    = create_guid();
            $cond  = array('user_guid' => $user_guid, 'contact_mobile' => $save['contact_mobile']);
			$exist = D('UserContactList')->where($cond)->find();
            if(!$exist){
                D('UserContactList')->data($save)->add();
            }
        }
        $this->output_data();
    }

	/**
	 * 通讯录中的二度人脉
	 *
	 * @access public
	 * @param  void
	 * @return json
	 * @author wangleiming<wangleiming@yunmai365.com>
	 **/

    public function contacts_two(){
        $this->check_request_method('get');
        $user_guid = $this->user_info['guid'];
        $ut        = I('get.ut');
        $cond = array(
            'user_guid' => $user_guid,
            'is_del'    => 0,
        );
        if($ut){
            $cond['updated_at'] = array('gt', $ut);
        }
        $res   = D('UserContactList')->where($cond)->order('updated_at desc')->getField('contact_mobile, contact_name, updated_at');
        $data  = $tmp1 = $tmp2 = array();
		$ut    = 0;
        if($res){
			foreach($res as $key => $value){
				$tmp = Crypt::decrypt($key, C('CONTACT_MOBILE_ENCRYPT_KEY'));
				$value['contact_mobile'] = $tmp;
				$res[$tmp] = $value;
				unset($res[$key]);
			}
            $two = $this->get_user_guids_in_same_org($user_guid);
            $one = $this->get_user_one_guid_list($user_guid);
            if($two || $one){
                $same = array();
                $two = D('User')->where(array('guid' => array('in', $two)))->select();
                $one = D('User')->where(array('guid' => array('in', $one)))->select();
                if($two){
                    $tmp1 = $this->userDetail($two, $res, 0);
                }
                if($one){
                    $tmp2 = $this->userDetail($one, $res, 1);
                }
            }
			$data = array_merge($tmp1, $tmp2);
			$ut   = current($res)['updated_at'];
        }
        $this->output_data(array('total' => count($data), 'res' => $data, 'ut' => $ut));
    }

	/**
	 * 根据guid获得指定用户的全部报名活动
	 *
	 * @acess public
	 * @param void
	 * @return json
	 **/

	public function activity_signup_list(){
        $this->check_request_method('get');
        $user_guid = $this->user_info['guid'];
		$aids = D('ActivityUserTicket')->where(array('user_guid' => $user_guid, 'status' => 4))->getField('activity_guid', true);

		if($aids){
			$acvitity = D('Activity')->where(array('guid' => array('in', $aids), 'is_del' => 0))->getField('guid, name', true);
			$data = $tmp = array();
			foreach($acvitity as $key => $value){
				$tmp['id'] = $key;
				$tmp['name'] = $value;
				$data[] = $tmp;
			}
        	$this->output_data(array('total' => count($data), 'res' => $data));
		}
        $this->output_error('10009');
	}

	/**
	 * 格式化&检索返回数据
	 *
	 * @access private
	 * @param  array   $data    原始数据(通讯录数据)
	 * @param  array   $compare 社团数据(一度人脉|社团成员)
	 * @param  int     $status  0 不是一度人脉 1 是一度人脉
	 * @return array
	 * @author wangleiming<wangleiming@yunmai365.com>
	 **/

    private function userDetail($data, $compare, $status = 0){
        $same = array();
        foreach($data as $key => $user){
            if(isset($compare[$user['mobile']])){
        		$mid = $this->user_info['guid'];
				if($status == 0){
					//已申请但还未通过
					$where_a2b = array(
						'user_guid_1' => $mid,
						'user_guid_2' => $user['guid'],
					);
					$a2b = D('Contacts')->where($where_a2b)->getField('status');
					$status = $a2b == 1 ? 2 : 0;
				}
                $same[$user['mobile']] = array(
                    'contact_name' => $compare[$user['mobile']]['contact_name'],
                    'photo'        => $user['photo_'.C('API_PHOTO.user_info', null, 240).''],
                    'guid'         => $user['guid'],
                    'real_name'    => $user['real_name'],
                    'status'       => $status,
                );
            }
        }
        return $same;
    }

}
