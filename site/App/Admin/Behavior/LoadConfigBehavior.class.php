<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/3
 * Time: 下午5:06
 */


namespace Admin\Behavior;

use Pinq\Expressions\VariableExpression;
use Think\Behavior;
use Think\Exception;


class LoadConfigBehavior extends Behavior
{
    protected $configKey;


    public function __construct()
    {
        //初始化配置
        $this->configKey = C('CONFIG_KEY');

    }

    public function run(&$params)
    {
        try {

            //获取Config
            $config = F($this->configKey);

            if (empty($config)) {
                $config = $this->loadConfigFromDB();
                //写入缓存
                F($this->configKey, $config);
            }
            //将结果添加到Config中
            foreach ($config as $key => &$value) {
                //动态加载到配置中


//                //判断要加载的数组是否为array
//                if (is_array($value)) {
//
//                    $old_value = C($key);
//                    if (!empty($old_value) && !empty($value)&&is_array($value)) {
//                        //递归合并所有的值,并以$value为准
//                        $value = array_merge_recursive($old_value, $value);
//                    }
//                }

                C($key, $value);

            }
        } catch (Exception $e) {

        }
    }


    //重新加载配置 数据库 -> Cache
    public function  reloadSettings()
    {

        $config = $this->loadConfigFromDB();

        //写入快速缓存
        F($this->configKey, $config);

        //将结果添加到Config中
        foreach ($config as $key => &$value) {
//            //动态加载到配置中
//            //判断要加载的数组是否为array
//            if (is_array($value)) {
//
//                $old_value = C($key);
//                if (!empty($old_value) && !empty($value)&&is_array($value)) {
//                    //递归合并所有的值,并以$value为准
//                    $value = array_merge_recursive($old_value, $value);
//                }
//            }

            C($key, $value);

        }
    }

    //从数据库加载配置
    protected function  loadConfigFromDB()
    {

        $result = array();

        //获取所有的配置
        $source = M('AppSettings')->select();

        foreach ($source as $item) {


            if ($item['parent_id'] == '') {

                switch ($item['type']) {
                    case "String": {
                        $result[$item["key"]] = strval($item['value']);
                        break;
                    }
                    case "Int": {
                        $val = intval($item['value']);
                        if (!is_null($val)) {
                            $result[$item["key"]] = $val;
                        }
                        break;
                    }

                    case "Float": {
                        $val = floatval($item['value']);
                        if (!is_null($val)) {
                            $result[$item["key"]] = $val;
                        }
                        break;
                    }
                    case "Bool": {

                        $result[$item["key"]] = (bool)($item['value']);
                        break;
                    }
                    default: {
                        $child = array();


                        $this->findChild($source, $item['id'], $child);
                        $result[$item["key"]] = $child;
                    }
                }
            }
        }

        return empty($result) ? array() : $result;
    }

    //查找子集
    protected function findChild($source, $pid, &$obj)
    {

        foreach ($source as $item) {

            if ($item['parent_id'] == $pid) {


                switch ($item['type']) {
                    case "String": {
                        $obj[$item["key"]] = strval($item['value']);
                        break;
                    }
                    case "Int": {
                        $val = intval($item['value']);
                        if (!is_null($val)) {
                            $obj[$item["key"]] = $val;
                        }
                        break;
                    }

                    case "Float": {
                        $val = floatval($item['value']);
                        if (!is_null($val)) {
                            $obj[$item["key"]] = $val;
                        }
                        break;
                    }
                    case "Bool": {
                        $result[$item["key"]] = (bool)($item['value']);
                        break;
                    }
                    default: {
                        $child = array();
                        $this->findChild($source, $item['id'], $child);
                        $obj[$item["key"]] = $child;
                    }
                }
            }

        }

    }

}