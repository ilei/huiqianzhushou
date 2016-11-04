<?php
namespace Home\Controller;

class IndexController extends BaseController{


    public function __construct(){
        parent::__construct(false);
    }

    public function index(){
        redirect(U('/auth/login'));
    }


    //android下载
    public function android_download(){
        $res = D('AppUpload')->where(array('type' => 2, 'is_del' => 0))->order('updated_at desc')->find();
        $file = UPLOAD_PATH . '/meetelf/android/meetelf-beta.apk';
        $size = sizecount(filesize($file));
        $this->assign('version', $res['external_version']); 
        $this->assign('size', $size);   
        $this->show();
    }

    public function download(){
        $this->css[] = 'meetelf/home/css/home.index.download.css';
        $this->show(); 
    }
}
