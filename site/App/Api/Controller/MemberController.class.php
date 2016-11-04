<?php
namespace Api\Controller;

vendor('getui.YmPush');
use Api\Controller\BaseController;
use Think\Image;
use Think\Upload;
use Common\Model\UserModel;
use Org\Api\YmChat;

/**
 * 我 控制器
 *
 * CT: 2014-10-13 16:00 by YLX
 *
 */
class MemberController extends BaseUserController
{

    /**
     * 获取用户信息
     * photo传的240的
     * CT: 2014-10-17 11:20 by YLX
     */
    public function info() {
        $this->check_request_method('get');

        $guid       = $this->user_info['guid'];
        $updated_at = I('get.ut', '0');

        // 判断客户端用户信息是否已经是最新
        if ($this->user_info['updated_at'] <= $updated_at) {
            $this->output_error('10013');
        }

        $info = D('User')->getDetail($guid);
        // html字符反转
        foreach($info['company'] as $key=>$value){
            foreach($value as $k=>$v) {
                $info['company'][$key][$k] = trim(htmlspecialchars_decode($v));
            }
        }
        $this->output_data($info);
    }
    
    /**
     * 编辑用户信息
     * 
     * CT: 2014-10-13 16:30 by YLX
     * UT: 2014-10-17 16:50 by YLX
     */
    public function edit()
    {
        if (IS_PUT){
            $params = $this->_request_params;
            $key = $params['k'];
            $value = $params['v'];
            $user_guid = $this->user_info['guid'];

            $editable = array('real_name', 'photo', 'sex', 'birthday', 'home_area', 
                              'interest', 'edu', 'area', 'industry', 'remark');
            
            if (!in_array($key, $editable)) return $this->output_error('10003');
            
            $time = time();
            switch ($key){
            	case 'birthday':
            	    if (date('Y-m-d') < $value) {
            	        $this->output_error('10014');
            	    }
                    $data = array($key=>$value, 'updated_at'=>$time);
                    break;
            	case 'sex':
            	case 'edu':
                case 'real_name':
                    $data = array($key=>$value, 'updated_at'=>$time);
                    break;
                case 'remark':
                    if(mb_strlen($value,'utf-8') <= 50){
                        $data = array($key=>$value, 'updated_at'=>$time);
                    }else{
                        return $this->output_error('10003','签名长度不得超过50字');
                    }
                    break;
                case 'interest':
            	    $data = array('updated_at'=>$time);
            	    $del_ids = explode(',', $params['del']);//I('post.del'));
            	    $add_ids = explode(',', $params['add']);//I('post.add'));
            	    // 删除兴趣
            	    if (!empty($del_ids)){
            	        M('UserInterest')->where(array('interest_id'=>array('IN', $del_ids), 'user_guid'=>$user_guid))->delete();
            	    }
            	    // 增加兴趣
            	    if (!empty($add_ids)){
            	        $add_arr = array();
            	        foreach ($add_ids as $id){
                            $user_interests = D('User')->getInterestIds($user_guid);
                            if (!in_array($id, $user_interests)) {
                                $add_arr[] = array('guid' => create_guid(),
                                                    'user_guid' => $user_guid,
                                                    'interest_id' => $id,
                                                    'created_at' => $time,
                                                    'updated_at' => $time
                                                );
                            }
            	        }
            	        $r = M('UserInterest')->addAll($add_arr);
                        if (!empty($r)){
                            M('Interest')->where(array('id'=>array('IN', $add_ids)))->setField('num', array('exp', 'num+1'));
                        }
            	    }
            	    break;
            	case 'industry':
                    $data = array('main_industry_guid'=>$value, 'updated_at'=>$time);
                    break;
            	case 'area':
            	case 'home_area':
            	    list($areaid_1, $areaid_2) = explode(',', $value);
            	    if (empty($areaid_1) || empty($areaid_2)){
            	        return $this->output_error('10003');
            	    }
            	    $data = array($key.'id_1'=>$areaid_1, $key.'id_2'=>$areaid_2, 'updated_at'=>$time);
            	    break;
            	default:
                    $this->output_error('10003');
            	   break;
            }
    
            $res = M('User')->where(array('guid'=>$user_guid))->data($data)->save();
            if ($res){
                return $this->output_data(array('ut'=>$time)); // 保存成功
            }else{
                return $this->output_error('10011'); // 保存失败
            }
        } else {
            $this->output_error('10025', 'HTTP request error');
        }
    }

    /**
     * 上传头像
     *
     * CT: 2014-12-03 12:11 by ylx
     */
    public function portrait()
    {
        $picture  = $this->check_request_method('post');

//        $params    = $this->_request_params;
        $mid = $this->user_info['guid'];//I('post.user_guid');
        $time   = time();
        $config = array(
            'maxSize' => C('MAX_UPLOAD_SIZE'),
            'exts'    => C('ALLOWED_EXTS'),
            'rootPath' => UPLOAD_PATH,
            'savePath' => "/user/".date('Y_m_d', time())."/$mid/profile/",
            'subName'  => '',
            'saveName' => md5($mid.$time),
            'replace'  => true
        );
        $upload = new Upload($config);// 实例化上传类
        // 上传文件
        $info = $upload->upload();
        if (empty($info)){
            return $this->output_error('10012', $upload->getError());
        }

        $photo_path = $info['v']['savepath'].$info['v']['savename'];
        //RTH
        $temp_path = UPLOAD_PATH.$photo_path;
        $image = new Image();
        $image->open($temp_path);
//        $ext = $image->type();
        $upload_path = $info['v']['savepath'];
        $upload_name = md5($mid.$time);
        $upload_240_path = $upload_path.$upload_name.'_240.jpg';
        $upload_120_path = $upload_path.$upload_name.'_120.jpg';
        $image->thumb('240', '240')->save(UPLOAD_PATH.$upload_240_path);
        $image->thumb('120', '120')->save(UPLOAD_PATH.$upload_120_path);
        $data = array('photo'=>$photo_path,'photo_240'=>$upload_240_path,'photo_120'=>$upload_120_path, 'updated_at'=>$time);
        //RTH
//        $data = array('photo'=>$photo_path, 'updated_at'=>$time);
        $res = M('User')->where(array('guid'=>$mid))->data($data)->save();

        if ($res){
            return $this->output_data(array('ut'=>$time, 'return'=>$upload_240_path)); // 保存成功
        }else{
            return $this->output_error('10011'); // 保存失败
        }
    }
    
    /**
     * 获取行业列表
     * 
     * CT: 2014-10-17 11:06 by YLX
     */
    public function industry_list()
    {
        $list = D('Industry')->get_active_list();
        $this->output_data($list);
    }

    /**
     * 修改密码
     * 
     * CT: 2014-11-27 14:08 by QXL
     */
    public function change_passwd(){
        $this->check_request_method('put');
    	$old = $this->_request_params['oldpasswd'];
        $new = $this->_request_params['newpasswd'];
    	if(empty($old) || empty($new)){
    		$this->output_error('10003');
    	}

    	if ($old!==$this->user_info['password']){
    		$this->output_error('10002');
    	}
        // 注册环信
        $YmChat = new \Org\Api\YmChat();
        $result = $YmChat->editPassword(array('username'=>$this->user_info['guid'],'password'=>hashCode($old),'newpassword'=>hashCode($new)));
        if($result['status'] != 200){
            $this->output_error('10011');
        }

        // 更新数据库
    	if(M('User')->where(array('guid'=>$this->user_info['guid']))->save(array('password'=>$new))){
    		$this->output_data();
    	}else{
            $YmChat->editPassword(array('username'=>$this->user_info['guid'],'password'=>hashCode($new),'newpassword'=>hashCode($old)));
    		$this->output_error('10011');
    	}
    }
    
    /**
     * 密码验证
     *
     * CT: 2014-12-04 14:08 by QXL
     */
    public function check_passwd(){
        $this->check_request_method('post');

        $data = $this->_request_params;
        if (!$data['password']){
            $this->output_error('10003');
        }
        $password=$data['password'];

        if ($password == $this->user_info['password']){
            $this->output_data();
        } else {
            $this->output_error('10002');
        }
    }

    /**
     * 关闭APP后， 重新打开APP
     *
     * CT: 2014-10-09 10:00 by YLX
     * UT: 2014-12-31 10.00 by qiu
     */
    public function relogin()
    {
        $this->check_request_method('post');
        $user_info = $this->user_info;
        $user_guid = $user_info['guid'];
        $token = $this->_request_params['Token'];

        // 更新token时间
        $time = time();
        $new_token = md5($token.$time);
        $token_num = rand(10,99);
        $data = array(
            'token'         => $new_token,
            'token_num'     => $token_num,
            'app_version'   => I('post.app_version'),
            'updated_at'    => $time
        );
        $model_device = D('UserDevice');
        $check_data = $model_device->where(array('user_guid' => $user_guid, 'token' => $token))->create($data);
        if(!$check_data) {
            $this->output_error('10023', 'wrong token or user_guid');
        }
        if($model_device->save()){
            //清除Redis缓存, 生成新的
            S($token.':user_device', null);
            $token_info = D('UserDevice')->getTokenInfo(array('user_guid' => $user_guid, 'token' => $new_token));
			
            S($token.':user_device', $token_info, C('TOKEN_EXPIRE'));    //设置Redis缓存

            // 解除所有与该用户别名绑定的CID
            $this->unbindGetuiAlias($user_guid);

            // 返回JSON数组
            $data = array(
                              'guid'      => $user_info['guid'], 'email' => $user_info['email'],
                              'mobile'    => $user_info['mobile'],
                              'real_name' => isset($user_info['real_name']) ? $user_info['real_name'] : $user_info['email'],
                              'is_active' => $user_info['is_active'],
                              'photo'     => !empty($user_info['photo'])?$user_info['photo']:0,
                              'moblie_verify' => $user_info['moblie_verify'],
                              'token'     => $new_token,
                              'token_num' => $token_num
            );
            header('Token:'.$new_token);
            $this->output_data($data);
        } else {
            $this->output_error('10023');
        }
    }

    /**
     * 帐号退出登录
     *
     * CT: 2014-09-19 17:00 by YLX
     * UT: 2014-09-28 11:00 by YLX
     */
    public function logout()
    {
        $this->check_request_method('post');
        $guid = $this->user_info['guid'];//I('post.guid');
        if (empty($guid)) {
            $this->output_error('10003'); // 参数错误
        }
		$type = I('post.type', 1); // 1为关闭平台，2为退出登录
        // 解除所有与该用户别名绑定的CID
        $this->unbindGetuiAlias($guid);
		if(intval($type) == 2){
        	D('UserDevice')->logoutAll($guid);
			// 清除redis缓存
			$token = $this->_request_params['Token'];
			S($token.':user_device', null);
			S($token.':user_info', null);
		}
        $this->output_data();
    }


    /**
     * 上传错误日志
     *
     * CT: 2014-12-08 11:00 by YLX
     */
    public function error_log()
    {
        $this->check_request_method('post');
        $device = I('post.device', 'android');
        $user_guid = $this->user_info['guid'];
        $time   = time();
        $config = array(
            'rootPath' => UPLOAD_PATH,
            'savePath' => "/app_error_log/$device/".date('Y_m_d', time())."/",
            'subName'  => '',
            'saveName' => $user_guid.'-'.$time
        );
        $upload = new Upload($config);// 实例化上传类
        // 上传文件
        $info = $upload->upload();
        if (empty($info)){
            return $this->output_error('10011', $upload->getError());
        }

        return $this->output_data(); // 保存成功
    }
	
	
	/**
     * 获取社团邀请列表
     *
     * CT: 2014-12-29 09:50 by QXL
     */
    public function get_org_invite(){
        $this->check_request_method('get');

        $guid = $this->user_info['guid'];
        $condition = array();
        $condition['user_guid'] = $guid;
        $condition['is_del'] = '0';
        $condition['status'] = array('neq','4');
        $invite_list = D('UserOrgStateView')->where($condition)->order('updated_at DESC')->select();
        if (empty($invite_list)){
           $this->output_data(array('list'=>null));
        }
        $this->output_data(array('list'=>$invite_list));
    }
    
    /**
     * 获取黑名单列表
     *
     * CT: 2014-12-29 10:00 by QXL
     */
    public function get_org_blacklist(){
        $this->check_request_method('get');

        $uid = $this->user_info['guid'];
        $blacklist = D('OrgBlacklistView')->getList($uid);
        if (empty($blacklist)){
           $this->output_data(array('list'=>null));
        }
        $this->output_data(array('list'=>$blacklist, 'ut'=>time()));
    }
    
    /**
     * 添加用户的社团黑名单
     *
     * CT: 2014-12-31 14:00 by qiu
     */
    public function add_org_black()
    {
        $this->check_request_method('post');
        $params = $this->_request_params;
        $mid = $this->user_info['guid'];
        $oid = $params['oid'];

        //2 判断参宿是否正确
        if (empty($mid) || empty($oid)) $this->output_error('10003');
        $data = array();
        $data['org_guid'] = $oid;
        $data['user_guid'] = $mid;
        $data['type'] = 2;
        //3 判断是否存在
        $model = D('OrgBlacklist');
        $blacklist = $model->where($data)->select();
        if (!empty($blacklist)){
            $this->output_error('10009');
        }
        //4 准备录入 补齐参数
        $time = time();
        $data['guid'] = create_guid();
        $data['created_at'] = $time;
        $data['updated_at'] = $time;
        $data['is_del'] = 0;
        //5 录入
        $res = $model->data($data)->add();
        if (empty($res)) {
            $this->output_error('10009');
        }

        $status_res = M('UserOrgState')->where(array('user_guid'=>$mid, 'org_guid'=>$oid))->save(array('status'=>4, 'updated_at'=>time()));
        if(empty($status_res)){
            $this->output_error('10009');
        }

        $this->output_data();
        exit();
    	  	    	
    }
    
    /**
     * 删除用户的社团黑名单 物理删除
     *
     * CT: 2014-12-31 14:00 by qiu
     */
    public function del_org_black(){
    	 $this->check_request_method('delete');
         $mid = $this->user_info['guid'];
         $oid = I('get.oid');

         //2 判断参宿是否正确
         if (empty($mid) || empty($oid)) $this->output_error('10003');

         //3 直接删除
         $res = D('OrgBlacklist')->where(array('user_guid'=>$mid, 'org_guid'=>$oid, 'type'=>2))->delete();
         $user_org_state_res = M('UserOrgState')->where(array('user_guid'=>$mid, 'org_guid'=>$oid, 'type'=>'1', 'status'=>'4'))->delete();
         
         if (empty($res) || empty($user_org_state_res)){
         	$this->output_error('10009');
         }
		 
         $this->output_data();
         exit();
    }
    
    /**
     * 处理邀请信息
     *
     * CT: 2014-12-29 10:00 by QXL
     */
	public function handle_org_invite(){
		if(IS_PUT) {
			$this->check_request_method('put');
			$org_guid = $this->_request_params['org_guid'];
			$status = $this->_request_params['status'];//2:同意 3:拒绝 1:拉黑
			$user_guid = $this->user_info['guid'];
			
			if(empty($org_guid) || empty($status)){
				$this->output_error('10003');
			}
			
			$time = time();
			
			$condition = array();
			$condition['user_guid'] = $user_guid;
			$condition['org_guid'] = $org_guid;
			$condition['type'] = '1';
			
			if(M('UserOrgState')->where($condition)->save(array('status'=>$status, 'updated_at'=>time()))){
				switch ($status) {
					case 2://同意
						$org_info = M('Org')->where(array('guid'=>$org_guid))->find();
//						$all_chat_group_id = $org_info['all_chat_group_id'];
//						$other_chat_group_id = $org_info['other_chat_group_id'];
						
						$add_group_members_data=array();
						$add_group_members_data['guid'] = create_guid();
						$add_group_members_data['user_guid'] = $user_guid;
						$add_group_members_data['org_group_guid'] = null;
						$add_group_members_data['org_guid'] = $org_guid;
						$add_group_members_data['created_at'] = $time;
						$add_group_members_data['updated_at'] = $time;
						
						if(M('OrgGroupMembers')->add($add_group_members_data)){
						    
						    //成功 记录LOG
						    D('OrgGroupMembersLog')->record(array($org_info['org_all_guid'],$org_info['org_other_guid']), array($user_guid), '1', $org_guid);
						    D('OrgMembersLog')->record($org_guid, array($user_guid), '1');

                    		D('UserTimeline')->record($user_guid, '2', $org_guid, $org_info['name']);
                            $this->output_data();
//							$YmChat = new YmChat();
//							$all_result = $YmChat->addGroupsUser($all_chat_group_id, $user_guid);
//							$other_result = $YmChat->addGroupsUser($other_chat_group_id, $user_guid);
//                            if($all_result['status']=='200' && $other_result['status']=='200'){
//								$this->output_data();
//                            }else{
//                                $this->output_error('10032');
//                            }
						}else{
							$this->output_error('10011');
						}
						break;
					case 3://拒绝
						$this->output_data();
						break;
					case 4://拉黑
						$blacklist_data = array();
						$blacklist_data['guid'] = create_guid();
						$blacklist_data['user_guid'] = $user_guid;
						$blacklist_data['org_guid'] = $org_guid;
						$blacklist_data['type'] = '2';
						$blacklist_data['created_at'] = $time;
						$blacklist_data['updated_at'] = $time;
						if(M('OrgBlacklist')->add($blacklist_data, array(), true)){
							$this->output_data();
						}else{
							$this->output_error('10011');
						}
						break;
                    default:
                        $this->output_error('10003');
                        break;
				}
			}else{
				$this->output_error('10011');
			}
		} else {
			$this->output_error('10025', 'HTTP method error');
		}
	}

    /**
     * 获取社团拒绝理由
     * CT: 2015-01-07 12:02 by QXL
     */
    public function get_refuse_msg(){
        $this->check_request_method('get');

        $org_guid = $this->_request_params['org_guid'];
        $user_guid = $this->user_info['guid'];
        if(empty($org_guid)) {
            $this->output_error('10003');
        }

        $state_data = M('UserOrgState')->where(array('org_guid'=>$org_guid, 'user_guid'=>$user_guid, 'status'=>'3', 'type'=>'2'))->find();
        if(!empty($state_data)){
            $this->output_data(array('refuse_msg'=>$state_data['refuse_msg']));
        }else{
            $this->output_data();
        }
    }
	
	/**
     * 手机认证 - 验证手机并发送验证码
     *
     * CT: 2014-12-30 11:00 by QXL
     * CT: 2015-04-23 11:00 by ylx
     */
    public function sms_check_mobile(){
        $this->check_request_method('post');

        $mobile = $this->_request_params['mobile'];
        $user_mobile = $this->user_info['mobile'];

        if(empty($mobile) || $mobile != $user_mobile){
            $this->output_error('10003');
        }

        $sms_type = C('MOBILE_CODE_TYPE.api_verify_mobile');
        // 检查短信发送次数
        $check_send_num = D('CheckMobile')->checkSendNum($mobile, $sms_type);
        if(!$check_send_num){
            $this->output_error('10033');
        }

        $time = time();
        $mobile_code = kookeg_get_mobile_code();
        $check_data=array(
                            'mobile'=>$mobile,
                            'code'=>$mobile_code,
                            'expired_at'=>$time + 60*30,
                            'type' => $sms_type, //2, // 用户手机验证
                            'created_at'=>$time,
                            'updated_at'=>$time
                        );
        if(M('CheckMobile')->add($check_data)){
			vendor('YmPush.VerCodeInfo');
            $sms_result = \VerCodeInfo::send(C('CODE_TYPE.api_verify_mobile'), $mobile, array($mobile_code, 30));
            if($sms_result['code'] == '0'){
                // 统计发送次数
                D('CheckMobile')->recordSendNum($mobile, $sms_type);
                $this->output_data();
            }else{
                $this->output_error('10008');
            }
        }else{
            $this->output_error('10011');
        }
    }
	
    /**
     *  手机认证 - 手机确认验证
     *
     * CT: 2014-12-30 11:00 by QXL
     * CT: 2015-04-23 11:00 by ylx
     */
    public function send_check_mobile(){
        $this->check_request_method('put');

        $mobile_code = $this->_request_params['mobile_code'];
        $user_guid = $this->user_info['guid'];
        $mobile = $this->user_info['mobile'];

        if(empty($mobile_code)){
            $this->output_error('10003');
        }

        $check_data = D('CheckMobile')->check_code($mobile, $mobile_code, C('MOBILE_CODE_TYPE.api_verify_mobile'));
        switch($check_data){
            case '31': // 验证码错误
                $this->output_error('10031');
                break;
            case '30': // 操作过期
                $this->output_error('10030');
                break;
            case 'ok':
                if(M('User')->where(array('guid'=>$user_guid))->save(array('moblie_verify'=>'1'))){
                    // 删除已用验证码
                    $check_code_where = array('mobile'=>$mobile, 'code'=>$mobile_code, 'status'=>'1', 'type' => C('MOBILE_CODE_TYPE.api_verify_mobile'));
                    M('CheckMobile')->where($check_code_where)->delete();
                    $this->output_data();
                }else{
                    $this->output_error('10011');
                }
                break;
            case 'ko':
                $this->output_error('10003');
                break;
        }
    }
    
    /**
     * 身份证上传
     * CT: 2015-02-05 11:36 by QXL
     */
    public function upload_credentials(){
        $this->check_request_method('post');
        $mid = $this->user_info['guid'];
//        $realname = $this->_request_params['real_name'];
		$time   = time();
		
		$upload = new Upload();
		$upload->maxSize = C('MAX_UPLOAD_SIZE');
		$upload->exts = C('ALLOWED_EXTS');
		$upload->rootPath =  UPLOAD_PATH;
		$upload->savePath = "/user/".date('Y_m_d', time())."/$mid/credentials/";
		$upload->subName = '';

		// 上传文件
        $info = $upload->upload();
		if (empty($info)){
            return $this->output_error('10012', $upload->getError());
        }

		$identity_card_front = $info['front']['savepath'].$info['front']['savename'];
        $identity_card_back = $info['side']['savepath'].$info['side']['savename'];
        
        $data = array();
//        $data['real_name'] = $realname;
        $data['identity_card_front'] = $identity_card_front;
        $data['identity_card_back'] = $identity_card_back;
        $data['updated_at'] = $time;
		
		$res = M('UserAttribute')->where(array('user_guid'=>$mid))->save($data);
        if ($res){
            if(M('User')->where(array('guid'=>$mid))->save(array('real_name_verify'=>'2','updated_at'=>time()))){
                return $this->output_data(array('ut'=>$time));
            }else{
                return $this->output_error('10011');
            }
        }else{
            return $this->output_error('10011');
		}
	}

    /**
     * 用户实名认证状态
     * CT: 2015-03-30 17:36 by RTH
     */
    public function user_status(){
        $user_guid = $this->user_info['guid'];
        $user_model = D('User');
        $user_attribute_model = D('UserAttribute');
        $user_real_name_verify = $user_model->field('real_name_verify')->where(array('guid' => $user_guid))->find();
        //获取失败返回错误编码
        if(empty($user_real_name_verify)){
            $this->output_error(10011);
        }
        if($user_real_name_verify['real_name_verify'] == 3){ //姓名是否认证 需要配合上传身份证，0为未认证，1为已认证 2已提交未审核 3已拒绝
            $user_verify_status_info = $user_attribute_model->where(array('user_guid' => $user_guid))->find();
            if(empty($user_verify_status_info)){
                //错误提示编码
                $this->output_error(10011);
            }
            //status 用户实名认证状态    identity_refuse_reason  拒绝理由
            $this->output_data(array('status' => $user_real_name_verify['real_name_verify'],'identity_refuse_reason' => $user_verify_status_info['identity_refuse_reason']));
        }else{
            $this->output_data(array('status' => $user_real_name_verify['real_name_verify']));
        }
    }
}