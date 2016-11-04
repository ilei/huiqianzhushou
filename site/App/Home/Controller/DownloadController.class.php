<?php
namespace Home\Controller;

use Home\Controller\RestController;
/**
 * 下载
 * CT: 2015-01-06 11:00 by YLX
 * UT: 2015-04-16 11:00 by YLX
 */
class DownloadController extends RestController {


    public function __construct($login = true){
        parent::__construct(false);
    }
    /**
     * apk升级
     * CT: 2015-01-06 11:00 by YLX
     * UT: 2015-04-16 11:00 by YLX
     */
    public function app_check() {
        $type = I('get.d');
        if(!in_array($type, array_keys(C('APP_TYPE')))){
            $this->output_error('10003');
        }

        $file_info = M('AppUpload')->where(array('is_del' => '0', 'status'=>'1', 'type' => $type))
            ->order('updated_at DESC')
            ->find();
        if(empty($file_info)) {
            $file_info['version'] = 0;
        }
        switch($type) {
            case 1:
                $url = C('DOWNLOAD_ELF_ANDROID');
                break;
            case 2:
                $url = C('DOWNLOAD_ELF_ANDROID_BETA');
                break;
            case 3:
                $url = C('DOWNLOAD_ELF_PC');;
                break;
            case 4:
                $url = C('DOWNLOAD_ELF_PC_BETA');;
                break;
            case 5:
                $url = C('DOWNLOAD_ELF_IOS').trim($file_info['plist_path']);
                break;
            case 6:
                $url = C('DOWNLOAD_ELF_IOS').trim($file_info['plist_path']);
                break;
        }
        $data      = array(
            'name'    => C('APP_NAME'),
            'ut'      => date('Y-m-d H:i:s', time()),
            'url'     => $url,
            'is_force'=> $file_info['is_force'],
            'content' => isset($file_info['content']) ? $file_info['content'] : '',
//            'version' => isset($file_info['version']) ? $file_info['version'] : 0
            'version' => isset($file_info['internal_version']) ? $file_info['internal_version'] : 0
        );
        $this->output_data($data);
    }

    /**
     * 错误输出
     *
     * CT: 2014-09-19 17:00 by YLX
     * UT: 2014-09-23 14:00 by YLX
     *
     */
    public function output_error($code, $msg=null, $data=null)
    {
        $data = array('code'=>$code, 'msg'=>$msg, 'data'=>$data);
        return $this->response($data, 'json');
    }

    /**
     * 数据输出
     *
     * CT: 2014-09-19 17:00 by YLX
     * UT: 2014-09-23 14:00 by YLX
     */
    public function output_data($data = null,$msg=null)
    {
        $data = array('code'=>10000, 'msg'=>$msg, 'data' => $data);
        return $this->response($data, 'json');
    }

}