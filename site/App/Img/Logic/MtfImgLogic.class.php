<?php
namespace Img\Logic;

/**
 * Image Util
 * @author  wangleiming<wangleiming@yunmai365.com>
 * @version 0.1
 **/

class MtfImgLogic{
    /**
     * AdImage::retrial
     * @param string file
     * @return boolean
     */
    public static function retrial($file) {
        $im = new Imagick();
        try{
            $im->readImage($file);
            if($im->getImageColorspace() == Imagick::COLORSPACE_CMYK) {
                $im->clear();
                $im->destroy();
                return FALSE;
            }
            $im->writeImages($file, TRUE);
            $im->clear();
            $im->destroy();
            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }
}
