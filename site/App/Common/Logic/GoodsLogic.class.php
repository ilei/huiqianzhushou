<?php 
namespace  Common\Logic;
/**
 * 商品业务逻辑 
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 

class GoodsLogic{

	public $errors = array();

	/**
	 * 创建商品
	 *
	 * @access public 
	 * @param  array  $data 
	 * @return mixed 
	 **/ 

	public function add($data = array()){
		if(empty($data)){
			return false;
		}		

		//必须要有卖家guid、商品名称、商品目标ID(票就是活动GUID)
		if(!validate_data($data, 'seller_guid') || !validate_data($data, 'name') || !validate_data($data, 'target_guid')){
			array_push($this->errors, '缺少必要字段');	
			return false;
		}
		$guid  = create_guid();
		$goods = array(
			'guid'        => $guid,	
			'cate_guid'   => validate_data($data, 'cate_guid', '59178B30920D054D3EC18938F0449C6F'),
			'seller_guid' => trim($data['seller_guid']),
			'seller_name' => validate_data($data, 'seller_name', ''),
			'target_guid' => trim($data['target_guid']),
			'name'        => trim($data['name']),
			'price'       => $data['price'],
			'is_vat'      => intval(validate_data($data, 'is_vat', 0)),
			'is_admin'    => intval(validate_data($data, 'is_admin', 1)), 
			'status'      => intval(validate_data($data, 'status', 2)), 
			'poster'      => validate_data($data, 'poster', ''), 
			'content'     => validate_data($data, 'content', ''), 
			'start_time'  => validate_data($data, 'start_time', ''), 
			'end_time'    => validate_data($data, 'end_time', ''), 
			'sort'        => validate_data($data, 'sort', 1),
			'created_time' => time(),	
			'updated_time' => time(),	
			'ticket_guid'  => validate_data($data, 'ticket_guid', ''),
		);		

		//取得卖家名称
		if(!$goods['seller_name']){
			$seller = D('UserAttrInfo')->find_one(array('user_guid' => $goods['seller_guid']));
			$goods['seller_name'] = $seller ? ($seller['nickname'] ? $seller['nickname'] : $seller['mobile']) : '';
		}
		$model = D('Goods');
		$model->startTrans();
		$model->create($goods); 
		$res = $model->add();
		if(!$res){
			array_push($this->errors, '商品信息添加失败');
			$model->rollback();
			return false;
		}

		$goods_storage = !is_numeric($data['storage']) ? 0 : ($data['storage'] <= 0 ? -1 : intval($data['storage']));

		$storage = array(
			'guid'    	   => create_guid(),
			'goods_guid'   => $goods['guid'],
			'init_storage' => $goods_storage,
			'curr_storage' => $goods_storage,
		);
		D('GoodsStorage')->create($storage);
		$res = D('GoodsStorage')->add();
		if(!$res){
			array_push($this->errors, '商品库存添加失败');
			$model->rollback();
			return false;
		}
		$record = array(
			'guid'         => create_guid(),
			'record'       => $goods_storage,
			'created_time' => time(),	
			'goods_guid'   => $goods['guid'],
		);

		//type 1 正常库存限制，2表示库存无限制
		$record['type'] = $goods_storage == -1 ? 2 : 1; 
		D('GoodsStorageRecord')->create($record);
		$res = D('GoodsStorageRecord')->add();
		if(!$res){
			array_push($this->errors, '商品库存变更记录添加失败');
			$model->rollback();
			return false;
		}
		$model->commit();
		goods_operation_log($goods['guid'], C('create_goods'), $goods);
		goods_operation_log($goods['guid'], C('create_goods_storage'), $storage);
		goods_operation_log($goods['guid'], C('create_goods_storage_record'), $record);
		return true;
	}

	/**
	 * 取得商品信息 包含库存
	 *
	 * @access public 
	 * @param  string $goods_guid 产品GUID 
	 * @return array
	 **/ 

	public function get_goods($goods_guid){
		$goods = D('Goods')->find_one(array('guid' => $goods_guid, 'status' => 2));
		if(!$goods){
			return false;
		}
		$storage = D('GoodsStorage')->find_one(array('goods_guid' => $goods_guid));
		if(!$storage){
			return false;
		}
		$goods['storage'] = $storage['curr_storage'];
		return $goods;
	}

	/**
	 * 添加多个商品 
	 *
	 * @access public 
	 * @param  array  $data 
	 * @return true or false 
	 **/ 

	public function add_arr($data){
		$level = get_arr_level($data);
		if($level == 1){
			return $this->add($data);
		}	
		foreach($data as $value){
			$res = $this->add($value);
			if(!$res){
				return false;
			}
		}
		return true;
	}

	/**
	 * 更新商品 
	 *
	 * @access public  
	 * @param  array  $data 
	 * @param  array  $condition 
	 * @return true or false 
	 **/ 

	public function update_goods($data, $condition){
		if(!$condition || !$data){
			return false;	
		}
		//拿到$goods_guid
		$goodsModel = D('Goods');
		$goods = $goodsModel->find_one($condition);
		if(!$goods){
			return false;
		}
		$goods_guid = $goods['guid'];
		$goodsModel->startTrans();
		//是否更新库存
		if(validate_data($data, 'num')){
			$storage = $data['num'];
			$storage = !is_numeric($storage) ? 0 : ($storage <= 0 ? -1 : intval($storage));
			$old     = D('GoodsStorage')->find_one(array('goods_guid' => $goods_guid));
			$old_sto = $old['curr_storage'];
			//都不是不限制才更新
			if(!($old_sto == -1 && $storage == -1)){
				$sto_con = array('goods_guid' => $goods_guid, 'version' => $old['version']);
				$sto_upd = array('curr_storage' => $storage, 'version' => intval($old['version'])+1);
				$sto     = D('GoodsStorage')->update($sto_con, $sto_upd);
				if(!$sto){
					$goodsModel->rollback();
					return false;
				}
				if(intval($old_sto) != $storage){
					$record = array(
						'guid'         => create_guid(),
						'record'       => $storage,
						'created_time' => time(),	
						'goods_guid'   => $goods_guid,
					);

					//type 1 正常库存限制，2表示库存无限制
					$record['type'] = $storage == -1 ? 2 : 1; 
					D('GoodsStorageRecord')->create($record);
					$res = D('GoodsStorageRecord')->add();
					if(!$res){
						$goodsModel->rollback();
						return false;
					}
				}
				unset($data['num']);
			}
			goods_operation_log($goods_guid, C('update_goods_storage'), $sto_upd);
			goods_operation_log($goods_guid, C('create_goods_storage_record'), $record);
		}
		$cond = array_merge($condition, array('version' => $goods['version']));
		unset($data['guid']);
		$upda = array_merge($data, array('version' => intval($goods['version']) + 1));
		$res = $goodsModel->update($cond, $upda);
		if(!$res){
			$goodsModel->rollback();
			return false;
		}
		$goodsModel->commit();
		goods_operation_log($goods_guid, C('update_goods'), $upda);
		return true;
	}
}
