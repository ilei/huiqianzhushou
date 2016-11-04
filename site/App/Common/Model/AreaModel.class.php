<?php
namespace Common\Model;
use Common\Model\BaseModel;

class AreaModel extends BaseModel 
{    
    public function getName($id)
    {
        return $this->where(array('id'=>$id))->getField('name');
    }
    
}
