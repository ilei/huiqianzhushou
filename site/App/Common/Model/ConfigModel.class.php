<?php
namespace Common\Model;
use Common\Model\BaseModel;
/**
 * 配置表  模型
 *
 * CT 2014-12-05 11:39 by RTH
 */
class ConfigModel extends BaseModel{

    /**
     * 根据模块获取配置
     * @param $module 对应模块名: common, admin, home, api, site, mobile
     * @return bool|mixed
     * CT: 2015-01-09 10:56 BY YLX
     */
    public function get_config_by_module($module) {
        if(empty($module)) return false;
        return $this->field('guid, value, name')->where(array('module' => $module))->select();
    }
}