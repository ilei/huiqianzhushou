<?php
namespace Common\Model;
use Think\Model\MongoModel;

/**
 * 用户操作记录表 
 * 
 * CT: 2015-07-15 16:40 by wangleiming 
 */
class TicketLogModel extends MongoModel 
{
	protected $connection = null;
	public function __construct($tableName = 'ym_ticket_operation_log'){
		$this->connection = C('DB_CONFIG_MONGO');
		$this->dbName = C('DB_CONFIG_MONGO.DB_NAME');
		parent::__construct($tableName);
	}

}