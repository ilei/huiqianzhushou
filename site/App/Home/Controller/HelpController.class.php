<?php
namespace Home\Controller;

use Home\Controller\BaseController;
/**
 * Created by PhpStorm.
 * User: RTH
 * Date: 2015/10/20
 * Time: 9:23
 */

 class HelpController extends BaseController{

     public function __construct(){
         parent::__construct($login = false);
         layout('layout_new');
     }

     public function index(){

         $this->show();
     }
}