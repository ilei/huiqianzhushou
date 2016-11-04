<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;

use Org\Api\YmChat;
vendor('getui.YmPush');

/**
 * 社团控制器
 * CT: 2014-11-27 15:00 by QXL
 *
 */
class UserController extends BaseController{
    /**
     * 获取社团列表
     *
     * CT: 2014-12-03 09:46 by QXL
     */
	public function index(){
		//         每页显示数量, 从配置文件中获取
		$num_per_page = C('NUM_PER_PAGE');

        $userList= D('UserView')->where(array('is_del'=>'0'))->order('created_at DESC')->page(I('get.p', '1').','.$num_per_page)->select();

		// 使用page类,实现分类
		$count      = D('UserView')->where(array('is_del' => '0'))->count();// 查询满足要求的总记录数
		$Page       = new \Think\Page($count,$num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
		$show       = $Page->show();// 分页显示输出

		$this->assign('count',$count);
		$this->assign('userList',$userList);
		$this->assign('page',$show);
		$this->display();
	}
	
	/**
     * 获取查找的社团列表
     *
     * CT: 2014-12-03 09:46 by QXL
     */
	public function select_list()
	{
		$keyword =  trim(I('post.search'));
		$this->assign('orgList',D('UserView')->getSelectUserList($keyword));
		$this->display(index);
	}

	/**
	 * 新增用户
	 *
	 * CT: 2014-12-03 09:46 by QXL
	 */
	public function add(){
		$community_types =C('COMMUNITY_TYPE');
	    $community_species =C('COMMUNITY_SPECIES');
	    $this->assign('community_types',$community_types);
	    $this->assign('community_species',$community_species);
		$this->display();
	}
	
	/**
	 * 获取社团详细信息
	 *
	 * CT: 2014-12-03 09:46 by QXL
     * UT: 2015-08-10 17:50 by QY
	 */
	public function view(){
	    $_userData=D('UserView')->getOrgData(array('guid'=>I('get.guid')));
	    $community_types =C('COMMUNITY_TYPE');
	    $community_species =C('COMMUNITY_SPECIES');
	    $levelList=D('GradeLevel')->field(array('guid','name'))->order('sort ASC')->select();
        $_userData['photo']=get_image_path($_userData['photo']);
	    //$orgData['areaid_1']=M('area')->field('name')->getById($orgData['areaid_1'])['name'];
	    //$orgData['areaid_2']=M('area')->field('name')->getById($orgData['areaid_2'])['name'];
	    $this->assign('orgData',$_userData);
	    $this->assign('community_types',$community_types);
	    $this->assign('community_species',$community_species);
	    $this->assign('levelList',$levelList);
	    $this->display();
	}
	
	/**
	 * 获取社团认证信息
	 *
	 * CT: 2014-12-03 13:50 by QXL
	 */
	public function auth(){
	    $orgData=D('UserView')->getOrgData(array('guid'=>I('get.guid')));
	    $this->assign('orgData',$orgData);
	    $this->display();
	}
	
	/**
	 * 同意认证
	 *
	 * CT: 2014-12-03 13:50 by QXL
	 */
	public function agree_auth(){
	    if(IS_AJAX){
	        $guid=I('post.key');
	        if(M('OrgAuthentication')->where(array('org_guid'=>$guid))->save(array('status'=>'3'))){
	            $this->ajaxReturn(array('status'=>'ok','msg'=>'提交成功'));
	        }else{
	            $this->ajaxReturn(array('status'=>'ko','msg'=>'操作失败'));
	        }
	    }else{
	        $this->error('非法请求');
	    }
	}
	
	/**
	 * 拒绝认证
	 *
	 * CT: 2014-12-03 13:50 by QXL
	 */
	public function refuse_auth(){
	    if(IS_AJAX){
	        $guid=I('post.key');
	        $refuse_msg=I('post.refuseMsg');
	        if(M('OrgAuthentication')->where(array('org_guid'=>$guid))->save(array('status'=>'4','refuse_msg'=>$refuse_msg))){
	            $this->ajaxReturn(array('status'=>'200','msg'=>'操作成功'));
	        }else{
	            $this->ajaxReturn(array('status'=>'201','msg'=>'操作失败'));
	        }
	    }else{
	        $this->error('非法请求');
	    }
	}
	/**
	 * 注册社团 有问题待查
	 *
	 * CT: 2014-11-28 17:00 by QXL
	 */
	public function regUser(){
	    if(IS_AJAX){
	    	//组合社团信息
	    	$levelData=M('grade_level')->order('sort ASC')->find();
	    	$UserDal = D("User");
	    	$_userData=array();
            $_userData['guid']=create_guid();
	    	//$orgData['r']=I('post.name');
            $_userData['vip']=$levelData['guid'];
	    	//$orgData['all_group_guid']=get_org_all_member_group_guid($orgData['guid']);
	    	//$orgData['other_group_guid']=get_org_other_member_group_guid($orgData['guid']);
	    	//$orgData['all_group_guid']=0;
	    	//$orgData['other_group_guid']=0;
	    	//$orgData['is_verify']=1;
            $_userData['password'] = I('post.password');
            $_userData['repassword'] = I('post.repassword');
            $_userData['email'] = I('post.email');
            $_userData['mobile'] = I('post.mobile');
			//$orgData['mail'] = I('post.email');
            $_userData['acc_type'] = I('post.community_types');
            $_userData['category'] = I('post.community_species');
			//$orgData['start'] = strtotime(I('post.startTime'));
			//$orgData['end'] = strtotime(I('post.endTime'));
			//$chat = new YmChat();
			//$res = $chat->accreditRegister(array('username'=>$orgData['guid'],'password'=> hashCode($orgData['password'])));
            //if($res['status'] != 200) {
            //    $this->ajaxReturn(array('code'=>'201','Msg'=>'环信注册失败: 社团帐户, 请重试.'));
            //}

	    	//组合社团认证信息
	    	$UserAttrInfoDal = M("user_attr_info");
	    	$_userAttrInfoData=array();
            $_userAttrInfoData['guid']=create_guid();
            $_userAttrInfoData['user_guid']=$_userData['guid'];
            //$_userAttrInfoData['status']='0';
            $_userAttrInfoData['realname']=I('post.realname');
            $_userAttrInfoData['nickname']=I('post.nickname');
            $_userAttrInfoData['created_at']=time();
            $_userAttrInfoData['updated_at']=time();

			if($UserDal->create($_userData)){
				if ($UserDal->add()){
					if($UserAttrInfoDal->add($_userAttrInfoData)){
						$this->ajaxReturn(array('code'=>'200'));
					}else{
						$this->ajaxReturn(array('code'=>'201','Msg'=>'账号认证信息创建失败'));
					}
				}else{
					$this->ajaxReturn(array('code'=>'201','Msg'=>'账号注册失败'));
				}
			}else{
				$this->ajaxReturn(array('code'=>'201','Msg'=>$UserDal->getError()));
			}
    	}else{
    		 $this->error('非法请求');
    	}
	}
	
	/**
	 * 修改社团等级
	 *
	 * CT: 2014-12-04 14:14 by QXL
     * UT: 2015-08-11 09:23 by QY
	 */
	public function change_level(){
	    if(IS_AJAX){
	        $guid=I('post.key');
			$realname = I('post.realname');
            $nickname = I('post.nickname');
			$type = I('post.community_types');
			$category = I('post.community_species');
			//$start = strtotime(I('post.startTime'));
			//$end = strtotime(I('post.endTime'));
            //each($nickname);
            //exit;
	        if(M('User')->where(array('guid'=>$guid))->save(array('updated_at'=>time(),'vip'=>I('post.vip'), 'acc_type'=>$type,'category'=>$category))){
	           if (M('UserAttrInfo')->where(array('user_guid'=>$guid))->save(array('realname'=>trim($realname),'nickname'=>trim($nickname)))){
                   $this->ajaxReturn(array('code'=>'200','Msg'=>'保存成功'));
               }else {
                   $this->ajaxReturn(array('code'=>'201','Msg'=>'保存失败'));

               }
	        }else{
	            $this->ajaxReturn(array('code'=>'201','Msg'=>'保存失败'));
	        }
	    }else{
	        $this->error('非法请求');
	    }
	}
	
	/**
	 * 删除用户
	 *
	 * CT: 2014-11-28 17:00 by QXL
     * UT: 2015-08-10 12:06 by QY
	 */
	public function delUser(){
	    if(IS_AJAX){
	        $guid=I('post.key');
	        if(M('User')->where(array('guid'=>$guid))->save(array('is_del'=>'1', 'updated_at'=>time()))){
	            $this->ajaxReturn(array('code'=>'200','Msg'=>'删除成功'));
	        }else{
	            $this->ajaxReturn(array('code'=>'201','Msg'=>'删除失败'));
	        }
	    }else{
	        $this->error('非法请求');
	    }
	}
	
	/**
	 * 锁定用户
	 *
	 * CT: 2014-11-28 17:00 by QXL
     * UT: 2015-08-10 12:01 by QY
	 */
	public function lock(){
	    if(IS_AJAX){
	        $guid=I('post.key');
	        if(M('User')->where(array('guid'=>$guid))->save(array('is_lock'=>'1', 'updated_at'=>time()))){
	            $this->ajaxReturn(array('code'=>'200','Msg'=>'锁定成功'));
	        }else{
	            $this->ajaxReturn(array('code'=>'201','Msg'=>'锁定失败'));
	        }
	    }else{
	        $this->error('非法请求');
	    }
	}
	
	/**
	 * 解锁用户
	 *
	 * CT: 2014-11-28 17:00 by QXL
     * UT：2015-08-10 12:03 by QY
	 */
	public function unlock(){
	    if(IS_AJAX){
	        $guid=I('post.key');
	        if(M('User')->where(array('guid'=>$guid))->save(array('is_lock'=>'0', 'updated_at'=>time()))){
	            $this->ajaxReturn(array('code'=>'200','Msg'=>'解锁成功'));
	        }else{
	            $this->ajaxReturn(array('code'=>'201','Msg'=>'解锁失败'));
	        }
	    }else{
	        $this->error('非法请求');
	    }
	}
	
	/**
	 * 检查是否存在用户Email
	 *
	 * CT: 2014-11-28 17:00 by QXL
     * UT: 2015-08-10 16:33 by QY
	 */
	public function checkMail(){
		$userInfo=M('User')->getByEmail(I('post.email'));
		if(empty($userInfo)){
			echo 'true';
			exit();
		}else{
			echo 'false';
			exit();
		}
	}
	
	/**
	 * 检查是否存在用户手机
	 *
	 * CT: 2014-11-28 17:00 by QXL
     * UT: 2015-08-10 16:33 by QY
	 */
	public function checkMobile(){
		$userInfo=M('User')->getByPhone(I('post.mobile'));
		if(empty($userInfo)){
			echo 'true';
			exit();
		}else{
			echo 'false';
			exit();
		}
	}
	
	/**
	 * 检查是否存在群组名称
	 *
	 * CT: 2014-11-28 17:00 by QXL
     * UT: 2015-08-10 16:33 by QY
	 */
	public function checkGroupName(){
		$OrgInfo=M('User')->getByName(I('post.name'));
		if(empty($OrgInfo)){
			echo 'true';
			exit();
		}else{
			echo 'false';
			exit();
		}
	}

    //内容审核页
    public function verify(){ // verify
        $org_guid = I('get.org_guid');
        $org_model = D('Org');
        $org_info = $org_model->where(array('guid'=>$org_guid))->find();

        $area = D('Area');
        $area_1 = $area->field('name')->where(array('id'=>$org_info['areaid_1']))->find();
        $area_2 = $area->field('name')->where(array('id'=>$org_info['areaid_2']))->find();

        $this->assign('area_1',$area_1);
        $this->assign('area_2',$area_2);
        $this->assign('org_info',$org_info);
        $this->display();
    }

    //社团审核通过
    public function verify_pass(){

        $org_guid = $_GET['org_guid'];
        $org_model = D('Org');
        $orgData = $org_model->where(array('guid'=>$org_guid))->find();
        $time =time();
        $chat = new YmChat();
        $res = $chat->accreditRegister(array('username'=>$orgData['guid'],'password'=>hashCode($orgData['password'])));
        if($res['status'] != 200) {
           $this->error('社团审核失败.');
		}
		/*
        $all_chat_group_id = $chat->createGroups(array('groupname'=>$orgData['name'],'desc'=>'全部成员','public'=>false,'owner'=>$orgData['guid']));
        if($all_chat_group_id['status'] != 200) {
            $this->error('社团审核失败..');
        }
        $other_chat_group_id = $chat->createGroups(array('groupname'=>$orgData['name'],'desc'=>'未分组成员','public'=>false,'owner'=>$orgData['guid']));
        if($other_chat_group_id['status'] != 200) {
            $this->error('社团审核失败...');;
        }

        $orgData['all_chat_group_id']=$all_chat_group_id['data']['groupid'];
        $orgData['other_chat_group_id']=$other_chat_group_id['data']['groupid'];
        $orgData['all_group_guid']=get_org_all_member_group_guid($orgData['guid']);
        $orgData['other_group_guid']=get_org_other_member_group_guid($orgData['guid']);
		 */
        $verify_org_res = $org_model->where(array('guid' => $org_guid))->save(array('is_verify' => 1,'verify_time'=>$time));
        if($verify_org_res){
            //发送邮件
            $content = '您的社团已经审核成功。';
            $this->assign('content',$content);
            $this->assign('orgData', $orgData);//社团信息
            $content = $this->fetch('email_notice');
            $email_result = send_email($orgData['email'], '酷客会签','恭喜社团'.$orgData['name'].'审核成功', $content);

            if($email_result['status'] != 'success') { // 邮件发送不成功,就再发一次
                send_email($orgData['email'], '酷客会签','恭喜社团'.$orgData['name'].'审核成功', $content);
            }
            $this->success('社团审核成功',U('User/index'));
        }else{
            $this->error('社团审核失败....');
        }
    }

    //社团审核不通过
    public function verify_refuse(){
        $org_model = D('Org');
        $guid = $_POST['org_guid'];
        $data['is_verify'] = 2;
        $data['verify_refuse_reason'] = htmlspecialchars($_POST['verify_refuse_reason']);
        $data['updated_at'] = time();
        $data['verify_time'] = time();
        $orgData = $org_model->where(array('guid'=>$guid))->find();
        $org_res = $org_model->where(array('guid'=>$guid))->data($data)->save();
        if($org_res){
            //发送邮件
            $content = '您的社团审核失败，请重新提交。失败原因:&nbsp;&nbsp;<b style="color: #aa1111;">'.$data['verify_refuse_reason']."</b>。".'<br>请登录'.'<a href="http://mp.shetuanbang.net">http://mp.shetuanbang.net</a>'.'修改资料重新提交。谢谢您的配合。';
            $this->assign('content',$content);
            $this->assign('orgData', $orgData);//社团信息
            $content = $this->fetch('email_notice');
            $email_result = send_email($orgData['email'], '酷客会签','社团'.$orgData['name'].'审核失败', $content);

            if($email_result['status'] != 'success') { // 邮件发送不成功,就再发一次
                send_email($orgData['email'], '酷客会签','社团'.$orgData['name'].'审核失败', $content);
            }
            $this->success('拒绝通过成功',U('User/index'));
        }else{
            $this->error('拒绝通过失败',U('User/index'));
        }
    }

    //社团审核内容查看
    public function content_verify(){
        $org_guid = I('get.org_guid');
        $org_model = D('Org');
        $org_info = $org_model->where(array('guid'=>$org_guid))->find();

        $area = D('Area');
        $area_1 = $area->field('name')->where(array('id'=>$org_info['areaid_1']))->find();
        $area_2 = $area->field('name')->where(array('id'=>$org_info['areaid_2']))->find();

        $this->assign('area_1',$area_1);
        $this->assign('area_2',$area_2);
        $this->assign('org_info',$org_info);
        $this->display();
    }
}
