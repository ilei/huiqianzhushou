<?php 
namespace  Common\Logic;

class ActLogic{

    public $errors = array();

    public function get_act_info($activity_guid, $user_guid = false){
        if(!$activity_guid){
            return false;
        } 
        $cond = array('guid' => $activity_guid);
        if($user_guid){
            $cond['user_guid'] = $user_guid;
        }
        $activity     = D('Activity')->where($cond)->find();
        $tickets      = M('ActivityAttrTicket')->where(array('activity_guid' => $activity_guid))->select(); 
        $build_info   = D('ActivityForm')->where(array('activity_guid' => $activity_guid))->order('id asc')->select();
        $option_info  = D('ActivityFormOption')->where(array('activity_guid' => $activity_guid))->field('guid,build_guid,value')->select();
        foreach($option_info as $key => $value){
            unset($option_info[$key]);
            $option_info[$value['build_guid']][] = $value; 
        }
        $flows        = M('ActivityAttrFlow')->where(array('activity_guid' => $activity_guid))->select();

        $undertakers  = M('ActivityAttrUndertaker')->where(array('activity_guid' => $activity_guid))->select();
        return array($activity, $tickets, $build_info, $option_info, $flows, $undertakers); 
    }

    public function save_undertaker($undertakers, $activity_guid, $user_guid){

        if(!$undertakers || !$activity_guid){
            return false;
        }
        $time = time();
        M('ActivityAttrUndertaker')->where(array('activity_guid' => trim($activity_guid)))->delete();
        $data_undertakers = array();
        foreach ($undertakers as $k => $u) {
            if(!$u['name']){
                continue;
            }
            if(strstr($u['name'],',')){
                $u['name'] = array_filter(explode(',', $u['name']));
            }elseif(strstr($u['name'],'，')){
                $u['name'] = array_filter(explode('，', $u['name']));
            }elseif(strstr($u['name'],';')){
                $u['name'] = array_filter(explode(';', $u['name']));
            }elseif(strstr($u['name'],'；')){
                $u['name'] = array_filter(explode('；', $u['name']));
            }
            $common = array(
                'activity_guid' => $activity_guid,
                'created_at'    => $time,
                'updated_at'    => $time,
            );
            $u['name'] = !is_array($u['name']) ? array($u['name']) : $u['name'];
            if(!$k){
                $u['guid'] = array_filter(explode(',', $u['guid']));
                foreach($u['name'] as $index => $value){
                    $organizer[] = array_merge($common, array(
                        'guid' => create_guid(),
                        'type' => 1, 
                        'name' => trim($value),
                        'organizer_guid' => $u['guid'] ? $u['guid'][$index] : '',
                    ));  
                }
                if($organizer){
                    M('ActivityAttrUndertaker')->addAll($organizer);
                }
            }else{
                if(!$u['type']){
                    continue; 
                }
                if(!(isset($u['guid']) && $u['guid'])){
                    $exist = M('UserPartnerCategory')->where(array('user_guid' => $user_guid, 'name' => trim($u['type'])))->find();
                    if($exist){
                        $partner_guid = $exist['guid'];
                    }else{
                        $save = array(
                            'guid' => create_guid(),
                            'name' => $u['type'],
                            'user_guid' => $user_guid,
                            'created_at' => $time, 
                            'updated_at' => $time, 
                        );
                        M('UserPartnerCategory')->add($save);
                        $partner_guid = $save['guid'];
                    }

                }else{
                    $partner_guid = trim($u['guid']); 
                }
                $data_undertakers[] = array_merge($common, array(
                    'guid' => create_guid(),
                    'name' => is_array($u['name']) ? implode(',', $u['name']) : $u['name'],
                    'partner_guid' => $partner_guid, 
                    'type' => trim($u['type']),
                ));
            }
        }
        if($data_undertakers){
            $res = M('ActivityAttrUndertaker')->addAll($data_undertakers);
            if($res){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }

    public function save_flow($flows, $activity_guid){
        if(!$flows || !$activity_guid){
            return false;
        } 
        $time = time();
        $data_flow = array();
        M('ActivityAttrFlow')->where(array('activity_guid' => trim($activity_guid)))->delete();
        foreach ($flows as $k => $f) {
            if(!$f['title']){
                continue;
            }
            $data_flow[] = array(
                'guid'          => create_guid(),
                'activity_guid' => $activity_guid,
                'title'         => $f['title'],
                'content'       => '',
                'start_time'    => !empty($f['start_time']) ? strtotime($f['start_time']) : '',
                'end_time'      => !empty($f['end_time']) ? strtotime($f['end_time']) : '',
                'created_at'    => $time,
                'updated_at'    => $time
            );
        }
        if($data_flow){
            $res = M('ActivityAttrFlow')->addAll($data_flow);
            if($res){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }

    public function save_form($forms, $activity_guid){
        if(!$forms || !$activity_guid){
            return false;
        }
        $old_res =  D('ActivityForm')->where(array('activity_guid' => $activity_guid,'name'=>array('IN',array('姓名','手机','邮箱','公司','职位','地址','性别','年龄'))))->select();
        $time = time();
        D('ActivityForm')->where(array('activity_guid' => $activity_guid,'name'=>array('IN',array('姓名','手机','邮箱','公司','职位','地址','性别','年龄'))))->delete();
        foreach ($old_res as $key => $value) {
            if($value[ym_type]=='sex'){
                D('ActivityFormOption')->where(array('activity_guid' => $activity_guid,'build_guid'=>$value['guid'],'value'=>array('IN',array('男','女'))))->delete();
            }
        }
        $data_build   = array();
        $data_build[] = array( // 姓名
            'guid'          => create_guid(),
            'activity_guid' => $activity_guid,
            'name'          => L('_ACT_FORM_REALNAME_'),
            'note'          => L('_ACT_FORM_REALNAME_NOTE_'),
            'ym_type'       => 'real_name',
            'html_type'     => 'text',
            'is_required'   => 1,
            'is_info'       => 1,
            'created_at'    => $time,
            'updated_at'    => $time,
            'sort'          => isset($old_res[0]['sort']) ? intval($old_res[0]['sort']) : 1,
        );
        $data_build[] = array( //手机
            'guid'          => create_guid(),
            'activity_guid' => $activity_guid,
            'name'          => L('_ACT_FORM_MOBILE_'),
            'note'          => L('_ACT_FORM_MOBILE_NOTE_'),
            'ym_type'       => 'mobile',
            'html_type'     => 'text',
            'is_required'   => 1,
            'is_info'       => 1,
            'created_at'    => $time,
            'updated_at'    => $time,
            'sort'          => isset($old_res[1]['sort']) ? intval($old_res[1]['sort']) : 1,
        );
        $old_res = kookeg_array_column($old_res, 'sort', 'name');
        $data_options = array();
        foreach ($forms as $i) {
            if (isset($i['name'])) {
                $buid_guid    = create_guid();
                $data_build[] = array(
                    'guid'          => $buid_guid,
                    'activity_guid' => $activity_guid,
                    'name'          => $i['name'],
                    'note'          => '请输入' . $i['name'],
                    'ym_type'       => $i['type'],
                    'html_type'     => $i['type'] == 'sex' ? 'radio' : 'text',
                    'is_required'   => isset($i['is_required']) ? intval($i['is_required']) : 0,
                    'is_info'       => 0,
                    'created_at'    => $time,
                    'updated_at'    => $time,
                    'sort'          => isset($old_res[$i['name']]) ? intval($old_res[$i['name']]) : 1,
                );
                if($i['type'] == 'sex'){
                    $i['options'] = array('男', '女'); 
                }
                if (!empty($i['options'])) {
                    foreach ($i['options'] as $o) {
                        $data_options[] = array(
                            'guid'          => create_guid(),
                            'activity_guid' => $activity_guid,
                            'build_guid'    => $buid_guid,
                            'value'         => $o,
                            'created_at'    => $time,
                            'updated_at'    => $time
                        );
                    }
                }
            }
        }
        if (!empty($data_build)) {
            M('ActivityForm')->addAll($data_build);
        }
        if (!empty($data_options)) {
            $res = M('ActivityFormOption')->addAll($data_options);
            if($res){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }

    public function save_ticket($tickets, $activity_guid, $data_activity, $user_guid = '', $delete = true){
        if(!$tickets || !$activity_guid || !$data_activity){
            return false;
        }
        $time = time();
        $data_ticket = $goods = array();
        if (!empty($tickets['old'])) {
            $old_guid = array();
            foreach ($tickets['old'] as $t) {
                array_push($old_guid, $t['guid']);
                if(!$t['name']){
                    continue;
                }
                $start_time = $t['start_time'] ? strtotime($t['start_time']) : ($data_activity['published_at'] ? $data_activity['published_at'] : time());
                $tmp = array(
                    'name'          => mb_substr($t['name'], 0, 10, 'utf-8'),
                    'num'           => intval($t['num']),
                    'start_time'    => $start_time,
                    'end_time'      => $t['end_time'] ? strtotime($t['end_time']) : $data_activity['end_time'],
                    'verify_num'    => 50,
                    'is_for_sale'   => 1,
                    'price'         => isset($t['price']) && is_numeric($t['price']) ? yuan_to_fen($t['price']) : 0,   
                    'is_need_verify'=> isset($t['is_need_verify']) && intval($t['is_need_verify']) ? '1' : '0',
                    'updated_at'    => $time,
                );
                if(isset($t['price']) && is_numeric($t['price']) ? yuan_to_fen($t['price']) : 0 != 0){
                    $tmp['is_free'] = 0;//0为收费票
                }
                M('ActivityAttrTicket')->where(array('guid' => $t['guid']))->save($tmp);
                $good = array(
                    'name'        => $tmp['name'],
                    'price'       => $tmp['price'], 
                    'storage'     => intval($t['num']),
                    'is_need_verify'=> $tmp['is_need_verify'], 
                    'start_time'    => $tmp['start_time'], 
                    'end_time'      => $tmp['end_time'],
                    'updated_time'  => $time,
                );
                D('Goods', 'Logic')->update_goods($good, array('ticket_guid' => $t['guid']));
            }
            if($old_guid && $delete){
                M('ActivityAttrTicket')->where(array('guid' => array('not in', $old_guid), 'activity_guid' => $activity_guid))->delete();
            }
        }elseif($delete){
            M('ActivityAttrTicket')->where(array('activity_guid' => $activity_guid))->delete();
        }
        if(!empty($tickets['new'])){
            foreach($tickets['new'] as $k => $t) {
                if(!$t['name']){
                    continue;
                }
                $start_time = $t['start_time'] ? strtotime($t['start_time']) : ($data_activity['published_at'] ? $data_activity['published_at'] : time());
                $exist = M('ActivityAttrTicket')->where(array('activity_guid' => $activity_guid, 'name' => $t['name']))->find();
                if($exist){
                    continue;
                }

                if(isset($t['price']) && is_numeric($t['price']) ? yuan_to_fen($t['price']) : 0 > 0){
                    $is_free = 0;//0为收费票
                }else{
                    $is_free = 1;
                }
                $tmp_guid = create_guid();
                $data_ticket[] = array(
                    'guid'          => $tmp_guid,
                    'activity_guid' => $activity_guid,
                    'name'          => $t['name'],
                    'num'           => $t['num'],
                    'start_time'    => $start_time, 
                    'end_time'      => $t['end_time'] ? strtotime($t['end_time']) : $data_activity['end_time'],
                    'verify_num' => 50,
                    'is_for_sale'   => 1,
                    'is_free'   => $is_free,
                    'price'         => isset($t['price']) && is_numeric($t['price']) ? yuan_to_fen($t['price']) : 0,
                    'is_need_verify'=> isset($t['is_need_verify']) && intval($t['is_need_verify']) ? '1' : '0',
                    'created_at'    => $time,
                    'updated_at'    => $time
                );
                $goods[] = array(
                    'name'        => $t['name'],
                    'seller_guid' => $user_guid,
                    'target_guid' => $activity_guid,	
                    'price'       => isset($t['price']) && is_numeric($t['price']) ? yuan_to_fen($t['price']) : 0, 
                    'ticket_guid' => $tmp_guid,
                    'storage'     => intval($t['num']),
                    'is_need_verify'=> isset($t['is_need_verify']) && intval($t['is_need_verify']) ? '1' : '0',
                    'start_time'    => $start_time,
                    'end_time'      => $t['end_time'] ? strtotime($t['end_time']) : $data_activity['end_time'],
                    'created_time'  => $time,
                    'updated_time'  => $time,
                );
            }
        }
        $r = D('ActivityAttrTicket')->addAll($data_ticket);
        if($r){
            $logic = D('Goods', 'Logic');
            $res = $logic->add_arr($goods);
            if($res){
                return true;
            }else{
                return false;
            }
        }
        return true;
    }

    public function copy($activity_guid, $user_guid = ''){
        if(!$activity_guid){
            return false;
        }
        $time = time();
        $all_info = $this->get_act_info($activity_guid, $user_guid);
        list($activity, $tickets, $build_info, $option_info, $flows, $undertakers) = $all_info; 
        unset($activity['id']);
        $activity['guid'] = create_guid();
        $activity['created_at'] = $time; 
        $activity['updated_at'] = $time; 
        $activity['status']     = C('default_ok_status');
        $activity['is_del']     = 0;
        $activity['is_verify']  = 0;
        $activity['start_time'] = $time;
        $activity['end_time']   = $time + 7*24*3600;
        $activity['verify_reason']  = '';
        $activity['refuse_reason']  = '';
        M('Activity')->data($activity)->add();
        if($tickets){
            foreach($tickets as $key => &$value){
                unset($value['id']);
                $value['guid'] = create_guid();
                $value['created_at'] = $time;
                $value['updated_at'] = $time;
                $value['start_time'] = $activity['start_time'];
                $value['end_time']   = $activity['end_time'];
                $value['activity_guid'] = $activity['guid'];
            }
            M('ActivityAttrTicket')->addAll($tickets);
        }
        $option_info  = D('ActivityFormOption')->where(array('activity_guid' => $activity_guid))->select();
        if($build_info){
            foreach($build_info as $key => &$value){
                unset($value['id']);
                $tmp_guid      = create_guid();
                foreach($option_info as $k => $v){
                    if($value['guid'] == $v['build_guid']){
                        unset($option_info[$k]['id']);
                        $option_info[$k]['guid'] = create_guid(); 
                        $option_info[$k]['build_guid'] = $tmp_guid;
                        $option_info[$k]['created_at'] = $time;
                        $option_info[$k]['updated_at'] = $time;
                        $option_info[$k]['activity_guid'] = $activity['guid'];
                    }     
                }
                $value['guid'] = $tmp_guid;
                $value['created_at'] = $time;
                $value['updated_at'] = $time;
                $value['activity_guid'] = $activity['guid'];
            } 
            M('ActivityForm')->addAll($build_info);
            M('ActivityFormOption')->addAll($option_info);
        }
        if($flows){
            foreach($flows as $key => &$value){
                $tmp = $time + $key*24*3600;
                unset($value['id']); 
                $value['guid'] = create_guid();
                $value['created_at'] = $time;
                $value['updated_at'] = $time;
                $value['start_time'] = $tmp;
                $value['end_time'] = $tmp+24*3600;
                $value['activity_guid'] = $activity['guid'];
            }
            M('ActivityAttrFlow')->addAll($flows); 
        }
        if($undertakers){
            foreach($undertakers as $key => &$value){
                unset($value['id']); 
                $value['guid'] = create_guid();
                $value['created_at'] = $time;
                $value['updated_at'] = $time;
                $value['activity_guid'] = $activity['guid'];
            } 
            M('ActivityAttrUndertaker')->addAll($undertakers);
        }
        return $activity['guid'];
    }


    /**
     * 检查报名时间
     * @param $guid activty_guid
     * @param $act_start_time activity_start_time
     * @param $act_end_time activity_end_time
     * @return array [status]  [type]
     */

    public function check_signup_time($guid,$act_start_time,$act_end_time)
    {
        // 判断报名是否开始
        $time = time();
        $signup_status=array();
        $ticket = M('ActivityAttrTicket')->where(array('activity_guid' => $guid))->select();
        $start  = min(kookeg_array_column($ticket, 'start_time', 'id'));
        $end    = max(kookeg_array_column($ticket, 'end_time', 'id'));
        if(!$start || !$end){
            $start = $act_start_time;
            $end   = $act_end_time;
        }
        if($time < $start || $time > $end){
            $signup_status['status'] = false;
            if ($time < $start) {
                $signup_status['time_type'] = 'start';
            } else {
                $signup_status['time_type'] = 'end';
            }
            return $signup_status;
        } else {
            $signup_status['status'] = true;
            return $signup_status;
        }
    }

}
