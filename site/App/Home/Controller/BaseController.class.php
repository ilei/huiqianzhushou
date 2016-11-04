<?php
namespace Home\Controller;
use 	  Think\Controller;

/**
 * 基础控制器
 *
 * @author ThinkLei 
 *
 **/

class BaseController extends Controller{

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

    public function ajax_response($data = array()){
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
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
            }
            $res = D('Area')->find_all('parent_id="' . intval($areaid) . '"', 'id, name');
            if (!empty($res)) {
                $this->assign('area', $res);
                $msg = $this->fetch('Act:area');
                $this->ajax_response(array('status' => C('ajax_success'), 'msg' => $msg));
            } else {
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_PARAM_ERROR_')));
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
                $this->ajax_response(array('status' => C('ajax_failed'), 'msg' => L('_REQUEST_TOO_MUCH_')));
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
}
