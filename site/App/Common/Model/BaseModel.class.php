<?php
namespace Common\Model;
use Think\Model;
class BaseModel extends Model 
{
    protected $_auto = array (
        array('updated_at','time', self::MODEL_BOTH, 'function'),
        array('created_at','time', self::MODEL_INSERT, 'function'),
    );

    protected $patchValidate = true;

    public function is_valid($data='',$type=365) 
    {
        if(empty($data) || !is_array($data)) {
            $this->error = L('_DATA_TYPE_INVALID_');
            return false;
        }

        // 数据自动验证
        if(!$this->autoValidation($data, $type)) {
            return false;
        } else {
            return true;
        }

    }

    public function find_one($condition, $field='*', $order_by='')
    {
        return $this->field($field)->where($condition)->order($order_by)->find();
    }

    public function find_all($condition, $field='*', $group='', $order_by='id ASC', $limit='')
    {
        return $this->field($field)->where($condition)->group($group)->order($order_by)->limit($limit)->select();
    }

    public function get_field($condition, $field, $sepa=null, $order_by='')
    {
        return $this->where($condition)->order($order_by)->getField($field, $sepa);
    }

    public function pagination($condition, $page_num, $num_per_page, $order_by = 'updated_at DESC')
    {
        $list = $this->where($condition)->order($order_by)->page($page_num, $num_per_page)->select();

        $count      = $this->where($condition)->count();
        $Page       = new \Think\Page($count, $num_per_page);
        $show       = $Page->show();
        return array($show, $list);
    }

    public function update($condition, $data)
    {
        $check = $this->create($data);
        if(!$check){
            return array($check, $this->getError());
        }
        return array($check,  $this->where($condition)->save());
    }

    public function insert($data)
    {
        $check = $this->create($data);
        if(!$check){
            return array($check, $this->getError());
        }
        return array($check, $this->add());
    }

    public function insert_all($data)
    {
        return $this->addAll($data);
    }

    public function soft_delete($condition) {
        return $this->_delete($condition, 0);
    }

    public function phy_delete($condition) { 
        return $this->_delete($condition, 1);
    }

    public function _delete($condition, $type = 0)
    {
        switch($type){
        case 0:
            $data = array('is_del'=>'1', 'updated_at'=>time());
            $r = $this->where($condition)->save($data);
            break;
        case 1:
            $r = $this->where($condition)->delete();
            break;
        default:
            $r = false;
            break;
        }
        return $r;
    }

    public function get_count($condition,$field=''){
        return $this->where($condition)->field($field)->count();
    }

    public function set_field($condition,$data){
        return $this->where($condition)->setField($data);
    }

}
