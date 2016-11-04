<?php
namespace Home\Controller;

/**
 * 购买控制器 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/

class BuyController extends BaseController{

    public function __construct(){
        parent::__construct();	
        layout('layout_new');
        vendor('Breadcrumb.Breadcrumb');
        $this->bread = new \Breadcrumb();
        $this->bread->append_crumb(L('_HOME_TITLE_'), U('Home/Index/index'));
        $this->bread->append_crumb(L('_MY_WALLET_TITLE_'), U('Home/User/wallet'));
    }

    /**
     * 购买短信包页面 
     *
     * @access public 
     * @param  void 
     * @return void 
     **/ 

    public function buy_email_msg(){
        //header("Content-type:text/html;charset=utf-8");
        //exit('由于支付宝接口域名问题，暂不支持充值。不会影响您的使用');
        $this->title = L('_BUY_MSG_TITLE_'); 
        $this->css[] = 'meetelf/css/release.css';
        $this->main  = '/Public/meetelf/home/js/build/buy.buy_email.js';
        $this->bread->append_crumb($this->title);
        $cond  = array('type' => C('own_goods.discount_type'), 'status' => C('own_goods.ok'), 'is_del' => C('own_goods.ok'));
        $goods = M('OwnGoods')->where($cond)->select();
        $guids = array_columns($goods, 'guid', 'id');
        $cond  = array('goods_guid' => array('IN', array_unique($guids)), 'status' => C('own_goods.ok'));
        $exts  = M('OwnGoodsExt')->where($cond)->select();
        $exts  = array_columns($exts, null, 'goods_guid');
        foreach($goods as $key => $value){
            $goods[$key]['nums'] = $exts[$value['guid']]['nums'];
        }
        $this->assign('breadcrumb', $this->bread->output());
        $this->assign('goods', $goods);
        $this->show();
    }

    public function buy_post(){
        if(IS_AJAX){
            $this->ajax_request_limit('buy:msg:post', 1, 10);
            $msg_guid   = (($tmp_1 = trim(I('post.guid')[1])) && strlen($tmp_1) == 32) ? $tmp_1 : 0; 
            $email_guid = (($tmp_2 = trim(I('post.guid')[2])) && strlen($tmp_2) == 32) ? $tmp_2 : 0; 
            if($msg_guid){
                $msg = M('OwnGoods')->where(array('guid' => $msg_guid))->find(); 
                $msg_guid = $msg['category'] == 1 ? $msg_guid : 0;
            }
            if($email_guid){
                $email = M('OwnGoods')->where(array('guid' => $email_guid))->find(); 
                $email_guid = $email['category'] == 2 ? $email_guid : 0;
            }
            $guids = array_unique(array_filter(array($msg_guid, $email_guid)));
            if(!$guids){
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_GOODS_NOT_EMPTY_')));
            }
            $auth  = $this->get_auth_session();
            $order = array(
                'goods_guid' => $guids, 
                'buyer_guid' => $auth['guid'],
            );
            if(I('post.guid')[1] && I('post.guid')[2]){
                $order['title'] = $msg['name'] . '和' . $email['name'];//L('_BUY_EMAIL_MSG_'); 
            }elseif(I('post.guid')[1]){
                $order['title'] = $msg['name'];//L('_BUY_MSG_'); 
            }elseif(I('post.guid')[2]){
                $order['title'] = $email['name'];//L('_BUY_EMAIL_'); 
            }
            $logic = D('OwnOrder', 'Logic');
            $order_id = $logic->add($order);
            if($order_id){
                $session_order = array(
                    'title' => $order['title'],
                    'money' => $logic->money, 
                    'order_id' => $order_id,
                    'msg'      => true,
                );
                session('session_order', $session_order);
                $this->ajax_return(array('status' => C('ajax_success'), 'url' => U('Home/Paymsg/dopay/', array('order_id' => $order_id))));
            }else{
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            }
        } 
        exit();
    }
}
