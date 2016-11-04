<?php
namespace Home\Controller;
use 	  Think\Controller;

/**
 * 基础控制器
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/

class RestController extends Controller{

    //要加载的js文件
    public $module      = array();

    //加载优化过的js文件
    public  $main       =array();

    //要单独加载的css文件 
    protected $css      = array();

    //keywords  seo优化
    protected $keywords = array();

    //网站描述
    protected $desc     = null;

    //网站title
    public $title       = null;


    public function __construct($login = true){
        parent::__construct();
        if($login){
            $this->check_login();
        }
    }

    /**
     * 空方法
     *
     * @access  public
     * @param   void
     * @return  void
     **/

    public function _empty(){
        header('HTTP/1.0 404 Not Found');
        $this->display('Common@Tpl/404');
    }

    /**
     * 获取session值
     *
     * @access public
     * @param  string $key 单独获取某个值
     * @return mixed
     **/

    public function get_auth_session($key = ''){
        $userInfo = session('auth');
        if($userInfo){
            $account  = M('UserAccount')->where(array('account_guid' => $userInfo['guid']))->find();
            $userInfo['msg_nums'] = $account['msg_nums'];
            $userInfo['email_nums'] = $account['email_nums'];
        }
        return $key ? $userInfo[$key] : $userInfo;
    }

    /**
     *
     * 设置session值
     *
     * @access public
     * @param  mixed  $data
     * @return void
     **/

    public function set_auth_session($data){
        session('auth', $data);
    }


    /**
     *
     * 扩展后的展示页面的方法
     *
     * @access public
     * @param  string $html
     * @return void
     **/

    public function show($html = ''){
        if(!$this->main){
            $this->main = '/Public/meetelf/home/js/home.index.index.js';
        }
        $this->main = substr(str_replace('/build','', $this->main),0, -3);
        if(APP_DEBUG){
            if($this->main){
                $name = substr($this->main, strrpos($this->main, '/')+1);
                $path = substr($this->main, 0, strrpos($this->main, '/'));
                $this->main = $path . '/min/' . $name;
            }
            foreach($this->css as $key => $value){
                $this->css[$key] = substr($value, 0, -4) . '.min.css';
            }
        }else{
            $this->module[] = $this->main . '.js';
            $this->main = '/Public/common/js/main';

        }
        $this->assign(
            array(
                'module'   => $this->module,
                'main'     => $this->main,
                'title'    => $this->title,
                'auth'     => $this->get_login_user(),
                'css'      => $this->css,
                'debug'    => APP_DEBUG,
                'keywords' => $this->keywords,
                'desc'     => $this->desc,
            )
        );
        $this->display($html);
    }

    /**
     * 微信号登录网站后的操作
     *
     * @access public
     * @param  array  微信用户信息
     * @return boolean
     **/

    public function weixin_after_login($user){
        $openid = $user['openid'];

        $img = $user['headimgurl'] ? download_img($user['headimgurl']) : '';
        //检查是否是注册用户 
        $exist = M('UserNew')->where(array('weixin' => $openid))->find();
        $logic = D('Weixin', 'Logic');
        if(!$exist){
            $data= array(
                'guid'       => create_guid(),
                'weixin'     => $openid,
                'created_at' => time(),
                'updated_at' => time(),
                'status'     => C('default_ok_status'),
                'photo'      => $img,
            );
            $res = M('UserNew')->data($data)->add();
            if($res){
                $user['user_guid'] = $data['guid'];
                $res= $logic->create_weixin_user($user);
            }
        }else{
            $user['user_guid'] = $exist['guid'];
            $user['imgurl']    = $img;
            $res= $logic->create_weixin_user($user, $exist['guid']);
        }
        if($res){
            // 存储相关session
            $auth = array(
                'guid'     => $user['user_guid'],
                'email'    => '',
                'mobile'   => '',
                'vip'      => '',
                'nickname' => $user['nickname'],
                'photo'    => $img,
            );
            session('auth', $auth);
        }
        return $res;
    }

    /**
     * 获取上一页面URL
     *
     * @access public
     * @param  void
     * @return string | ''
     *
     **/

    public function getReferer(){
        return $_SERVER['HTTP_REFERER'];
    }

    /**
     * 登陆后操作
     *
     * @access protected
     * @param  array     $userInfo
     * @param  bool      $remember 是否记住密码
     * @return void
     *
     **/

    protected function opration_after_login($userInfo, $remember = false){
        if ($remember) {
            $token  = md5(uniqid(rand(), TRUE));
            $expire = time() + C('REMEMBER_EXPIRE', null, '2592000');
            $ip     = get_client_ip();
            // 保存cookie
            setcookie(C('REMEMBER_KEY'), $token.':'.$userInfo['guid'].':'.md5($ip), $expire, '/');
            // 保存cookie信息到数据库
            $data   = array('auto_token' => $token, 'auto_login' => 1);
            M('User')->where(array('guid' => $userInfo['guid']))->data($data)->save();
        }
        // 存储相关session
        $auth = array(
            'guid'   => $userInfo['guid'],
            'email'  => $userInfo['email'],
            'mobile' => $userInfo['mobile'],
            'vip'    => $userInfo['vip'],
        );
        $userAttr = M('UserAttrInfo')->where("user_guid = '{$userInfo['guid']}'")->find();
        $account  = M('UserAccount')->where(array('account_guid' => $userInfo['guid']))->find();
        $auth['nickname']   = validate_data($userAttr, 'nickname', '');
        $auth['photo']      = validate_data($userAttr, 'photo', '');
        $auth['realname']   = validate_data($userAttr, 'realname', '');
        $auth['msg_nums']   = validate_data($account, 'msg_nums', 0);
        $auth['balance']    = yuan_to_fen($account['balance'], false);
        $auth['email_nums'] = validate_data($account, 'email_nums', 0);
        session('auth', $auth);
        return ;
    }


    /**
     *
     * ajax返回json数据
     *
     * @access public
     * @param  array $data
     * @return json
     **/

    public function ajax_return($data = array()){
        exit(json_encode($data));
    }

    /**
     *
     * 获取地区信息
     * 	根据地区信息ID获取子类地区信息
     *
     * @access public
     * @param  void
     * @return html
     **/

    public function ajax_get_area(){
        if(IS_AJAX){
            $areaid = I('post.id');
            if ($areaid < 1) {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            }
            $res = D('Area')->find_all('parent_id="' . intval($areaid) . '"', 'id, name');
            if (!empty($res)) {
                $this->assign('area', $res);
                $msg = $this->fetch('Act:area');
                $this->ajax_return(array('status' => C('ajax_success'), 'msg' => $msg));
            } else {
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            }
        }
        exit();
    }

    /**
     * 检查用户是否已登陆
     *
     * @access public
     * @param  void
     * @return void
     *
     **/

    public function check_login(){
        $session_auth = $this->get_auth_session();
        if(CONTROLLER_NAME == 'Auth' || $this->_filter_login()){
            return true;
            exit;
        }
        if (empty($session_auth)){
            if (!isset($_COOKIE[C('REMEMBER_KEY')])) {
                $this->redirect('Auth/login');
            }
            list($token, $userGuid, $ip) = explode(':', $_COOKIE[C('REMEMBER_KEY')]);
            $condition = array('guid' => $userGuid, 'auto_token' => $token, 'auto_login' => 1);
            $res = M('User')->where($condition)->find();
            if (!$res) {
                $this->redirect('Auth/login');
            }
            $this->opration_after_login($res);
            return ;
        }
    }

    /**
     * 限制ajax请求次数
     *
     * @access public
     * @param  string  $key
     * @return mixed
     *
     **/

    public function ajax_request_limit($key, $nums = 3, $expire = 10, $ajax = true){
        $key = md5($key . $_COOKIE['__permanent_id']);
        if(!request_nums_limit($key, $nums, $expire)){
            if($ajax){
                $this->ajax_return(array('status' => C('ajax_failed'), 'msg' => L('_REQUEST_TOO_MUCH_')));
            }else{
                return false;
            }
        }
        return true;
    }

    /**
     * 返回登录用户信息
     *
     * @access public
     * @param  void
     * @return array
     **/

    public function get_login_user(){
        $auth = $this->get_auth_session();
        if(!$auth && isset($_COOKIE[C('REMEMBER_KEY')]) && $_COOKIE[C('REMEMBER_KEY')]) {
            list($token, $userGuid, $ip) = explode(':', $_COOKIE[C('REMEMBER_KEY')]);
            $condition = array('guid' => $userGuid, 'auto_token' => $token, 'auto_login' => 1);
            $userInfo  = M('User')->where($condition)->find();
            if(!$userInfo){
                return false;
            }
            // 存储相关session
            $auth = array(
                'guid'   => $userInfo['guid'],
                'email'  => $userInfo['email'],
                'mobile' => $userInfo['mobile'],
                'vip'    => $userInfo['vip'],
            );
            $userAttr = M('UserAttrInfo')->where("user_guid = '{$userInfo['guid']}'")->find();
            $account  = M('UserAccount')->where(array('account_guid' => $userInfo['guid']))->find();
            $auth['nickname']   = validate_data($userAttr, 'nickname', '');
            $auth['photo']      = validate_data($userAttr, 'photo', '');
            $auth['realname']   = validate_data($userAttr, 'realname', '');
            $auth['msg_nums']   = validate_data($account, 'msg_nums', 0);
            $auth['balance']    = yuan_to_fen($account['balance'], false);
            $auth['email_nums'] = validate_data($account, 'email_nums', 0);
            session('auth', $auth);
        }
        return $auth;
    }

    private function _filter_login(){
        $controller = array('paymsg', 'payment', 'signup');
        $action     = array('notify_url', 'add_signup', 'post_signup');
        if(in_array(strtolower(CONTROLLER_NAME), $controller) && in_array(strtolower(ACTION_NAME), $action)){
            return true;
        }
        return false;
    }

    /**
     * 错误输出
     *
     * CT: 2014-09-19 17:00 by YLX
     * UT: 2014-09-23 14:00 by YLX
     *
     */
    public function output_error($code, $msg=null, $data=null)
    {
        $data = array('code'=>$code, 'msg'=>$msg, 'data'=>$data);
        return $this->response($data, 'json');
    }

    /**
     * 数据输出
     *
     * CT: 2014-09-19 17:00 by YLX
     * UT: 2014-09-23 14:00 by YLX
     */
    public function output_data($data = null,$msg=null)
    {
        $data = array('code'=>10000, 'msg'=>$msg, 'data' => $data);
        return $this->response($data, 'json');
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @param integer $code HTTP状态
     * @return void
     */
    protected function response($data,$type='json',$code=200) {
        $this->sendHttpStatus($code);
        exit($this->encodeData($data,strtolower($type)));
    }

    // 发送Http状态信息
    protected function sendHttpStatus($code) {
        static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );
        if(isset($_status[$code])) {
            header('HTTP/1.1 '.$code.' '.$_status[$code]);
            // 确保FastCGI模式下正常
            header('Status:'.$code.' '.$_status[$code]);
        }
    }

    /**
     * 编码数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @return string
     */
    protected function encodeData($data,$type='') {
        if(empty($data))  return '';
        if('json' == $type) {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data);
        }elseif('xml' == $type){
            // 返回xml格式数据
            $data = xml_encode($data);
        }elseif('php'==$type){
            $data = serialize($data);
        }// 默认直接输出
        $this->setContentType($type);
        //header('Content-Length: ' . strlen($data));
        return $data;
    }

    /**
     * 设置页面输出的CONTENT_TYPE和编码
     * @access public
     * @param string $type content_type 类型对应的扩展名
     * @param string $charset 页面输出编码
     * @return void
     */
    public function setContentType($type, $charset=''){
        if(headers_sent()) return;
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        $type = strtolower($type);
        if(isset($this->allowOutputType[$type])) //过滤content_type
            header('Content-Type: '.$this->allowOutputType[$type].'; charset='.$charset);
    }

}
