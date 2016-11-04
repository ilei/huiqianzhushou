<?php
namespace Mobile\Controller;
use       Mobile\Controller\BaseController;

/**
 * 购买商品支付 
 * CT: 2015-05-11 17:50 by wangleiming
 */
class PayController extends BaseController{

    public function __construct(){
        parent::__construct();
    }
	/**
	 * 初始化相关类库
	 */
	public function _initialize()
	{
        $this->_check_weixin();
        header("Content-type:text/html;charset=utf-8");
		vendor('WapAlipay.CoreFunction');
		vendor('WapAlipay.RsaFunction');
		vendor('WapAlipay.Notify');
		vendor('WapAlipay.Submit');
	}
    public function _check_weixin(){
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false){
            $this->show('pay');die; 
        }
    }

	/**
	 * 充值页面 
	 *
	 * @access public 
	 * @param  void 
	 * @return void 
	 **/ 

	public function dopay(){
		$alipay_config = array_merge(C('WAP_ALIPAY'), C('WAP_ALIPAY_EXT'));
		$order_id = I('get.order_id');
		if(!$order_id || strlen(trim($order_id)) != C('order_length')){
			exit($this->error('参数错误!'));
		}
		$order = D('Order')->find_one(array('order_id' => trim($order_id)));
		if(!$order || $order['is_del'] == 1){
			exit($this->error('订单不存在!'));
		}
		if($order['status'] == 1){
			exit($this->error('订单已支付，请勿重复支付!'));
		}

		//检测库存
		$goods_guid = $order['goods_guid'];
		$goods = D('Goods', 'Logic')->get_goods($goods_guid);
		if(!$goods){
			exit($this->error('订单商品已售完!'));
		}
		if($goods['storage'] != -1 && (intval($goods['storage']) < intval($order['quantity']))){
			exit($this->error('商品库存不足!'));
		}
		$money = yuan_to_fen($order['total_price'], false);
		$update = array('version' => $order['version']+1, 'payment_time' => time());
		$res   = D('Order')->update(array('guid' => $order['guid'], 'version' => $order['version']), $update);
		if(!$res){
			exit($this->error('支付失败!'));
		}
        $session_order = array(
            'title' => $order['title'],
            'money' => $order['total_price'], 
            'order_id' => $order_id,
            'aguid' => $order['target_guid'],
        );
        session('session_order', $session_order);
        $param = array('out_trade_no' => $order_id, 'trade_no' => $order_id, 'total_fee' => yuan_to_fen($order['total_price'], false), 'trade_status' => 'TRADE_FINISHED');
		$this->doalipay($order_id, $money, $order);	
	}

    /**
     * 充值失败后重新充值
     *
     * @access public 
     * @param  void 
     * @return void
     **/ 

    public function repay(){
        $guid = I('get.guid');
        if(!$guid || strlen(trim($guid)) != 32){
            return false;
        }		
        $order = D('Order')->find_one(array('guid' => trim($guid)));
        $auth  = $this->get_auth_session();
        if(!$order || ($order['status'] == 1)){
            return false;
        }
        $money = yuan_to_fen($order['total_price'], false);
        $this->doalipay($order['order_id'], $money,$order);
        exit();
    }


	/**
	 * 请求支付宝 
	 *
	 * @access private 
	 * @param  string  $order_id 
	 * @param  int     $money    充值金额 
	 * @param  string  $org_guid  社团GUID 
	 * @return void 
	 **/ 

	private function doalipay($order_id, $money, $order){
		if(!$order_id || strlen($order_id) != C('order_length') || !is_numeric($money)){
			return false;		
		}

		$alipay_config = array_merge(C('WAP_ALIPAY'), C('WAP_ALIPAY_EXT'));

		$payment_type  = "1"; //支付类型

		//服务器异步通知页面路径, 需http://格式的完整路径，不能加?id=123这类自定义参数
		$notify_url    = $alipay_config['notify_url']; 

		//页面跳转同步通知页面路径, 需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
		$return_url    = $alipay_config['return_url']; 

		//$seller_email  = trim($alipay_config['seller_email']); // 卖家支付宝帐号

		//商户订单号 商户网站订单系统中唯一订单号，必填
		$out_trade_no  = trim($order_id); 
		$subject       = $order['title'];                          //订单名称
		$total_fee     = $money;       //付款金额
		$body          = $order['desc']; 				   //订单描述 

		//商品展示地址 需以http://开头的完整路径，例如：http://www.商户网址.com/myorder.html
		$show_url      = '';                  

		//客户端的IP地址, 非局域网的外网IP地址，如：221.0.0.1
		$exter_invoke_ip   = get_client_ip(); 

		$alipaySubmit      = new \AlipaySubmit($alipay_config);

		//防钓鱼时间戳, 若要使用请调用类文件submit中的query_timestamp函数
		$anti_phishing_key = $alipaySubmit->query_timestamp(); 
		/************************************************************/

		//构造要请求的参数数组，无需改动
		$parameter = array(
			"service"           => "alipay.wap.create.direct.pay.by.user",
			"partner" 			=> trim($alipay_config['partner']),
			"seller_id"         => trim($alipay_config['seller_id']),
			"payment_type"	    => $payment_type,
			"notify_url"	    => $notify_url,
			"return_url" 	    => $return_url,
			"out_trade_no"	    => $out_trade_no,
			"subject"	        => $subject,
			"total_fee"	        => $total_fee,
			"body"	            => $body,
			"show_url"	        => $show_url,
			"it_b_pay"	        => '',
			"extern_token"	    => '',
			"_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);

		//建立请求
		$html_text = $alipaySubmit->buildRequestForm($parameter,"post");
		echo $html_text;
	}

	/**
	 * 服务器异步通知页面方法
	 * CT: 2015-05-13 15:00 BY wangleiming 
	 */

	public function notify_url(){

		if(!IS_POST){
			return false;
		}
		$alipay_config = array_merge(C('WAP_ALIPAY'), C('WAP_ALIPAY_EXT'));

		//计算得出通知验证结果
		$alipayNotify  = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();

		if($verify_result) {//验证成功

			//获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
			$out_trade_no = validate_post('out_trade_no');      //商户订单号
			$trade_no     = validate_post('trade_no');          //支付宝交易号
			$trade_status = validate_post('trade_status');      //交易状态
			$total_fee    = validate_post('total_fee');         //交易金额
			$notify_id    = validate_post('notify_id');         //通知校验ID。
			$notify_time  = validate_post('notify_time');       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
			$buyer_email  = validate_post('buyer_email');       //买家支付宝帐号；

			$parameter = array(
				"out_trade_no"  => $out_trade_no, //商户订单编号；
				"trade_no"      => $trade_no,     //支付宝交易号；
				"total_fee"     => $total_fee,    //交易金额；
				"trade_status"  => $trade_status, //交易状态
				"notify_id"     => $notify_id,    //通知校验ID。
				"notify_time"   => $notify_time,  //通知的发送时间。
				"buyer_email"   => $buyer_email,  //买家支付宝帐号；
                'alipay_type'    => 1,
			);

			/**
			 * 交易目前所处的状态。
			 * 成功状态的值只有两个：
			 * TRADE_FINISHED（普通即时到账的交易成功状态）
			 * TRADE_SUCCESS（开通了高级即时到账或机票分销产品后的交易成功状态）
			 */

			if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
				$order = D('Order')->where(array('order_id' => $out_trade_no))->find();
				if(!$order){
					order_operation_log($out_trade_no, C('alipay_notify_record'), $parameter, '同步通知：交易失败，订单不存在');	
					echo "fail";
					$this->redirect($alipay_config['errorpage']);
					return false;
				}
                if($order['status'] == 1 && $order['alipay_type']){
                    $this->redirect($alipay_config['successpage']);
                    return true;
                }
				if(!($order['status'] == 0 || $order['status'] == 2) && !$order['alipay_type']){
					order_operation_log($out_trade_no, C('alipay_notify_record'), $parameter, '异步通知：交易失败，订单状态不正确,订单状态为:' . $order['status']);	
					echo "fail";
					return false;
				}
				if($order['total_price'] != yuan_to_fen($total_fee)){
					order_operation_log($out_trade_no, C('alipay_notify_record'), $parameter, '异步通知：交易失败，交易金额不一致');	
					echo "fail";
					return false;
				}
                $userinfo = M('ActivityUserinfo')->where(array('guid' => $order['buyer_guid']))->find();
                $ext = array('userinfo' => $userinfo);
				$logic = D('OrderPay', 'Logic');
				$res   = $logic->orderPaySuccess($out_trade_no, $total_fee, $parameter, $order, $ext);
				if($res){
					order_operation_log($out_trade_no, C('alipay_notify_record'), $parameter, '异步通知：交易成功');	
				}else{
					order_operation_log($out_trade_no, C('alipay_notify_record'), $parameter, '异步通知：交易失败,' . implode(',', $logic->errors));	
					echo "fail";
					return false;
				}
				echo "success";		//请不要修改或删除
			}else{
				order_operation_log($out_trade_no, C('alipay_notify_record'), $parameter, '异步通知：支付宝返回交易状态不正确');	
				echo "fail";
			}
		}else {
			//验证失败
			order_operation_log($out_trade_no, C('alipay_notify_record'), $verify_result, '异步通知：支付宝验证失败');	
			echo "fail";
		}
	}

	/**
	 * 页面跳转处理方法
	 * CT: 2015-05-13 15:00 BY wangleiming 
	 */
	public function return_url(){

		if(!$_GET || !validate_get('out_trade_no') || !validate_get('trade_no')){
			return false;
		}
		$alipay_config  = array_merge(C('WAP_ALIPAY'), C('WAP_ALIPAY_EXT'));
		$alipayNotify   = new \AlipayNotify($alipay_config);
		$verify_result  = $alipayNotify->verifyReturn();
		$out_trade_no   = validate_get('out_trade_no');      //商户订单号
		if($verify_result) {//验证成功

			//获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
			$trade_no       = validate_get('trade_no');          //支付宝交易号
			$trade_status   = validate_get('trade_status');      //交易状态
			$total_fee      = validate_get('total_fee');         //交易金额
			$notify_id      = validate_get('notify_id');         //通知校验ID。
			$notify_time    = validate_get('notify_time');       //通知的发送时间。
			$buyer_email    = validate_get('buyer_email');       //买家支付宝帐号；

			$parameter = array(
				"out_trade_no"   => $out_trade_no,      //商户订单编号；
				"trade_no"       => $trade_no,          //支付宝交易号；
				"total_fee"      => $total_fee,         //交易金额；
				"trade_status"   => $trade_status,      //交易状态
				"notify_id"      => $notify_id,         //通知校验ID。159EF47CB71F95E931D3056
				"notify_time"    => $notify_time,       //通知的发送时间。
				"buyer_email"    => $buyer_email,       //买家支付宝帐号
                'alipay_type'    => 2,
			);
			$order = D('Order')->where(array('order_id' => $out_trade_no))->find();
            $alipay_config['successpage'] = $alipay_config['successpage'] . '&guid=' . $order['guid'];
            $alipay_config['errorpage'] = $alipay_config['errorpage'] . '&guid=' . $order['guid'];
			if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
				if(!$order){
					order_operation_log($out_trade_no, C('alipay_return_record'), $parameter, '同步通知：交易失败，订单不存在');	
					$this->redirect($alipay_config['errorpage']);
					return false;
				}
                if($order['status'] == 1 && $order['alipay_type']){
                    $this->redirect($alipay_config['successpage']);
                    return true;
                }
				if(!($order['status'] == 0 || $order['status'] == 2) && !$order['alipay_type']){
					order_operation_log($out_trade_no, C('alipay_return_record'), $parameter, '同步通知：交易失败，订单状态不正确,订单状态为:' . $order['status']);	
					$this->redirect($alipay_config['errorpage']);
					return false;
				}
				if($order['total_price'] != yuan_to_fen($total_fee)){
					order_operation_log($out_trade_no, C('alipay_return_record'), $parameter, '同步通知：交易失败，交易金额不一致');	
					$this->redirect($alipay_config['errorpage']);
					return false;
				}
				$logic = D('OrderPay', 'Logic');
                $userinfo = M('ActivityUserinfo')->where(array('guid' => $order['buyer_guid']))->find();
                $ext = array('userinfo' => $userinfo);
				$res   = $logic->orderPaySuccess($out_trade_no, $total_fee, $parameter, $order, $ext);
				if($res){
					order_operation_log($out_trade_no, C('alipay_return_record'), $parameter, '同步通知：交易成功');	
					$this->redirect($alipay_config['successpage']);
					return true;
				}else{
					order_operation_log($out_trade_no, C('alipay_return_record'), $parameter, '同步通知：交易失败,' . implode(',', $logic->errors));	
					$this->redirect($alipay_config['errorpage']);
					return false;
				}
				$this->redirect($alipay_config['successpage']);
			}else {
				order_operation_log($out_trade_no, C('alipay_return_record'), $parameter, '同步通知：支付宝返回交易状态不正确');	
				$this->redirect($alipay_config['errorpage']);
			}
		}else {
			order_operation_log($out_trade_no, C('alipay_return_record'), $verify_result, '同步通知：支付宝验证失败');	
			$this->redirect($alipay_config['errorpage']);
		}
	}

}
