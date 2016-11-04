<?php
namespace Home\Controller;

use Home\Controller\BaseController;

/**
 * 支付成功或失败返回页面展示
 * @author wangleiming<wangleiming@yunmai365.com> 
 **/
class InfoController extends BaseController{

    public function __construct(){
        parent::__construct();	
        layout('layout_new');
        vendor('Breadcrumb.Breadcrumb');
        $this->bread = new \Breadcrumb();
        $this->bread->append_crumb(L('_HOME_TITLE_'), U('Home/Index/index'));
        $this->bread->append_crumb(L('_MY_WALLET_TITLE_'), U('Home/User/wallet'));
    }

    public function myorder(){
        $this->css[] = 'meetelf/css/activity_preview.css';
        $this->main  = '/Public/meetelf/home/js/build/home.information.order.js';
        $status = intval(I('get.status'));
        $order  = session('session_order');
        if(!$order){
            $this->redirect(U('Home/User/wallet'));
        }
        $this->assign('order', $order);
        if(is_mobile_request()){
            $url = U('Mobile/Index/order', array('status' => $status), true, true,false);
           $this->redirect($url);  
        }
        session('session_order', null);
        if($status == 1){
            $this->title = L('_PAY_SUCCESS_');
            $this->bread->append_crumb($this->title);
            $this->assign('breadcrumb', $this->bread->output());
            $this->assign('msg', $order['msg']);
            $this->show('success');
        
        }else{
            $this->title = L('_PAY_FAILED_');
            $this->bread->append_crumb($this->title);
            $this->assign('breadcrumb', $this->bread->output());
            $this->assign('msg', $order['msg']);
            $this->show('error');
        }
        exit();
    }


    /**
     * 充值记录展示
     *
     * @access public
     * @param  void
     * @return void
     **/

    public function recharge()
    {
        $session_auth = $this->get_auth_session();
        $this->assign('meta_title', '充值记录');
        $num_per_page = C('NUM_PER_PAGE', null, '10');
        $list         = D('RechargeRecord')->alias('g')
            ->where(array('account_guid' => $session_auth['org_guid']))
            ->order(array('created_time' => 'DESC'))
            ->page(I('get.p', '1') . ',' . $num_per_page)
            ->select();
        // 查询满足要求的总记录数
        $count = D('RechargeRecord')->alias('g')->where(array('account_guid' => $auth['org_guid']))->count();
        // 使用page类,实现分类
        $page  = new \Think\Page($count, $num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $pager = $page->show();// 分页显示输出

        $render = array(
            'list' => $list,
            'page' => $pager,
        );
        $this->assign($render);
        $this->display();
    }

    public function recharge_info()
    {
        $auth = $this->get_auth_session();
        $this->assign('meta_title', '充值记录详情');
        $guid  = I('get.guid');
        $order = D('RechargeRecord')->find_one(array('guid' => trim($guid), 'account_guid' => $auth['org_guid']));
        if (!$order) {
            header('HTTP/1.0 404 Not Found');
            $this->display('Common@Tpl/404');
            exit();
        }
        $this->assign(array(
            'info' => $order,
            'auth' => $auth,
        ));
        $this->display();
    }
}
