<?php 
namespace  Common\Logic;

/**
 * 微信登录 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 
class WeixinLogic{

	public $errors = array();


	public function get_code($state = '', $appid = '', $redirect_uri = '', $response_type = 'code', $scope = 'snsapi_login'){
		$appid           = $appid         ? $appid : C('WEIXIN_APPID');			
		$redirect_uri    = $redirect_uri  ? urlencode($redirect_uri) : urlencode(C('WEIXIN_REDIRECT_URI'));
		$response_type   = $response_type ? $response_type : C('WEIXIN_RESPONSE_TYPE'); 
		$scope           = $scope         ? $scope : C('WEIXIN_SCOPE');
		$state           = $state         ? $state : '';
		$query           = "appid={$appid}&redirect_uri={$redirect_uri}&response_type={$response_type}&scope={$scope}";
		if($state){
			$query      .= "&state={$state}";
		}
		$request_uri     = C('WEIXIN_CODE_URI') . $query; 
		ob_start();
		ob_end_clean();
		header("Location: {$request_uri}");
		exit();
	}

	public function get_access_info($code, $appid = '', $appsecret = '', $grant_type = 'authorization_code'){
		if(!$code){
			return false;
		}	
		$appid      = $appid      ? $appid      : C('WEIXIN_APPID');			
		$appsecret  = $appsecret  ? $appsecret  : C('WEIXIN_APPSECRET');
		$grant_type = $grant_type ? $grant_type : 'authorization_code';
		$query = "appid={$appid}&secret={$appsecret}&grant_type={$grant_type}&code={$code}";
		$request_uri = C('WEIXIN_TOKEN_URI') . $query;
		vendor('Curl.Curl');
		$curl = new \Curl();
		$res  = $curl->getRequest($request_uri);
		return json_decode($res[$request_uri], true);
	}

	public function get_userinfo($token, $openid){
		if(!$token || !$openid){
			return false;
		}	
		$query = "access_token={$token}&openid={$openid}";
		$request_uri = C('WEIXIN_USERINFO_URI') . $query;
		vendor('Curl.Curl');
		$curl = new \Curl();
		$res  = $curl->getRequest($request_uri);
		return json_decode($res[$request_uri], true);
	}

	public function create_weixin_user($info, $guid = ''){
		if(!$info){
			return false;
		}
		$time = time();
		$user = array(
			'third_id'  => $info['openid'],
			'nickname'  => validate_data($info, 'nickname', ''),	
			'sex'       => validate_data($info, 'sex', 0),
			'imgurl'    => validate_data($info, 'headimgurl', ''),
			'unionid'   => $info['unionid'],
			'country'   => validate_data($info, 'country', ''),
			'province'  => validate_data($info, 'province', ''),
			'city'      => validate_data($info, 'city', ''),
			'status'    => C('default_ok_status'),
			'user_guid' => $info['user_guid'],
			'type'      => C('user.third_type_weixin'),
			'updated_time' => $time,
		);
		if($guid){
			if($info['imgurl']){
				M('UserNew')->where(array('guid' => $guid))->save(array('photo' => $info['imgurl']));
			}
			return M('UserThirdInfo')->where(array('user_guid' => $guid))->save($user);
		}else{
			$user['guid'] = create_guid();
			$user['created_time'] = $time;
			return M('UserThirdInfo')->data($user)->add();
		}
	}
	
}
