<?php
namespace Img\Controller;

use Img\Controller\BaseController;
use Think\Upload;

/**
 * 图片上传 
 * @author wangleiming<wangleiming@yunmai365.com>
 **/ 

class UploadController extends BaseController{

    public function __construct() {
        parent::__construct();
    }

    public function crop(){
        header('Access-Control-Allow-Origin:*'); 
        $cropdata = json_decode(html_entity_decode(I('post.cropdata'))); 
        $type     = I('post.type');
        $size     = I('post.size');
        $res = $this->uploadHandler($type, $size, $cropdata);
        if(is_array($res)){
            list($val, $path) = $res;
            echo json_encode((array('status' => C('ajax_success'), 'data' => array('val' => $val, 'path' => $path))));
        }else{
            echo json_encode((array('status' => C('ajax_failed'), 'msg' => $res)));
        }
        exit(); 
    }

    public function actImg(){
        header('Access-Control-Allow-Origin:*'); 
        header("Content-type:text/html;charset=utf-8");
        $type = I('get.type'); 
        $res  = $this->uploadHandler($type); 
        $this->assign('callback', $callback);
        if(is_array($res)){
            list($val, $path) = $res;
            $this->assign('path', $path);
            $res = $this->fetch('script');
        }else{
            $this->assign('msg', $res);
            $res = $this->fetch('script');
        }
        exit($res);
    }

    private function uploadHandler($type, $size = array(), $cropdata = array()){
        $time_dir = date('Y_m_d', time()); 
        $guid   = create_guid();
        $config = array(
            'maxSize'  => C('MAX_UPLOAD_SIZE'),
            'exts'     => C('ALLOWED_EXTS'),
            'rootPath' => UPLOAD_PATH,
            'savePath' => '/etf/' . $time_dir . '/' . $guid . '/default/images/',
            'subName'  => '',
            'saveName' => $guid . '-origin',
        );
        switch($type){
        case 'avatar':
            $config = array_merge($config, array(
                'savePath' => '/etf/' . $time_dir . '/' . $guid . '/user/photo/',
            ));
            $dst_data = array(array(120, 120), array(70, 70));
            break;
        case 'activity_poster': 
            $config = array_merge($config, array(
                'savePath' => '/elf/' . $time_dir . '/' . $guid . '/activity/poster/',
            ));
            $dst_data = array(array(150, 90), array(540, 324));
            break;
        case 'act_content':
            $config = array_merge($config, array(
                'savePath' => '/etf/' . $time_dir . '/' . $guid . '/activity/content/',
            ));
            break;
        default:
            break;
        }
        $upload = new Upload($config);//实例化上传类
        // 上传文件
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            return $upload->getError();
        } else {// 上传成功
            $info = $info['upload'];
            $info['type'] = $this->_get_img_type($config['rootPath'].$info['savepath'].$info['savename']);
            if($cropdata){
                $src = $config['rootPath'] . $info['savepath'] . $info['savename']; 
                foreach($dst_data as $key => $value){
                    if($key == 0){
                        $size = explode(',', $size);
                        $size_1 = isset($size[0]) && intval($size[0]) ? $size[0] : $value[0]; 
                        $size_2 = isset($size[1]) && intval($size[1]) ? $size[1] : $value[1]; 
                    }
                    $dst = $config['rootPath'] . $info['savepath'] . $guid . "-{$value[0]}-{$value[1]}." . $info['ext'];
                    $this->cut($src, $dst, $cropdata, $info['type'], $value); 
                }

                $info['savename'] = $guid . "-{$size_1}-{$size_2}." . $info['ext']; 
            }
            $val  = $info['savepath']. $info['savename'];
            $path = '/Upload' . $val;
            return array($val, $path);
        }
    }  

    private function cut($src, $dst, $data, $type, $dst_data) {
        $res = true;
        if (!empty($src) && !empty($dst) && !empty($data)) {
            switch ($type) {
            case "image/gif":
                $src_img = imagecreatefromgif($src);
                break;

            case 'image/jpeg':
                $src_img = imagecreatefromjpeg($src);
                break;

            case 'image/png':
                $src_img = imagecreatefrompng($src);
                break;
            }
            $size = getimagesize($src);
            $size_w = $size[0]; // natural width
            $size_h = $size[1]; // natural height

            $src_img_w = $size_w;
            $src_img_h = $size_h;

            $degrees = $data->rotate;

            // Rotate the source image
            if (is_numeric($degrees) && $degrees != 0) {
                // PHP's degrees is opposite to CSS's degrees
                $new_img = imagerotate( $src_img, -$degrees, imagecolorallocatealpha($src_img, 0, 0, 0, 127) );

                imagedestroy($src_img);
                $src_img = $new_img;

                $deg = abs($degrees) % 180;
                $arc = ($deg > 90 ? (180 - $deg) : $deg) * M_PI / 180;

                $src_img_w = $size_w * cos($arc) + $size_h * sin($arc);
                $src_img_h = $size_w * sin($arc) + $size_h * cos($arc);

                // Fix rotated image miss 1px issue when degrees < 0
                $src_img_w -= 1;
                $src_img_h -= 1;
            }

            $tmp_img_w = $data -> width;
            $tmp_img_h = $data -> height;
            $dst_img_w = $dst_data[0];
            $dst_img_h = $dst_data[1];

            $src_x = $data -> x;
            $src_y = $data -> y;

            if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
                $src_x = $src_w = $dst_x = $dst_w = 0;
            } else if ($src_x <= 0) {
                $dst_x = -$src_x;
                $src_x = 0;
                $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
            } else if ($src_x <= $src_img_w) {
                $dst_x = 0;
                $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
            }

            if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
                $src_y = $src_h = $dst_y = $dst_h = 0;
            } else if ($src_y <= 0) {
                $dst_y = -$src_y;
                $src_y = 0;
                $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
            } else if ($src_y <= $src_img_h) {
                $dst_y = 0;
                $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
            }

            // Scale to destination position and size
            $ratio = $tmp_img_w / $dst_img_w;
            $dst_x /= $ratio;
            $dst_y /= $ratio;
            $dst_w /= $ratio;
            $dst_h /= $ratio;

            $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);

            // Add transparent background to destination image
            imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
            imagesavealpha($dst_img, true);

            $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

            if ($result) {
                if (!imagepng($dst_img, $dst)) {
                    $res = false;
                }
            } else {
                $res = false;
            }

            imagedestroy($src_img);
            imagedestroy($dst_img);
            return $res;
        }
    }

    private function _get_img_type($filename){
        if(function_exists('getimagesize') && !getimagesize($filename)){
            return false; 
        }  
        $fp = fopen($filename, 'rb');
        $buffer = fread($fp,4); 
        fclose($fp);
        $buffer = unpack('H*', $buffer);
        $type   = array(
            'FFD8FFE0' => 'image/jpeg', 
            'FFD8FFE2' => 'image/jpeg', 
            'FFD8FFE3' => 'image/jpeg', 
            'FFD8FFE1' => 'image/jpeg', 
            'FFD8FFE8' => 'image/jpeg',
            '47494638' => 'image/gif',
            '89504E47' => 'image/png',
            '89504E470D0A1A0A' => 'image/png',
        );
        return $type[strtoupper(current($buffer))];
    }
}
