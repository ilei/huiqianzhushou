<?php
namespace Org\Api;
/**
 * 亿美短信服务
 * @author  qixinlei
 */
class YmSMS{
    const API_URL_PREFIX = 'http://sdkhttp.eucp.b2m.cn/sdkproxy/'; //接口API地址前缀
    const REGIST_URL = 'regist.action'; //序列号注册的Http接口地址
    const REGIST_DETAIL_INFO_URL = 'registdetailinfo.action'; //注册企业信息的Http接口地址
    const LOGOUT_URL = 'logout.action'; //注销序列号的Http接口地址
    const SENDSMS_URL = 'sendsms.action'; //同步发送即时短信的Http接口地址
    const SENDTIMESMS_URL = 'sendtimesms.action'; //发送定时短信的Http接口地址
    const GETMO_URL = 'getmo.action'; //获取上行短信的Http接口地址
    const QUERY_BALANCE_URL = 'querybalance.action'; //查询余额的Http接口地址
    const CHARGEUP_URL = 'chargeup.action'; //充值操作的Http接口地址
    const CHANGEPASSWORD_URL = 'changepassword.action'; //修改密码的Http接口地址
    const GETREPORT_URL = 'getreport.action'; //获取状态报告的Http接口地址
    
    private $_cdkey = '3SDK-EMY-0130-JJUOT';//序列号序列号 特服号546797
    private $_password = '409645'; //用户密码
	private $_param = array(); //初始化接口参数
    private $_callbackStr = array(
                                '-2'=>'客户端异常',
                                '-9000'=>'数据格式错误,数据超出数据库允许范围',
                                '-9001'=>'序列号格式错误',
                                '-9002'=>'密码格式错误',
                                '-9003'=>'客户端Key格式错误',
                                '-9004'=>'设置转发格式错误',
                                '-9005'=>'公司地址格式错误',
                                '-9006'=>'企业中文名格式错误',
                                '-9007'=>'企业中文名简称格式错误',
                                '-9008'=>'邮件地址格式错误',
                                '-9009'=>'企业英文名格式错误',
                                '-9010'=>'企业英文名简称格式错误',
                                '-9011'=>'传真格式错误',
                                '-9012'=>'联系人格式错误',
                                '-9013'=>'联系电话格式错误',
                                '-9014'=>'邮编格式错误',
                                '-9015'=>'新密码格式错误',
                                '-9016'=>'发送短信包大小超出范围',
                                '-9017'=>'发送短信内容格式错误',
                                '-9018'=>'发送短信扩展号格式错误',
                                '-9019'=>'发送短信优先级格式错误',
                                '-9020'=>'发送短信手机号格式错误',
                                '-9021'=>'发送短信定时时间格式错误',
                                '-9022'=>'发送短信唯一序列值错误',
                                '-9023'=>'充值卡号格式错误',
                                '-9024'=>'充值密码格式错误',
								'0'=>'成功',
								'-1'=>'系统异常',
								'-101'=>'命令不被支持',
								'-102'=>'RegistryTransInfo删除信息失败',
								'-103'=>'RegistryInfo更新信息失败',
								'-104'=>'请求超过限制',
								'-111'=>'企业注册失败',
								'-117'=>'发送短信失败',
								'-118'=>'接收MO失败',
								'-119'=>'接收Report失败',
								'-120'=>'修改密码失败',
								'-122'=>'号码注销激活失败',
								'-110'=>'号码注册激活失败',
								'-123'=>'查询单价失败',
								'-124'=>'查询余额失败',
								'-125'=>'设置MO转发失败',
								'-126'=>'路由信息失败',
								'-127'=>'计费失败0余额',
                                '-128'=>'计费失败余额不足',
                                '-1100'=>'序列号错误,序列号不存在内存中,或尝试攻击的用户',
                                '-1103'=>'序列号Key错误',
                                '-1102'=>'序列号密码错误',
                                '-1104'=>'路由失败，请联系系统管理员',
                                '-1105'=>'注册号状态异常, 未用 1',
                                '-1107'=>'注册号状态异常, 停用 3',
                                '-1108'=>'注册号状态异常, 停止 5',
                                '-113'=>'充值失败',
                                '-1131'=>'充值卡无效',
                                '-1132'=>'充值密码无效',
                                '-1133'=>'充值卡绑定异常',
                                '-1134'=>'充值状态无效',
                                '-1135'=>'充值金额无效',
                                '-190'=>'数据操作失败',
                                '-1901'=>'数据库插入操作失败',
                                '-1902'=>'数据库更新操作失败',
                                '-1903'=>'数据库删除操作失败',
							   );
							   
							   
	public function __construct(){
        $this->_cdkey = C('YMSMS.cdkey');
        $this->_password = C('YMSMS.password');
		$this->_param['cdkey'] = $this->_cdkey;
		$this->_param['password'] = $this->_password;
	}
	
    /**
     * POST 请求
     * 序列号注册
	 * @return Array 
     */
    public function regist(){
        $result=$this->http_post(self::API_URL_PREFIX.self::REGIST_URL, $this->_param);
        $xml = (array)simplexml_load_string(trim($result));
		return $this->callback($xml['error']);
    }

	/**
     * POST 请求
     * 注册企业信息
 	 * @param ename String 企业名称(必填)
     * @param linkman String 联系人姓名(必填)
     * @param phonenum String 联系电话(必填)
     * @param mobile String 联系手机(必填)
     * @param email String 电子邮件(必填)
     * @param fax String 联系传真(必填)
     * @param address String 公司地址(必填)
     * @param postcode String 邮政编码(必填)
	 * @return Array
     */
    public function registdetailinfo($ename, $linkman, $phonenum, $mobile, $email, $fax, $address, $postcode){
		$this->_param['ename'] = $ename;
		$this->_param['linkman'] = $linkman;
		$this->_param['phonenum'] = $phonenum;
		$this->_param['mobile'] = $mobile;
		$this->_param['email'] = $email;
		$this->_param['fax'] = $fax;
		$this->_param['address'] = $address;
		$this->_param['postcode'] = $postcode;
        $result=$this->http_post(self::API_URL_PREFIX.self::REGIST_DETAIL_INFO_URL, $this->_param);
        $xml = (array)simplexml_load_string(trim($result));
		return $this->callback($xml['error']);
    }
	
    
    /**
     * POST 请求
     * 序列号注销
 	 * @return Array 
     */
    public function logout(){
        $result=$this->http_post(self::API_URL_PREFIX.self::LOGOUT, $this->_param);
        $xml = (array)simplexml_load_string(trim($result));
        return $this->callback($xml['error']);
    }
	
	/**
     * POST 请求
     * 及时发送短信
     * @param phone String 手机号码(必填)
     * @param message String 短信内容(必填)
     * @param addserial String 附加号(非必填)
     * @param smspriority String 短信优先级1-5(必填)
     * @param seqid String 长整型值企业内部必须保持唯一，获取状态报告使用(必填)
	 * @return Array
     */
    public function sendsms($phone, $message){
		$this->_param['phone'] = $phone;
		$this->_param['message'] = $message;
        $result=$this->http_post(self::API_URL_PREFIX.self::SENDSMS_URL, $this->_param);
        $xml = (array)simplexml_load_string(trim($result));
		return $this->callback($xml['error']);
    }

	/**
     * POST 请求
     * 短信剩余条数
	 * @return Array
     */
	public function querybalance(){
        $result=$this->http_post(self::API_URL_PREFIX.self::QUERY_BALANCE_URL, $this->_param);
        $xml = (array)simplexml_load_string(trim($result));
		return $xml['error'] == '0' ? array('code'=>$xml['error'], 'Message'=>$xml['message']*10) : $this->callback($xml['error']);
    }
    
    /**
     * POST 请求
     * 获取状态报告
	 * @return Array
     */
    public function getreport(){
        $result=$this->http_post(self::API_URL_PREFIX.self::GETREPORT_URL, $this->_param);
        $xml = (array)simplexml_load_string(trim($result));
        return $this->callback($xml['error']);
    }
    
    /**
     * POST 请求
     * 修改密码
     * @param password String 用户原密码(必填)
     * @param newpassword String 用户新密码(必填) 
 	 * @return Array
     */
    public function changepassword($password, $newpassword){
        $this->_param['password'] = $password;
        $this->_param['newPassword'] = $newpassword;
        $result=$this->http_post(self::API_URL_PREFIX.self::CHANGEPASSWORD_URL, $this->_param);
        $xml = (array)simplexml_load_string(trim($result));
        return $this->callback($xml['error']);
    }
    
    /**
     * POST 请求
     * 账户充值
     * @param cardno String 充值卡卡号(必填)
     * @param $cardpass String 充值卡密码(必填)
 	 * @return Array
     */
    public function chargeup($cardno, $cardpass){
        $this->_param['cardno'] = $cardno;
        $this->_param['cardpass'] = $cardpass;
        $result=$this->http_post(self::API_URL_PREFIX.self::CHARGEUP_URL, $this->_param);
        $xml = (array)simplexml_load_string(trim($result));
        return $this->callback($xml['error']);
    }
	
    /**
     * 合成返回值
	 * @return Array
     */
    public function callback($code){
		$callbackArray = $this->_callbackStr;
        return array('code'=>$code, 'Message'=>$callbackArray[$code]);
	}
	
    /**
     * GET 请求
     * @param string $url
     */
    private function http_get($url){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    
    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    private function http_post($url,$param){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
}