<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 上午11:23
 *
 * 加载器 约定>配置
 */


class Loader{

    static public  function  config($name='config')
    {
        static $config_list=array();

        if (!isset($config_list[$name])){
            $path=CONFIG_PATH.$name.'.php';

            if(file_exists($path)){
                $config_list[$name]=include_once($path);
            }else{
                return null;
            }
        }
        return $config_list[$name];
    }
}