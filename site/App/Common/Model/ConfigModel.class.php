<?php
namespace Common\Model;
use Common\Model\BaseModel;
class ConfigModel extends BaseModel{

    public function get_config_by_module($module) {
        if(empty($module)) return false;
        return $this->field('guid, value, name')->where(array('module' => $module))->select();
    }
}
