<?php
/**
 * Created by PhpStorm.
 * User: ylx
 * Date: 2014/12/26
 * Time: 12:21
 */
namespace Mobile\Controller;

use Think\Controller;

/**
 * 空控制器
 * CT: 2014-09-19 15:00 by YLX
 *
 */
class EmptyController extends Controller {

    public function index() {
        header('HTTP/1.0 404 Not Found');
        $this->display('Tpl/404');
    }
}
