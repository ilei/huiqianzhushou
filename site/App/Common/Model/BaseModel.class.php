<?php
namespace Common\Model;
use Think\Model;

/**
 * 模型基类
 * 
 * CT: 2014-09-18 09:40 by YLX
 */
class BaseModel extends Model 
{
    /**
     * 主题模型自动完成
     * 
     * CT: 2014-10-21 09:26 by wangleiming 
     **/
    protected $_auto = array (
            array('updated_at','time', self::MODEL_BOTH, 'function'), // 对updated_at字段在新增/更新的时候写入当前时间戳
            array('created_at','time', self::MODEL_INSERT, 'function'), // 对created_at字段在新增的时候写入当前时间戳
    );

    /**
     * 是否批量显示验证信息
     * CT: 2014-12-05 11:13 by RTH
     */
    protected $patchValidate = true;

    /**
     * 数据有效性验证
     * @access public
     * @param array $data 创建数据
     * @param string $type 状态 365, 表示当验证时间标识为365时进行验证
     * @return mixed
     * 
     * CT: 2014-09-18 11:40 by YLX
     */
    public function is_valid($data='',$type=365) 
    {
        // 验证数据
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


    /**
     * #####################################################################################
     * #   模型基础方法   ---  start
     * #####################################################################################
     */
    /**
     * 获取详情
     * @param $condition
     * @return mixed
     * CT: 2014-12-05 09:35 by ylx
     */
    public function find_one($condition, $field='*', $order_by='')
    {
        return $this->field($field)->where($condition)->order($order_by)->find();
    }

    /**
     * 获取列表
     * @param array $condition
     * @param $order_by
     * @return mixed
     * CT: 2014-12-05 09:35 by ylx
     * UT: 2014-12-08 10:58 by RTH  // 加group   字段分组
     */
    public function find_all($condition, $field='*', $group='', $order_by='id ASC', $limit='')
    {
        return $this->field($field)->where($condition)->group($group)->order($order_by)->limit($limit)->select();
    }

    /**
     * 获取数据表中的某个列的多个或者单个数据
     * @param $condition
     * @param $field
     * @param null $sepa
     * @param string $order_by
     * @return mixed
     * CT: 2014-12-05 09:35 by ylx
     */
    public function get_field($condition, $field, $sepa=null, $order_by='')
    {
        return $this->where($condition)->order($order_by)->getField($field, $sepa);
    }

    /**
     * 分页方法
     * @param $condition
     * @param $page_num 请求页数
     * @param string $num_per_page 每页数量
     * @param string $order_by 默认updated_at DESC
     * @return array
     */
    public function pagination($condition, $page_num, $num_per_page, $order_by = 'updated_at DESC')
    {
        $list = $this->where($condition)->order($order_by)->page($page_num, $num_per_page)->select();

        // 使用page类,实现分类
        $count      = $this->where($condition)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count, $num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        return array($show, $list);
    }

    /**
     * 更新
     * @param $condition
     * @param $data
     * @return bool
     * CT: 2014-12-05 09:35 by ylx
     */
    public function update($condition, $data)
    {
        $check = $this->create($data);
        if(!$check){
            return array($check, $this->getError());
        }
        return array($check,  $this->where($condition)->save());
    }

    /**
     * 增加
     * @param array $data 一维数组
     * @return mixed
     * CT: 2014-12-05 09:35 by ylx
     */
    public function insert($data)
    {
        $check = $this->create($data);
        if(!$check){
            return array($check, $this->getError());
        }
        return array($check, $this->add());
    }

    /**
     * 批量增加
     * @param $data 二维数组
     * @return string
     * CT: 2014-12-05 09:35 by ylx
     */
    public function insert_all($data)
    {
        return $this->addAll($data);
    }

    /**
     * 删除
     * @param $condition
     * @param int $type 0为软删除, 1为物理删除
     * @return bool
     * CT: 2014-12-05 09:35 by ylx
     */
    public function soft_delete($condition) { // 软删除
        return $this->_delete($condition, 0);
    }
    public function phy_delete($condition) { //硬删除
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

    /**
     * 查询数据条数
     *
     * @return bool
     * CT:2014-12-08 09:59 by RTH
     */
    public function get_count($condition,$field=''){

        return $this->where($condition)->field($field)->count();
    }

    /**
     *更新某个数据字段或者多个字段
     *
     * @return bool
     * CT:2014-12-08 11:31 by RTH
     */
    public function set_field($condition,$data){

        return $this->where($condition)->setField($data);
    }

    /**
     * #####################################################################################
     * #   模型基础方法   ---  end
     * #####################################################################################
     */
}
