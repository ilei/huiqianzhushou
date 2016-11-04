<?php
namespace Mobile\Controller;

use Think\Controller;

class IndexController extends BaseController
{
    public function __construct()
    {
        parent::__construct(false);
    }

    /**
     * 首页展示
     */
    public function index()
    {
        $redirect = urldecode($_COOKIE["redirect"]);
        $this->assign('redirect', $redirect);
        $this->display();
    }

    public function order(){
        layout('layout');
        $status = intval(I('get.status'));
        $order  = I('get.guid');
        $order  = M('Order')->where(array('guid' => trim($order)))->find();
        if(!$order){
            $this->redirect(U('Mobile/Index/index'));
        }
        $this->assign('order', $order);
        if($status == 1){
            $this->title = '支付成功';
            $this->show('success');
        
        }else{
            $this->title = '支付失败';
            $this->show('error');
        }
        exit();
    }
}
