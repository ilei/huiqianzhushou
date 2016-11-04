<?php 
// +----------------------------------------------------------------------
// | YunMai365 Framework [ I CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011-2015 YunMai365 Team (http://www.shetuanbang.com)
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author wangleiming <wangleiming@yunmai365.com>
// +----------------------------------------------------------------------

/**
 * 加载类 
 *  1、以用户自定义优先加载 
 *  2、配置内容会进行保存
 **/ 

class Loader{

    /**
     * 加载配置
     *
     * @access publis static 
     * @param  string   $name server 或者 subpath.server
     * @param  bool     $exit 没有找到是否退出
     * @return array
     **/ 

    static function load_config($name, $exit = true){
        static $_config = array();	
        if(!isset($_config[$name])){
            $sub = str_replace('.', '/', $name) . '.php';
            if(file_exists(($file = CLIENT_PATH . 'config/' . $sub))){
                $_config[$name] = include($file);
            }elseif(file_exists(($file = CONFIG_PATH . $sub))){
                $_config[$name] = include($file);
            }else{
                if($exit){
                    echo "Fatal Error : load config file {$name} failed";
                    return $_config;
                }
            }
        }
        return $_config[$name];
    }

    /**
     * 加载文件 
     *
     * @access public static 
     * @param  string $name  
     * @param  string $ext   扩展名 
     * @return void 
     **/ 

    static function load($name, $ext  = 'php'){
        $sub = str_replace('.', '/', $name) . '.' . $ext;
        if(file_exists(($file = CLIENT_PATH . $sub))){
            include_once($file);
        }elseif(file_exists(($file = CORE_PATH . $sub))){
            include_once($file);
        }else{
            echo "Fatal Error : load file {$name} failed";
        }
    }	
}
