<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;
use Think\Cache;
defined('THINK_PATH') or exit();

/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 *
 * UT: 2015-05-20 17:20 BY YLX FROM http://www.thinkphp.cn/extend/501.html
 */
class Redis extends Cache {
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
//    public function __construct($options=array()) {
//        if ( !extension_loaded('redis') ) {
//            E(L('_NOT_SUPPERT_').':redis');
//        }
//        if(empty($options)) {
//            $options = array (
//                'host'          => C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1',
//                'port'          => C('REDIS_PORT') ? C('REDIS_PORT') : 6379,
//                'timeout'       => C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false,
//                'persistent'    => false,
//            );
//        }
//        $this->options =  $options;
//        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
//        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');
//        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;
//        $func = $options['persistent'] ? 'pconnect' : 'connect';
//        $this->handler  = new \Redis;
//        $options['timeout'] === false ?
//            $this->handler->$func($options['host'], $options['port']) :
//            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
//    }
//
//    /**
//     * 读取缓存
//     * @access public
//     * @param string $name 缓存变量名
//     * @return mixed
//     */
//    public function get($name) {
//        N('cache_read',1);
//        $value = $this->handler->get($this->options['prefix'].$name);
//        $jsonData  = json_decode( $value, true );
//        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
//    }
//
//    /**
//     * 写入缓存
//     * @access public
//     * @param string $name 缓存变量名
//     * @param mixed $value  存储数据
//     * @param integer $expire  有效时间（秒）
//     * @return boolean
//     */
//    public function set($name, $value, $expire = null) {
//        N('cache_write',1);
//        if(is_null($expire)) {
//            $expire  =  $this->options['expire'];
//        }
//        $name   =   $this->options['prefix'].$name;
//        //对数组/对象数据进行缓存处理，保证数据完整性
//        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
//        if(is_int($expire)) {
//            $result = $this->handler->setex($name, $expire, $value);
//        }else{
//            $result = $this->handler->set($name, $value);
//        }
//        if($result && $this->options['length']>0) {
//            // 记录缓存队列
//            $this->queue($name);
//        }
//        return $result;
//    }
//
//    /**
//     * 删除缓存
//     * @access public
//     * @param string $name 缓存变量名
//     * @return boolean
//     */
//    public function rm($name) {
//        return $this->handler->delete($this->options['prefix'].$name);
//    }
//
//    /**
//     * 清除缓存
//     * @access public
//     * @return boolean
//     */
//    public function clear() {
//        return $this->handler->flushDB();
//    }

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array())
    {
        if ( !extension_loaded('redis') ) {
            throw_exception(L('_NOT_SUPPERT_').':redis');
        }
        if(empty($options))
        {
            $options = array (
                'host'          => C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1',
                'port'          => C('REDIS_PORT') ? C('REDIS_PORT') : 6379,
                'timeout'       => C('REDIS_TIMEOUT') ? C('REDIS_TIMEOUT') : 300,
                //pengyong 2013年12月13日 16:21:01 add config
                'persistent'    => C('REDIS_PERSISTENT') ? C('REDIS_PERSISTENT') : false,
                'auth'		=> C('REDIS_AUTH') ? C('REDIS_AUTH'):null,//auth认证
                'rw_separate'	=> C('REDIS_RW_SEPARATE') ? C('REDIS_RW_SEPARATE') :false,//主从分离
            );
        }
        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;
        $this->options['func'] = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new \Redis;
    }


    /**
     * 主从连接
     * @access public
     * @param bool $master true=主连接
     */


    public function master($master=false)
    {

        $host = explode(",",$this->options['host']);

        if(count($host) > 1 && $master ==false && $this->options['rw_separate']==true)
        {
            array_shift($host);
        }
        if($this->options['rw_separate']==false)
        {
            shuffle($host);
        }
        $this->options['master'] = $master ==true ? $host[0]:'';
        $this->options['slave'] = $master ==false ? $host[0]:'';
        $func = $this->options['func'];
        $connect = $this->options['timeout'] ===false ?
            $this->handler->$func($host[0], $this->options['port']) :
            $this->handler->$func($host[0], $this->options['port'], $this->options['timeout']);
        //pengyong 2013年12月13日 16:17:50 支持认证模式
        if($this->options['auth']!=null)
        {
            $this->handler->auth($this->options['auth']);
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */


    public function get($name)
    {
        N('cache_read',1);
        $this->master(false);
        #F('read',$this->options['slave']);
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */


    public function set($name, $value, $expire = null)
    {
        N('cache_write',1);
        $this->master(true);
        #F('write',$this->options['master']);
        if(is_null($expire))
        {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        //删除缓存操作支持
        if($value===null)
        {
            return $this->handler->delete($this->options['prefix'].$name);
        }
        if(is_int($expire) && $expire !=0)
        {
            $result = $this->handler->setex($name, $expire, $value);
        }
        else
        {
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0)
        {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */


    public function rm($name)
    {
        $this->master(true);
        return $this->handler->delete($this->options['prefix'].$name);
    }


    /**
     * 清除缓存
     * @access public
     * @return boolen
     */


    public function clear()
    {
        $this->master(true);
        return $this->handler->flushDB();
    }


    /**
     * 析构释放连接
     * @access public
     */


    public function __destruct()
    {
        $this->handler->close();
    }
}
