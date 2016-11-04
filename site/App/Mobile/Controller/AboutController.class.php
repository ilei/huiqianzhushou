<?php
namespace Mobile\Controller;
use Think\Controller;

class AboutController extends BaseController
{
	/**
	 * 关于我们页面展示 
	 *
	 * UT 2015-06-10 10:00 by wangleiming 
	 **/ 
    //关于我们首页
    public function index(){
        $external_version = $_GET['v'];
        $this->assign('external_version',$external_version);
        $this->display();
    }

    /**
     * 条款
     */
    public function terms()
    {
        $this->display();
    }

}
