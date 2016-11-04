<?php
// +----------------------------------------------------------------------
// | Meetelf Framework [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011-2014 Meetelf Team (http://www.meetelf.com)
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author wangleiming <wangleiming@yunmai365.com>
// +----------------------------------------------------------------------

abstract class Adapter{
	/**
	 * 响应数据寄存器
	 *
	 * @var array
	 */
	protected $httpData = [ ];

	/**
	 * User Agent 浏览器的身份标识
	 *
	 * @var string
	 */
	protected $userAgent;

	/**
	 * 页面来源
	 *
	 * @var string
	 */
	protected $referer;

	/**
	 * 携带的Cookie
	 *
	 * @var string
	 */
	protected $cookie;
	protected $files = [ ];
	protected $hostIp;
	protected $header = [ ];
	protected $option = [ ];
	protected $timeout = 30;

	/**
	 * 待Post提交的数据
	 *
	 * @var array
	 */
	protected $postData = [ ];

	/**
	 * 多列队任务进程数，0表示不限制
	 *
	 * @var int
	 */
	protected $multiExecNum = 20;

	/**
	 * 默认请求方法
	 *
	 * @var string
	 */
	protected $method = 'GET';

	/**
	 * 默认连接超时时间，毫秒
	 *
	 * @var int
	 */
	protected $connectTimeout = 3000;
	protected $proxyHost;
	protected $proxyPort;
	protected $authorizationToken;

	/**
	 * 设置，获取REST的类型
	 *
	 * @access public 
	 * @param  string $method GET|POST|DELETE|PUT 等，不传则返回当前method
	 * @return string
	 * @return void 
	 **/

	public function setMethod($method = null){
		if (null === $method)
			return $this->method;
		$this->method = strtoupper ( $method );
	}

	/**
	 * 设置Header
	 *
	 * @access public 
	 * @param  array $header
	 * @return void 
	 **/

	public function setHeader($item, $value){
		$this->header = array_merge ( $this->header, [
				$item . ": " . $value
		] );
	}

	/**
	 * 设置Header
	 *
	 * @access public 
	 * @param  array $header
	 * @return void 
	 **/

	public function setHeaders($headers){
		$this->header = array_merge ( $this->header, ( array ) $headers );
	}

	/**
	 * 设置代理服务器访问
	 *
	 * @access public 
	 * @param  string $host
	 * @param  string $port
	 * @return void 
	 **/

	public function setHttpProxy($host, $port){
		$this->proxyHost = $host;
		$this->proxyPort = $port;
	}

	/**
	 * 设置IP
	 *
	 * @access public 
	 * @param  string $ip
	 * @return void 
	 **/

	public function setHostIp($ip){
		$this->hostIp = $ip;
	}

	/**
	 * 设置User Agent
	 *
	 * @access public 
	 * @param  string $userAgent
	 * @return void 
	 **/

	public function setUserAgent($userAgent){
		$this->userAgent = $userAgent;
	}

	/**
	 * 设置Http Referer
	 *
	 * @access public
	 * @param  string $referer
	 * @return void 
	 **/

	public function setReferer($referer){
		$this->referer = $referer;
	}

	/**
	 * 设置Cookie
	 *
	 * @access public 
	 * @param  string $cookie
	 * @return void 
	 **/

	public function setCookie($cookie){
		$this->cookie = $cookie;
	}

	/**
	 * 设置多个列队默认排队数上限
	 *
	 * @access public 
	 * @param  int $num
	 * @return void 
	 **/

	public function setMultiMaxNum($num = 0){
		$this->multiExecNum = ( int ) $num;
	}

	/**
	 * 设置超时时间
	 *
	 * @access public 
	 * @param  int $timeoutp
	 * @return void 
	 **/

	public function setTimeout($timeout){
		$this->timeout = $timeout;
	}

	/**
	 * 重置设置
	 **/

	public function reset(){
		$this->option = [ ];
		$this->header = [ ];
		$this->hostIp = null;
		$this->files = [ ];
		$this->cookie = null;
		$this->referer = null;
		$this->method = 'GET';
		$this->postData = [ ];
	}

	/**
	 * 获取结果数据
	 **/

	public function getResutData(){
		return $this->httpData;
	}

	abstract public function setAuthorization($username, $password);
	abstract public function getRequest($url);
	abstract public function postRequest($url, $vars);
	abstract public function putRequest($url, $vars);
	abstract public function deleteRequest($url);
}
