<?php
namespace Admin\Controller;

use Think\Controller;
/**
 * 配置表管理
 *
 * CT: 2014-09-15 10:00 by RTH
 * UT: 2014-09-17 10:00 by RTH
 */
class ConfigController extends BaseController
{
    /**
     * 配置
     * CT: 2015-01-09 12:00 by ylx
     */
    public function index() {
        $common = D('Config')->get_config_by_module('common');
        $admin  = D('Config')->get_config_by_module('admin');
        $home   = D('Config')->get_config_by_module('home');
        $api    = D('Config')->get_config_by_module('api');
        $mobile = D('Config')->get_config_by_module('mobile');
        $site   = D('Config')->get_config_by_module('site');
        $notice = D('Config')->get_config_by_module('notice');
        $this->assign('common', $common);
        $this->assign('admin', $admin);
        $this->assign('home', $home);
        $this->assign('api', $api);
        $this->assign('mobile', $mobile);
        $this->assign('site', $site);
        $this->assign('notice', $notice);
        $this->assign('meta_title', '网站配置管理');
        $this->display();
    }

    /**
     * 更新配置
     * CT: 2015-01-09 12:00 by ylx
     */
//    public function ajax_update_config() {
//        if(IS_AJAX) {
//            $data = I('post.');
//            foreach($data as $guid => $value) {
//                $r = D('Config')->set_field(array('guid' => $guid), array('value' => $value));
//                if(!isset($r)) {
//                    $this->ajaxResponse(array('status'=>'ko','msg'=>'保存失败, 请稍后重试.'));
//                }
//            }
//            $this->ajaxResponse(array('status'=>'ok','msg'=>'保存成功, 请生成配置文件.'));
//        }
//    }

    /**
     * ajax生成配置文件
     *
     * CT: 2015-01-09 12:00 by ylx
     */
    public function ajax_create_config_file(){
        if(IS_AJAX){
            //更新
            $data_set = I('post.');
            foreach($data_set as $guid => $value) {
                $r = D('Config')->set_field(array('guid' => $guid), array('value' => $value));
                if(!isset($r)) {
                    $this->ajaxResponse(array('status'=>'ko','msg'=>'保存失败, 请稍后重试.'));
                }
            }

            //生成
            $module = I('get.module');

			if($module == 'notice' || $module == 'common'){
				$module = 'common';
            	$data_select = D('Config')->where(array('module' => array('in' , 'common,notice')))->select();
			}else{
            	$data_select = D('Config')->where(array('module' => $module))->select();
			}
            $config = array();
            foreach($data_select as $d) {
                if(isset($d['value'])) {
                    $config[$d['key']] = $d['value'];
                }
            }

            if(file_put_contents(APP_PATH.'/'.ucfirst($module).'/Conf/generate_by_admin.php', "<?php \n return".' '.var_export($config ,true) .';') > 0){
                $this->ajaxResponse(array('status'=>'ok','msg'=>'生成成功'));
            }else{
                $this->ajaxResponse(array('status'=>'ko','msg'=>'生成失败'));
            }
        }else{
            $this->ajaxResponse(array('status'=>'ko','msg'=>'非法请求'));
        }
    }

    
}