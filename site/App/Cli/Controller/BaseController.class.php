<?php
namespace Cli\Controller;

use Think\Controller;

class BaseController extends Controller{
    
    public function __construct() {
        parent::__construct();
    }

    public function _empty() {
		exit('empty');
    }
    
}
