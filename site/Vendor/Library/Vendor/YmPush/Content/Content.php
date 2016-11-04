<?php 
/**
 * 信息内容类
 *
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 

abstract class Content{

	/**
	 * 取得内容信息 
	 *
	 * @access public 
	 * @param  mixed $type 参见 Common/Conf/status.php 
	 * @param  array $msg_data 发送的信息 
	 * @param  array $ext      额外信息
	 * @return mixed 
	 **/ 

	abstract function getContent($type, $msg_data = array(), $ext = array());

}
