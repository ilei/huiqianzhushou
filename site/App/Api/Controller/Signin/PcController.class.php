<?php
namespace Api\Controller\Signin;

use Api\Controller\BaseUserController;
use Think\Upload;

/**
 * 离线签到客户端接口控制器
 * Class PcController
 * @package Api\Controller\Signin
 * ct: 2015-07-15 12:06 by ylx
 */
class PcController extends BaseUserController
{
    /**
     * 获取用户信息及签到帐号信息
     * ct: 2015-07-15 12:06 by ylx
     */
    public function get_user_info()
    {
        $this->check_request_method('get');
//        $user_guid = $this->user_info['user_guid'];
        if($this->user_info['user_guid']){
            $user_guid       = $this->user_info['user_guid'];
        }else{
            $user_guid       = $this->user_info['guid'];
        }
        $user_info = M('User')->field('guid,email,vip,updated_at,password')->where(array('guid' => $user_guid))->find();

        $signin_user = M('SigninUser')
            ->field('guid,username,password,user_guid,updated_at')
            ->where(array('user_guid' => $user_guid, 'is_active' => 1, 'is_del' => 0))
            ->select();

        if (empty($user_info) || empty($signin_user)) {
            $this->output_error('10009', 'no data');
        }

        $this->output_data(array('user' => $user_info, 'signin_user' => $signin_user));
    }

    /*
     * 获取社团活动详情
     * ct：2015-08-20 18:00 by rth
     */
    public function get_activity_list(){
        $this->check_request_method('get');
        if($this->user_info['user_guid']){
            $user_guid = $this->user_info['user_guid'];
        }else{
            $user_guid = $this->user_info['guid'];
        }
        $ut = I('get.ut',0);
        $activity_list = M('Activity')
            ->field('guid,name,start_time,end_time,published_at,updated_at, areaid_1_name ,areaid_2_name, address')
            ->where(array('user_guid' => $user_guid,'updated_at' => array('EGT',$ut),'status' => array('in',array(1)),'is_verify' => 1))
            ->order('created_at DESC')
            ->select();
        foreach($activity_list as $k=>$v){
            $activity_list[$k]['address'] = $v['areaid_1_name'].$v['areaid_2_name'].$v['address'];
        }
        //返回两次UT时间内关闭活动的guid
        $activity_close_guids = M('Activity')
            ->field('guid')
            ->where(array('user_guid' => $user_guid,'updated_at' => array('EGT',$ut),'status' => '3','is_verify' => 1))//已关闭
            ->select();
        foreach($activity_close_guids as $k=>$v){
            $close_guids[] = $v['guid'];
        }
        if($close_guids){
            $this->output_data(array('ut' => time(),'file' => $activity_list,'close_guids' => $close_guids));
        }
        if(empty($activity_list)){
            $this->output_error('10009');
        }else{
            $this->output_data(array('ut' => time(),'file' => $activity_list,'close_guids' => $close_guids));
        }
    }

    /*
     * 获取报名和电子票信息
     *  ct：2015-08-20 18:00 by rth
     */
    public function get_signin_user_ticket_info(){
        $this->check_request_method('post');
        $ut = $this->_request_params['ut'];
        $activity_guids = $this->_request_params['aid'];
        $activity_guids = json_decode(html_entity_decode($activity_guids),true);

        $user_where = array('i.activity_guid' => array('in',$activity_guids), 'i.is_del' => 0,
                            't.status' => array('GT',1),
                            't.updated_at' => array('gt', $ut)
        );

        $list_user = M('ActivityUserinfo')->alias('i')
            ->join('ym_activity_user_ticket t on t.activity_guid = i.activity_guid and t.user_guid = i.user_guid')
            ->field('i.guid as guid, i.real_name as name, i.mobile, i.activity_guid,
                        t.guid as ticket_guid, t.ticket_name, t.ticket_code, t.signin_status as status, t.updated_at, t.is_del')
            ->where($user_where)
            ->order('i.created_at DESC')
            ->select();

        foreach ($list_user as $ku => $lu) {
            // 来源
//            $from       = C('ACTIVITY_SIGNUP_FROM');
//            $lu['from'] = $from[$lu['type']];
//            unset($list_user[$ku]['type']);

            // 其它信息
            $other = M('ActivityUserinfoOther')
                ->field('key, value, ym_type')
                ->where(array('userinfo_guid' => $lu['guid'], 'is_del' => '0'))
                ->order('id asc')->select();

            foreach ($other as $other_k => $o) {
                if ($o['ym_type'] == 'email' && empty($lu['email'])) {
                    $lu['email'] = $o['value'];
                }
                unset($other[$other_k]['ym_type']);
                $vals = explode('_____', $o['value']);
                if (count($vals) <= 1) {
                    $v_str = $o['value'];
                } else {
                    $v_str = implode(', ', $vals);
                }
                $other[$other_k]['value'] = $v_str;
            }
            $lu['other'] = $other;

            $list_user_all[] = $lu;
        }
        $this->output_data(array('ut' => time(),'file' => array_values($list_user_all)));
    }
    /**
     * 下载活动及票务信息
     * ct: 2015-07-15 12:06 by ylx
     */
    public function down()
    {
        $this->check_request_method('get');
        $ut = I('get.ut', 0);

        $time = time();
//        $user_guid       = $this->user_info['user_guid'];
        if($this->user_info['user_guid']){
            $user_guid       = $this->user_info['user_guid'];
        }else{
            $user_guid       = $this->user_info['guid'];
        }

        // 获取活动数据
        $model_activity = M('Activity');
        $where = array('user_guid' => $user_guid, 'is_del' => 0,'is_verify' => 1);
        if($ut == 0) {
            $where['status'] = 1;//如果ut等于0，返回全部进行中的活动
        } else if($ut > 0) {//如果ut大于零则返回全部活动
            $where['status'] = array('in', array(1, 2, 3));
            $where['updated_at'] = array('gt', $ut);
            $where['created_at'] = array('lt', $ut);
        }
        $list_activity  = $model_activity
            ->field('guid, name, start_time, end_time, published_at, updated_at, status')
            ->where($where)
            ->order('start_time ASC')
            ->select();

        if($ut == 0) {
            $list_activity_all = array('list_c' => $list_activity, 'list_u' => null, 'list_d' => null);
        } else if($ut > 0) {
            foreach($list_activity as $ak => $a) {
                switch($a['status']) {
                    case 1:
                        $list_activity_u[] = $a;
                        break;
                    case 2:
                    case 3:
                    $list_activity_d[] = $a['guid'];
                    break;
                }
            }
            $list_activity_c = $model_activity
                ->field('guid, name, start_time, end_time, published_at, updated_at, status')
                ->where(array('user_guid' => $user_guid, 'is_del' => 0, 'status' => 1, 'created_at' => array('egt', $ut)))
                ->order('start_time ASC')
                ->select();
            $list_activity_all = array(
                'list_c' => empty($list_activity_c) ? null : $list_activity_c,
                'list_u' => empty($list_activity_u) ? null : $list_activity_u,
                'list_d' => empty($list_activity_d) ? null : $list_activity_d
            );
            $list_activity = array_merge($list_activity_c, $list_activity_u);
        }

        // 获取用户票务数据
        $list_user_all = array();
        foreach ($list_activity as $k => $l) {
            $user_where = array('i.activity_guid' => $l['guid'], 'i.is_del' => 0,
                                't.status' => array('in', array(2, 3, 4)),
                                'i.updated_at' => array('gt', $ut)
                                );
            $list_user = M('ActivityUserinfo')->alias('i')
                ->join('ym_activity_user_ticket t on t.activity_guid = i.activity_guid and t.user_guid = i.user_guid')
                ->field('i.guid as guid, i.real_name as name, i.mobile, i.activity_guid,
                        t.guid as ticket_guid, t.ticket_name, t.ticket_code, i.type, t.signin_status as status, t.updated_at')
                ->where($user_where)
                ->order('i.created_at DESC')
                ->select();

            foreach ($list_user as $ku => $lu) {
                // 来源
                $from       = C('ACTIVITY_SIGNUP_FROM');
                $lu['from'] = $from[$lu['type']];
                unset($list_user[$ku]['type']);

                // 其它信息
                $other = M('ActivityUserinfoOther')
                    ->field('key, value, ym_type')
                    ->where(array('signup_userinfo_guid' => $lu['guid'], 'is_del' => '0'))
                    ->order('id asc')->select();

                foreach ($other as $other_k => $o) {
                    if ($o['ym_type'] == 'email' && empty($lu['email'])) {
                        $lu['email'] = $o['value'];
                    }
                    unset($other[$other_k]['ym_type']);
                    $vals = explode('_____', $o['value']);
                    if (count($vals) <= 1) {
                        $v_str = $o['value'];
                    } else {
                        $v_str = implode(', ', $vals);
                    }
                    $other[$other_k]['value'] = $v_str;
                }
                $lu['other'] = $other;

                $list_user_all[] = $lu;
            }
        }

        // 生成json文件
        $arr = array('list_activity' => $list_activity_all, 'list_user' => $list_user_all);

//        // 存储下载内容到mongodb
//        if($this->user_info['user_guid']){
//            $data = array(
//                'user_guid' => $this->user_info['guid'],
//                'org_guid'  => $user_guid,
//                'content'   => json_encode($arr),
//            );
//        }else{
//            $data = array(
//                'user_guid' => null,
//                'org_guid'  => $this->user_info['guid'],
//                'content'   => json_encode($arr),
//            );
//        }
//        D('PcSigninLog')->addDownLog($data);

        $this->output_data(array('ut' => $time, 'file' => $arr));
//        // 生成json文件
//        $arr          = array('list_activity' => $list_activity_all, 'list_user' => $list_user_all);
//        $file_content = json_encode($arr);
//        $file_path    = UPLOAD_PATH . '/signin/' . $user_guid.'/down';
//        $file_name    = date('YmdHis', $time) . '.json';
//        if (!is_dir($file_path)) {
//            mkdir($file_path, '0777');
//        }
//        if (file_put_contents($file_path . '/' . $file_name, $file_content)) {
//            $this->output_data(array('ut' => $time, 'file' => C('SITE_HOST_URL', null, 'http://www.shetuanbang.net').'/Upload/signin/'.$user_guid.'/down/'.$file_name));
//        } else {
//            $this->output_error('10011');
//        }
    }

    /**
     * 上传票务更新信息
     * ct: 2015-07-16 10:06 by ylx
     * ut: 2015-07-16 16:06 by ylx
     */
    public function up()
    {
        $this->check_request_method('post');

        $time     = time();
//        $org_guid = $this->user_info['org_guid'];
        $json = I('post.file');

        $arr  = api_json_explode($json, true);
        if(empty($arr)) {
            $this->output_error('10003', 'no data upload');
        }
        foreach ($arr['ticket'] as $tid => $status) {
            if(M('ActivityUserTicket')->where(array('guid' => $tid,'is_del' => '0'))->find()){
                if ($status > 0) {
                    $res = M('ActivityUserTicket')->where(array('guid' => $tid))
                        ->save(array('status' => '4', 'signin_status' => $status));
                    if (!$res) {
                        $this->output_error('10012', 'please upload again');
                    }
                    M('MsgContent')->where(array('ticket_guid' => $user_ticket_guid))->save(array('status' => 4));
                }
            }else{
                continue;
            }
        }

        $this->output_data(array('ut' => $time));
    }

    /**
     * 验证密码
     * ct: 2015.08.07 16:00 by ylx
     */
    public function ckeckPwd()
    {
        $this->check_request_method('post');
        $pwd = I('post.pwd');
        if(empty($pwd)) {
            $this->output_error('10003', 'param error');
        }

//        $guid = $this->user_info['guid'];
        if($this->user_info['user_guid']){
            $guid       = $this->user_info['guid'];
            $upwd = M('SigninUser')->field('password')->where(array('guid' => $guid))->find();
        }else{
            $guid       = $this->user_info['guid'];
            $upwd = M('User')->field('password')->where(array('guid' => $guid))->find();
        }

        if($upwd['password'] == $pwd) {
            $this->output_data();
        } else {
            $this->output_error('10002', 'wrong pwd');
        }
    }
}
