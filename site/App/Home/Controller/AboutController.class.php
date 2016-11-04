<?php
namespace Home\Controller;

use Home\Controller\BaseController;
use 	  Think\Controller;
/**
 * 关于我们
 *
 * CT 2015-08-24 16:00 by zyz
 */
class AboutController extends BaseController{

	public function __construct(){
        parent::__construct($login = false);
        layout('layout_bottom');
    }

	public function about()
	{
		$this->title    = L('_ABOUT_US_');
		$auth = $this->get_auth_session();
        $this->assign('auth', $auth);
		$this->show();
	}
	public function recruiting()
	{
		$this->title    = L('_ADVICE_MAN_');
		$auth = $this->get_auth_session();
        $this->assign('auth', $auth);
		$this->show();
	}
	public function contact()
	{
		$this->title    = L('_CONTACT_US_');
		$auth = $this->get_auth_session();
        $this->assign('auth', $auth);
		$this->show();
	}

	public function feedback()
	{	
		$this->main = '/Public/meetelf/home/js/build/feedback.js';
		$this->title    = L('_ADVICE_US_');
		$auth = $this->get_auth_session();
        $this->assign('auth', $auth);
		$this->show();
	}
	public function opinion()
	{
		$auth = $this->get_auth_session();
		$email = I('post.email');
		$content = I('post.content');
		$time    = time();
		$opinion_info = array(
						'guid'       => create_guid(),
						'user_guid'  => $auth['guid'],
						'mobile'     => $auth['mobile'],
						'real_name'  => $auth['realname'],
						'email'      => $email,
						'content'    => $content,
						'updated_at' => $time,
						'created_at' => $time,
					);
				if (D('Opinion')->add($opinion_info)) {
					$this->success(L('_SEND_SUCCESS_'));
				}else{
					$this->error(L('_CREATE_ERROR_'));
				}


	}
}
