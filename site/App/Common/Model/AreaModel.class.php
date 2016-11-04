<?php
namespace Common\Model;

/**
 * 区域 模型
 * 
 * CT: 2014-09-28 14:00 by YLX
 */
class AreaModel extends BaseModel 
{    
    /**
     * 根据ID获取地区名称
     *
     * CT: 2014-09-28 14:00 by YLX
     */
    public function getName($id)
    {
        return $this->where(array('id'=>$id))->getField('name');
    }
    
}