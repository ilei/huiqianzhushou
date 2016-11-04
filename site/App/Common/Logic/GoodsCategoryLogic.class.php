<?php 
namespace  Common\Logic;
/**
 * 商品类别业务逻辑 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 

class GoodsCategoryLogic{

	public $errors = array();

	/**
	 * 创建商品类别
	 *
	 * @access public 
	 * @param  array  $data 
	 * @return mixed 
	 **/ 

	public function add($data = array()){
		if(empty($data)){
			return false;
		}
		if(!validate_data($data, 'name') || !validate_data($data, 'creater_guid')){
			array_push($this->errors, '缺少名称或者创建者GUID');
			return false;	
		}
		vendor('pinyin.Pinyin');
		$tmp  = array(
			'guid'     => create_guid(),
			'mark'     => \Pinyin::pinyin(trim($data['name'])),
			'status'   => validate_data($data, 'status', 1), 
			'is_admin' => validate_data($data, 'is_admin', 1), 
		);
		$data  = array_merge($data, $tmp);
		$model = D('GoodsCategory');
		$model->create($data);
		$res   = $model->add();	
		if(!$res){
			$this->erros = $model->getError();
			return false;
		}
		return $res; 
	}	
}
