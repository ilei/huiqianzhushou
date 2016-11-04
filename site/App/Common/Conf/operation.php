<?php
return array(
	'create_recharge_record' => array(
		'code' => 10000,
		'text' => '添加了一条用户充值记录,相当于订单', 
	),
	'update_recharge_record' => array(
		'code' => 10001,
		'text' => '更改了一条用户充值记录的状态为支付成功', 
	),

	'create_balance_record' => array(
		'code' => 20000,
		'text' => '添加一条账户余额变更记录', 
	),

	'create_user_account' => array(
		'code' => 30000,
		'text' => '添加用户账户', 
	),

	'update_user_account' => array(
		'code' => 30001,
		'text' => '更新用户账户余额', 
	),

	'create_custom_record' => array(
		'code' => 40000,
		'text' => '添加一条用户消费记录', 
	),

	'alipay_notify_record' => array(
		'code' => 50000,
		'text' => '',
	),

	'alipay_return_record' => array(
		'code' => 50001,
		'text' => '',
	),

	'send_sms_ticket_succ' => array(
		'code' => 60000,	
		'text' => '短信电子票发送成功',	
	),
	'send_sms_ticket_fail' => array(
		'code' => 60001,	
		'text' => '短信电子票发送失败',	
	),
	'send_mail_ticket_succ' => array(
		'code' => 60002,	
		'text' => '邮件电子票发送成功',	
	),
	'send_mail_ticket_fail' => array(
		'code' => 60003,	
		'text' => '邮件电子票发送失败',	
	),

	'create_order'         => array(
		'code' => 70000,
		'text' => '创建订单成功',	
	),
	
	'update_order'         => array(
		'code' => 70001,
		'text' => '更新订单数据成功',	
	),

	'create_goods' => array(
		'code' => 80000,
		'text' => '创建商品成功',
	),

	'update_goods' => array(
		'code' => 80001,
		'text' => '更新商品成功',
	),

	'update_goods_storage' => array(
		'code' => 80002,
		'text' => '更新商品库存成功',
	),

	'create_goods_storage_record' => array(
		'code' => 80003,
		'text' => '添加商品库存记录成功',
	),

	'create_goods_storage' => array(
		'code' => 80004,
		'text' => '添加商品库存成功',
	),


);
