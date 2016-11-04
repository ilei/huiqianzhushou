<?php
namespace Home\Controller;

use Home\Controller\BaseController;
use Controls\Control\PagerControl;
use Controls\Model\PagerControlModel;
use Think\Verify;

class InformationController extends BaseController{
    public function __construct(){
        parent::__construct();
        layout('layout_info');
    }


    public function index()
    {   
        $this->css[] = 'meetelf/css/home.css';
        $this->main = '/Public/meetelf/home/js/build/home.information.index.js';
        $auth = $this->kookeg_auth_data();
        $uid = $auth['guid'];
        $info       = D('User')->find_one(array('guid' => $uid,'is_del'=>0));
        $other_info = M('UserAttrInfo')->where(array('user_guid' => $uid))->find();
        //我未发布的活动数量
        $not_release = D('Activity')->where(array('user_guid' => $uid,'status'=>0,'is_del'=>0))->count();
        //我发布的活动数量
        $published['status'] = array('IN','1,2,3');
        $activity   = D('Activity')->where(array('user_guid' => $uid,'is_del'=>0,'is_verify'=>1))->where($published)->count();
        //我参与的活动数量
        $condations['status'] = array('IN', '1,0,3');
        $ticket     = D('Order')->where(array('user_guid'=>$uid,'is_del'=>0))->where($condations)->count();
        //未发布的活动
        $activity_not = D('Activity')->where(array('user_guid'=>$uid,'status'=>0,'is_del'=>0))->order('id desc')->limit(1)->select();
        $not_time = weekday(array($activity_not['0']['start_time'],$activity_not['0']['end_time']));
        //我发布的活动
        $activity_new = D('Activity')->where(array('user_guid'=>$uid,'status'=>1,'is_del'=>0,'is_verify'=>1))->order('id desc')->limit(1)->select();
        //报名情况
        $enrollment = D('ActivityUserinfo')->where(array('activity_guid'=>$activity_new['0']['guid'],'is_del'=>0))->count();
        //最新报名人员
        $where = array('status'=>array('in','0,1'));
        $enrollment_info = D('Order')->where(array('target_guid'=>$activity_new['0']['guid'],'is_del'=>0))->where($where)->order('id desc')->limit(3)->select();
        //最新报名人员状态
        $userticket = array();
        foreach ($enrollment_info as $key => $info) {
            $userticket[]  = D('ActivityUserTicket')->where(array('userinfo_guid'=>$info['guid'],'is_del'=>0))->select();

        }
        $new_tic = D('Order')->where(array('user_guid'=>$uid,'is_del'=>0))->order('id desc')->limit(1)->select();
        $in_time='';
        if ($new_tic) {
            //我参与的活动
            $my_act  = D('Activity')->where(array('guid'=>$new_tic[0]['target_guid'],'is_del'=>0))->find();
            $in_time = weekday(array($my_act['start_time'],$my_act['end_time']));
        }
        $this->assign('info', $info);
        $this->assign('other_info', $other_info);
        $this->assign('not_release',$not_release);
        $this->assign('activity', $activity);
        $this->assign('activity_not',$activity_not);
        $this->assign('activity_new', $activity_new);
        $this->assign('enrollment', $enrollment);
        $this->assign('enrollment_info', $enrollment_info);
        $this->assign('my_act', $my_act);
        $this->assign('ticket', $ticket);
        $this->assign('in_time', $in_time);
        $this->assign('not_time', $not_time);
        $this->assign('meta_title', L('_MY_PAGE_'));

        $this->show();
    }

    public function information()
    {	

        $this->main = '/Public/meetelf/home/js/build/birthday.js';
        $auth = $this->kookeg_auth_data();
        $uid = $auth['guid'];
        $info       = D('User')->find_one(array('guid' => $uid));
        $other_info = M('UserAttrInfo')->where(array('user_guid' => $uid))->find();

        if (!empty($other_info['area2']) && !empty($other_info['area1'])) {
            $area_2 = D('Area')->find_all(array('parent_id' => $other_info['area1']), 'id, name');
            $this->assign('area_2', $area_2);
        }
        $area_1 = D('Area')->find_all('deep=1', 'id, name');
        $this->assign('area_1', $area_1);
        $this->assign('auth', $auth);
        $this->assign('info', $info);
        $this->assign('other_info', $other_info);
        $this->assign('meta_title', L('_INFORMATION_MSG_'));
        $this->show();
    }

    public function saveBase()
    {
        if (IS_POST) {
            $param      = I('post.');
            $data_info  = $param['info'];
            $data_other = $param['other_info'];
            $data_other['position'] = trim($data_other['position']);
            $data_other['company'] = trim($data_other['company']);
            $data_other['address'] = trim($data_other['address']);
            $data_other['realname'] = trim($data_other['realname']);
            $user_guid  = $this->kookeg_auth_data('guid');
            $auth = $this->kookeg_auth_data();
            $r ='';
            // 保存用户数据
            if (!empty($data_info)) {
                session('check_guid', $user_guid);
                session('check_mobile', $data_info['mobile']);
                $model_user = D('User');
                if ($data_info['mobile'] != $auth['mobile']) {
                    $check      = $model_user->create(array('mobile'=>$data_info['mobile']));
                    if (!$check) {
                        $this->error($model_user->getError());
                    } else {
                        $r = $model_user->where(array('guid' => $user_guid))->save();
                    }
                }

                if ($data_info['email'] != $auth['email']) {
                    $check      = $model_user->field('email')->create(array('email'=>$data_info['email']));
                    if (!$check) {
                        $this->error($model_user->getError());
                    } else {
                        $r = $model_user->where(array('guid' => $user_guid))->save();
                    }
                }

            }

            if (!empty($data_other)) {
                $model_other = D('UserAttrInfo');
                // 检查是否填写了birthday
                if (isset($data_other['y']) && isset($data_other['m']) && isset($data_other['d'])) {
                    $data_other['birthday'] = $data_other['y'] . '-' . $data_other['m'] . '-' . $data_other['d'];
                }

                $check = $model_other->create($data_other);
                if (!$check) {
                    $this->error($model_other->getError());
                } else {
                    $r = $model_other->where(array('user_guid' => $user_guid))->save($data_other);
                }
            }
            $user_info = D('User')->find_one(array('guid' => $user_guid));    
            $this->opration_after_login($user_info, $remember);
            $this->success(L('_MSG_SUCCESS_'));
        } else {
            $this->error(L('_UNAUTHORIZDE_ACCESS_'));
        }
    }

    public function ajax_get_child_area_list()
    {
        $areaid = I('post.id');
        if ($areaid < 1) {
            $this->ajaxResponse(array('status' => 'ok', 'data' => false));
        }

        $res = D('Area')->find_all('parent_id="' . $areaid . '"', 'id, name');
        if (!empty($res)) {
            $this->ajaxResponse(array('status' => 'ok', 'data' => $res));
        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_PARAMETER_ERROR_')));
        }

    }

    public function password()
    {   
        $this->main = '/Public/meetelf/home/js/build/home.base.password.js';
        if (IS_POST) {
            $new_password = I('post.password');
            $re_password  = I('post.repassword');

            if (!empty($new_password) && $new_password == $re_password) {
                $guid   = $this->kookeg_auth_data('guid');
                $result = M('User')->where(array('guid' => $guid))->setField('password', md5($new_password));
                if ($result || $result === 0) {
                    session(null);
                    exit($this->success(L('_PASSWORD_SUCCESS_'), U('/Home/Auth/login', '', true, false, false)));
                } else {
                    $this->error(L('_EDIT_FILAD_'));
                }
            } else {
                $this->error(L('_PASSWORD_EMPTY_'));
            }
        }

        $this->assign('meta_title', L('_EDIT_PASSWORD_'));
        $this->show();
    }

    public function organizers()
    {  
        $this->main = '/Public/meetelf/home/js/build/organizer.js';
        $this->css[] = 'meetelf/css/information.css';
        $auth = $this->kookeg_auth_data(); 
        $model = D('OrganizerInfo');
        $num_per_page = C('NUM_PER_PAGE', null, '10');
        $list         = $model->alias('g')
            ->where(array('user_guid' => $auth['guid'], 'status' => 1))
            ->order(array('created_at' => 'DESC'))
            ->page(I('get.p', '1') . ',' . $num_per_page)
            ->select();
        foreach ($list as $key => $value) {
            $activity_num = M('ActivityAttrUndertaker')->where(array( 'organizer_guid' =>$value['guid']))->count();
            $list[$key]['activity_num']=$activity_num;
        }
        $this->assign('list', $list);
        $this->assign('auth', $auth);
        $this->assign('meta_title', L('_ORGANIZER_MSG_'));
        $this->show();
    }

    public function organizer_add()
    {
        $auth = $this->kookeg_auth_data();
        $model = D('OrganizerInfo');
        $time  = time();     
        if (IS_POST) {
            $data = array(
                'guid'          => create_guid(),
                'name'          => I('post.account'),
                'mobile'        => I('post.mobile'),
                'user_guid'     => $auth['guid'],
                'is_active'     => intval(I('post.status')),
                'desc'          => I('post.desc'),
                'photo'         => I('post.poster'),
                'created_at'    => $time,
                'updated_at'    => $time,

            );

            //用户名称是否存在
            $restmp = $model->find_all('name ="' . $data['name'] . '" and user_guid="'.$auth['guid'].'" and status=1');
            if (empty($restmp)) {
                $data = $model->create($data);
                if (!$data) {
                    exit($this->error($model->getError()));
                }
                $res = $model->add($data);
                if (!$res) {
                    exit($this->error(L('_ORGANIZER_FILAD_')));
                }
                exit($this->success(L('_ORGANIZER_SUCCESS_'), U('Home/User/sponsor')));
            } else {
                $this->assign('exist', $restmp);
                exit($this->error(L('_EXISTED_'), U('Home/User/sponsor')));
            }
        }
    }

    public function organizer_edit()
    {
        $auth = $this->kookeg_auth_data();
        $guid = I('get.guid');
        $time  = time();
        if (IS_POST) {
            $data = array(
                'name'  => I('post.account'),
                'mobile'    => I('post.mobile'),
                'desc'    => I('post.desc'),
                'photo'         => I('post.poster_photo'),
                'created_at'    => $time,
                'updated_at'    => $time,
            );
            $model = D('OrganizerInfo');
            $data = $model->create($data, 2);
            if (!$data) {
                exit($this->error($model->getError()));
            }
            $res = $model->where(array('guid' => $guid))->save($data);
            if (!$res) {
                exit($this->error(L('_ORGANIZER_EDIT_FILAD_')));
            } else {
                exit($this->success(L('_ORGANIZER_EDIT_SUCCESS_'), U('Home/User/sponsor')));
            }
        }
        exit($this->error(L('_MSG_FILAD_'), $this->getReferer()));
    }

    public function del()
    {
        $guid = I('get.guid');
        if (empty($guid)) {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_DEL_FILAD_')));
        }

        // 检查是否存在
        $exist = D('OrganizerInfo')->find_one(array('guid' => $guid));
        if (!$exist) {
            $this->ajaxResponse(array('status' => 'ok', 'msg' => L('_DEL_SUCCESS_')));
        }

        $res = D('OrganizerInfo')->where(array('guid'=>$guid))->save(array('status'=>0));

        // 返回数据
        if (empty($res)) {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_DEL_FILAD_')));
        }
        $this->ajaxResponse(array('status' => 'ok', 'msg' => L('_DEL_SUCCESS_')));
    }

    public function account()
    {
        $this->main = '/Public/meetelf/home/js/account.js';
        $auth  = $this->kookeg_auth_data();
        $model = D('SigninUser');
        $this->assign('meta_title', L('_REGISTRATION_ACCOUNT'));
        $num_per_page = C('NUM_PER_PAGE', null, '10');
        $list         = $model->alias('g')
            ->where(array('user_guid' => $auth['guid'], 'is_del' => C('SIGINUSER.NO_DEL')))
            ->order(array('created_at' => 'DESC'))
            ->page(I('get.p', '1') . ',' . $num_per_page)
            ->select();
        $user_info = D('User')->where(array('guid' => $auth['guid']))->find();
        if(empty($user_info['login_verify_code'])){
            $vcode = $this->create_login_verify_code();
            $res = D('User')->where(array('guid' => $auth['guid']))->data(array('login_verify_code' => $vcode))->save();
            if(!empty($res)){
                $user_info = D('User')->where(array('guid' => $auth['guid']))->find();
            }
        }
        $render = array(
            'list' => $list,
            'user_info' => $user_info
        );
        $this->assign($render);
        $this->show();
    }

    public function create_login_verify_code(){
        $v_code = '';
        for($i = 0; $i < 6; $i++){
            $v_code .= rand(0,9);
        }

        $res = D('User')->where(array('login_verify_code' => $v_code))->find();
        if(empty($res)){
            return $v_code;
        }else{
            $this->create_login_verify_code();
        }
    }

    public function add()
    {
        $auth       = $this->kookeg_auth_data();
        $model      = D('SigninUser');
        $user_count = $model->where(array('user_guid' => $auth['guid'], 'is_del' => C('SIGINUSER.NO_DEL', null, 10)))->count();
        $num_limit  = C('SIGINUSER.MAX_NUM', null, 10);
        if ($user_count >= $num_limit) {
            $this->ajaxResponse(array('status' => C('ajax_failed'),'msg'=>L('_NOT_CREATE_ACCOUNT_') . $num_limit . L('_USER_ACCOUNT_')));
        }
        if (IS_POST) {
            $data = array(
                'guid'      => create_guid(),
                'username'  => I('post.username'),
                'password'  => md5(I('post.password')),
                'user_guid'  => $auth['guid'],
                'is_active' => intval(I('post.status')),
                'remark'    => I('post.remark'),
            );

            //用户名称是否存在
            $restmp = $model->find_all('username ="' . $data['username'] . '" and user_guid="'.$auth['guid'].'"');
            if (empty($restmp)) {
                $data = $model->create($data);
                if (!$data) {
                    exit($this->error($model->getError()));
                }
                $res = $model->add($data);
                if (!$res) {
                    $this->ajaxResponse(array('status' => C('ajax_failed'),'msg'=>L('_MSG_FILAD_')));
                }
                exit($this->success(L('_ACCOUNT_SUCCESS_'), U('Home/User/account')));
            } else {
                $this->ajaxResponse(array('status' => C('ajax_failed'),'msg'=>L('_ACCOUNT_EXISTED_')));
            }
        }
        $this->assign('meta_title', L('_ADD_ACCOUNT_'));
        $this->display();
    }

    public function del_account()
    {
        $guid = I('get.guid');
        if (empty($guid)) {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_DEL_FILAD_')));
        }

        // 检查是否存在
        $exist = D('SigninUser')->find_one(array('guid' => $guid));
        if (!$exist) {
            $this->ajaxResponse(array('status' => 'ok', 'msg' => L('_DEL_SUCCESS_')));
        }

        // 执行删除操作
        $res = D('SigninUser')->phy_delete(array('guid' => $guid, 'is_active' => C('SIGINUSER.NO_ACTIVE')));
        if(D('UserDevice')->where(array('user_guid' => $guid))->find()){
            D('UserDevice')->where(array('user_guid' => $guid))->delete();
        }

        // 返回数据
        if (empty($res)) {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => L('_DEL_FILAD_')));
        }
        $this->ajaxResponse(array('status' => 'ok', 'msg' => L('_DEL_SUCCESS_')));
    }
    public function edit()
    {
        $auth       = $this->kookeg_auth_data();
        $guid = I('get.guid');
        if (IS_POST) {
            $data = array(
                'username'  => I('post.username'),
                'is_active' => intval(I('post.status')),
                'remark'    => I('post.remark'),
            );
            if (I('post.password')) {
                $data['password'] = md5(I('post.password'));
            }
            $model = D('SigninUser');

            //用户名称是否存在
            $restmp = $model->find_all(array('username' => $data['username'], 'guid' => array('neq', $guid),'user_guid'=> $auth['guid']));
            if (empty($restmp)) {
                $data = $model->create($data, 2);
                if (!$data) {
                    exit($this->error($model->getError()));
                }
                if (!I('post.password')) {
                    unset($data['password']);
                }
                $res = $model->where(array('guid' => $guid))->save($data);
                if (!$res) {
                    exit($this->error(L('_ACCOUNT_EDIT_FIALD_')));
                } else {
                    exit($this->success(L('_ACCOUNT_EDIT_SUCCESS_'), U('Home/User/account')));
                    // exit($this->success('签到账号 修改成功', U('Info/signin')));
                }
            } else {
                exit($this->error(L('_ACCOUNT_EXISTED_'), U('Home/User/account')));
            }
        } else {
            if (empty($guid)) {
                exit($this->error(L('_OPERATION_FAILED_'),U('Home/User/account')));
            }

            $exist = D('SigninUser')->find_one(array('guid' => $guid));
            if (!$exist) {
                exit($this->error(L('_OPERATION_USER_FAILED_'), U('Home/User/account')));
            }
            $this->assign('user', $exist);
            $this->assign('meta_title', L('_EDIT_ACCOUNT_'));
            $this->display();
            exit();
        }
        exit($this->error(L('_OPERATION_FAILED_'), $this->getReferer()));
    }


    public function wallet(){
        $this->assign('meta_title', L('_MY_WALLET_'));
        $this->main  = '/Public/meetelf/home/js/build/home.information.js';
        $this->css[] = 'meetelf/home/css/release.css';
        $auth = $this->kookeg_auth_data(); 
        $pageSize = intval(I('post.i', 5));//分页大小
        $page = intval(I('post.p', 1));//当前页码

        //短信统计

        $model = D('order');
        $list         = $model->alias('g')
            ->where(array('buyer_guid' => $auth['guid'], 'is_discounct' => 3))
            ->order(array('created_at' => 'DESC'))
            ->page($page,$pageSize)
            ->select();

        $count = $model->alias('g')->where(array('buyer_guid' => $auth['guid'], 'is_discounct' => 3))->count();

        $model = new PagerControlModel($page,$count,$pageSize);
        $pager = new PagerControl($model,PagerControl::$Enum_First_Prev_Next_Last);
        $pager = $pager->fetch();
        $this->assign('list',$list);
        //分页
        $this->assign('pager',$pager);
        $wallet_list_page = $this->fetch('_wallet_list');
        $this->assign('wallet_list_page', $wallet_list_page);
        $this->assign('auth', $auth);
        //
        if (IS_AJAX) {  
            $return_data['list'] = $list;
            $return_data['pager'] = $pager;
            $return_data['data'] = $this->fetch('_wallet_list');
            layout(false);
            $this->ajaxResponse($return_data,'json');
            die;
        }
        $this->show();
    }
    public function use_record(){
        $this->assign('meta_title', '使用记录');
        $this->show();
    }

    public function email_build(){
        $auth = $this->kookeg_auth_data();
        $username = I('post.email');
        $regtime = time();
        $token = md5($username.$regtime); //创建用于激活识别码 
        $token_exptime = time()+60*60*24;//过期时间为24小时后 
        $data_info = array(
            'email'           => $username,  
            'email_token'     => $token,
            'token_exptime'   => $token_exptime,
        );
        $model_user = D('User');
        //查看邮箱是否被绑定
        $info = $model_user->find_one(array('email' => $username,'is_del'=>0,'email_verify'=>1));
        if ($info=='') {
            $this->assign('token',$token);
            $this->assign('email',$username);
            $content = $this->fetch('_email_notice');
            $email_res = send_email($username,L('_APP_NAME_'),L('_AUTHENTICATE_EMAIL_'),$content);
            if($email_res['status'] != 'success'){
                send_email($username,L('_APP_NAME_'),L('_AUTHENTICATE_EMAIL_'),$content);
            }

            $check      = $model_user->create($data_info);
            if (!$check) {
                $this->error($model_user->getError());
            } else {
                $r = $model_user->where(array('guid' => $auth['guid']))->save();
            }
            $this->ajax_response(array('status' => C('ajax_success'), 'msg' => L('_SEND_SUCCESS_')));
        }else{
            $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_EMAIL_EXISTED_')));
        }
    }

    public function email_remove(){
        $auth = $this->kookeg_auth_data();
        $model_user = D('User');
        $r = $model_user->where(array('guid' => $auth['guid']))->save(array('email_verify' =>0));
        if ($r) {
            $this->ajax_response(array('status' => C('ajax_success'), 'msg' => L('_SEND_SUCCESS_')));
        }else{
            $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_FAILURE_TO_CANCEL_')));
        }
    }
    //验证码
    public function verify(){
        $config = array(
            'imageW' => 150,
            'imageH' => 40,
            'fontSize' => 20,
            'length' => 4,
            'useCurve' => false,
        );
        $verify = new Verify($config);
        $verify->codeSet = '0123456789';
        $verify->entry();
    }
    public function change_mobile(){
        $this->css[] = 'meetelf/css/login.css';
        $this->main  = '/Public/meetelf/home/js/build/home.information.change.mobile.js';
        $auth = $this->kookeg_auth_data();
        $this->assign('meta_title', L('_CHANGE_MOBILE_'));
        $this->assign('auth', $auth);
        $this->show();
    }
    public function change_mobile_step2(){
        $this->css[] = 'meetelf/css/login.css';
        $this->main  = '/Public/meetelf/home/js/build/home.information.change.mobile.step2.js';
        $this->assign('meta_title', L('_CHANGE_MOBILE_'));
        $this->show();
    }
    public function change_mobile_step3(){
        $auth = $this->kookeg_auth_data();
        $mobile = I('post.mobile');
        $model_user = D('User');
        $r = $model_user->where(array('guid' => $auth['guid']))->save(array('mobile'=>$mobile));
        if ($r) {
            session(null);
            exit($this->success(L('_CHANGE_SUCCESS_'), U('Auth/login')));
        }else{
            exit($this->error(L('_CHANGE_FILAD_')));
        }

    }
    //ajax发送校验码
    public function ajax_send_code(){
        $mobile = I('post.mobile');
        $time = time();
        $code = kookeg_get_mobile_code();

        $data['guid'] = create_guid();
        $data['created_at'] = $time;
        $data['updated_at'] = $time;
        $data['status'] = 0;
        $data['code'] = $code;
        $data['type'] = 5;//更换手机号
        $data['key'] = md5($mobile.$code);//key   手机号+校验码
        $data['expired_at'] = $time+1800;//30分钟有效

        $day = strtotime(date("Y-m-d"));
        $next_day = $day+86400;
        $map['updated_at']=array('between',array($day,$next_day)); 
        $num = M('CheckMobile')->where(array('mobile'=>$mobile,'type'=>5))->where($map)->count();
        if ($num <3) {

            $data['mobile'] = $mobile;
            $res = M('CheckMobile')->data($data)->add();
            if($res){
                send_sms(C('CODE_TYPE.api_verify_mobile'), $mobile, array($code, 30), array('type' => 1));//发送验证码
                $this->ajax_response(array('status' => C('ajax_success'), 'msg' => L('_SEND_SUCCESS_')));
            }
        }else{
            $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_TIME_OUT_')));
        }    
    }

    //ajax验证验证码
    public function ajax_check_verify(){
        $verify = new Verify();
        $code = I('post.verify');
        if($verify->check($code)){
            echo 'true';
            exit();
        }else{
            echo 'false';
            exit();
        }
    }

    //ajax检查手机校验码准确性
    public function ajax_check_mobile_code() {
        // 检查验证码是否正确
        $mobile = I('post.mobile');
        $code = I('post.code');
        if($mobile == '' || $code == '') {
            echo 'false';exit();
        }
        $key = md5($mobile.$code);

        $check_data = M('CheckMobile')->field('expired_at')->where(array('key' => $key))->find();

        if(time() < $check_data['expired_at']){
            M('CheckMobile')->where(array('key' => $key))->data(array('status' => 1,'updated_at' => time()))->save();
            echo 'true';exit();
        }else{
            echo 'false';exit();
        }
    }
    //检查手机号是否重复
    public function ajax_check_mobile(){
        $mobile = I('post.mobile');
        if(strlen($mobile) == 11){
            $user_info = M('User')->where(array('mobile' => $mobile,'is_del' => '0'))->find();
            if($user_info){
                echo 'false';
                exit;
            }else{
                echo 'true';
                exit;
            }
        }
    }

    public function check()
    {
        $type  = I('get.type');
        $field = I('post.field');
        $auth  = $this->kookeg_auth_data();
        switch ($type) {
        case 'old_pass':
            $res = D('User')->find_one(array('guid' => $auth['guid'], 'password' => md5($field)));
            echo empty($res) ? 'false' : 'true';
            break;
        default:
            echo 'false';
            break;
        }
        exit();
    }
}
