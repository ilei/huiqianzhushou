<?php
namespace Api\Controller\App;
use Api\Controller\App\BaseUserController;


/**
 * Created by PhpStorm.
 * User: NYW
 * Date: 2015/12/2
 * Time: 9:20
 */

    class AccountController extends BaseUserController{

        /**
         * 获取个人账户信息
         */
        public function  get_account(){
            $this->check_request_method('get');
            //获取参数
            $guid       = $this->user_info['user_guid'];
            $info=D('UserAccount')->where(array('account_guid'=>$guid))->find();
            if(!$info){
                $data = array(
                    'ems_num'    => $info['msg_nums'],
                    'email_num' => $info['email_nums']);
                $this->output_data($data);
            }else{
                $this->output_error('10009');
            }

        }

}
