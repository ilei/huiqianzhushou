<?php
namespace Common\Model;
use Think\Model\MongoModel;

class TicketLogModel extends MongoModel 
{
	protected $connection = null;
	public function __construct($tableName = 'ym_ticket_operation_log'){
		$this->connection = C('DB_CONFIG_MONGO');
		$this->dbName = C('DB_CONFIG_MONGO.DB_NAME');
		parent::__construct($tableName);
	}

}
