<?php 
vendor('YmPush.Content.Content');

/**
 * 发送器类
 *  抽像类  定义发送规则 
 *  @author wangleiming<wangleiming@yunmai365.com>
 *
 **/ 

abstract class Sender{

	/**
	 * 发送内容实例对象
	 *
	 **/
	protected $content   = null;

	public function __construct(Content $content){
		$this->content = $content;
	}

	/**
	 * 发送信息函数 
	 *
	 * @access public 
	 * @param  int   $type      status.php 查看 
	 * @param  mixed $to_user   要发送到的目标用户
	 * @param  array $msg_data  要发送的信息内容
	 * @param  array $args      不同的发送器可能需要不同的参数 
	 * @return mixed 
	 **/ 

	abstract public function send($type, $to_user, $msg_data, $args = array());
}
