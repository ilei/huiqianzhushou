<?php
namespace Api\Controller\App;
use Api\Controller\BaseRestController;

/**
 * 下载
 * CT: 2015-01-06 11:00 by YLX
 * UT: 2015-04-16 11:00 by YLX
 */
class DownloadController extends BaseRestController {

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

}