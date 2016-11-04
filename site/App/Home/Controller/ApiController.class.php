<?php
namespace Home\Controller;

vendor('Aes.Aes');
/**
 * @author wangleiming<wangleiming@yunmai365.com>
 *
 **/ 
define('APPKEY', 'a11c7d3f56778271340b93381d6e7847');
class ApiController extends BaseController{


    public function __construct() {
        parent::__construct(false);
    }

    public function syslogin(){
        $data = str_replace('@', '/', I('get.code')); 
        $aes  = new \Aes(APPKEY); 
        $data = $aes->decrypt($data); 
        parse_str($data, $user);
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        setcookie('auth', $user['username'], time()+86400);
    }

}
