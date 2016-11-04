<?php
namespace Org\Util;
/**
 * 群组头像生成类
 * 
 * CT: 2014-11-21 9:00 by QXL
 */
class GroupFace{
	private $_groupFaceWidth = 120; 	//群组头像宽度
	private $_groupFaceHeight = 120;	//群组头像高度
	private $_userFaceWidth = 28; 	 	//用户头像缩略图宽度
	private $_userFaceHeight = 28; 	//用户头像缩略图高度
	private $_groupFaceIm = null;		//群组图片资源
	private $_userFace = array();	 	//用户头像
	private $_userFaceInfo = array();	//用户头像信息
	private $_groupFacePath='';			//群组头像地址
	private $_groupFaceName='';			//群组头像地址
	
	public function __construct($FaceArr,$Path='',$FileName=''){
		$this->_groupFacePath = $Path;
		$this->_groupFaceName = $FileName;
		$this->_userFace = $FaceArr;
	}
	
	/**
     * 创建用户头像资源&获取用户头像信息
     * 
     * CT: 2014-11-21 9:00 by QXL
     */
	public function createFaceIm(){
		foreach ($this->_userFace as $key => $value){
			if (!file_exists($value)){
				$value=get_image_path('');
			}		
			$faceinfo=getimagesize($value);
			$this->_userFaceInfo[$key]['width']=$faceinfo['0'];
			$this->_userFaceInfo[$key]['height']=$faceinfo['1'];
			$this->_userFaceInfo[$key]['type']=substr($faceinfo['mime'], strpos($faceinfo['mime'],'/')+1);
			switch ($this->_userFaceInfo[$key]['type']){
					case 'jpeg':
				 		$this->_userFaceInfo[$key]['source'] = imagecreatefromjpeg($value);
				 		 break;
					case 'png':
				  		$this->_userFaceInfo[$key]['source'] = imagecreatefrompng($value);
				  		break;
					case 'gif':
				 		 $this->_userFaceInfo[$key]['source'] = imagecreatefromgif($value);
				 		 break;
			}
		}
		$this->_userFaceInfo=array_values($this->_userFaceInfo);
	}
	
	/**
     * 创建群组头像资源&填充背景颜色
     * 
     * CT: 2014-11-21 9:00 by QXL
     */
	public function createGroupIm(){
		$this->_groupFaceIm = imagecreatetruecolor($this->_groupFaceWidth, $this->_groupFaceHeight);
		$gray = imagecolorallocate($this->_groupFaceIm,219,219,219); 
		imagefill($this->_groupFaceIm,0,0,$gray); 
	}
	
	/**
     * 合并群组头像
     * 
     * CT: 2014-11-21 9:00 by QXL
     */
	public function mergeFace(){
		$this->createGroupIm();
		$this->createFaceIm();
		foreach ($this->_userFaceInfo as $key=>$value){
			if($key<3) $faceArr[2][]=$value;
			if($key>2 && $key<6) $faceArr[1][]=$value;
			if($key>5 && $key <9) $faceArr[3][]=$value;
		}
		$this->faceLayout($faceArr);
		if(empty($this->_groupFacePath)){
			imagejpeg($this->_groupFaceIm);
		}else{
			if(file_exists($this->_groupFacePath)){
				imagejpeg($this->_groupFaceIm,$this->_groupFacePath.$this->_groupFaceName);
			}else{
				$this->MkFolder($this->_groupFacePath);
			}
			imagejpeg($this->_groupFaceIm,$this->_groupFacePath.$this->_groupFaceName);
		}
		$this->imageUserDestroy();
		$this->imageGroupDestroy();
	}
	
	/**
     * 群组头像布局
     * 
     * CT: 2014-11-21 9:00 by QXL
     */
	public function faceLayout($faceArr){
			$x=0;
			$spacing=($this->_groupFaceWidth-$this->_userFaceWidth*3)/4;
			switch (count($faceArr)){
				case 1:
			 		 $y=0;
			 		 break;
				case 2:
			  		 $y=($this->_groupFaceHeight-($this->_userFaceHeight*2+$spacing))/2;
			  		 break;
				case 3:
			 		 $y=$spacing;
			 		 break;
			}
			foreach ($faceArr as $key=>$value){
				$num=count($value);
				switch ($num){
					case 1:
				 		 $x=($this->_groupFaceWidth-$this->_userFaceWidth)/2;
				 		 break;
					case 2:
				  		 $x=($this->_groupFaceWidth-($this->_userFaceWidth*2+$spacing))/2;
				  		break;
					case 3:
				 		 $x=$spacing;
				 		break;
				}
				
				for ($i = 0; $i < $num; $i++) {
					imagecopyresampled($this->_groupFaceIm,$faceArr[$key][$i]['source'],$x+($this->_userFaceWidth+$spacing)*$i,$y+($this->_userFaceHeight+$spacing)*($key-1),0,0,$this->_userFaceWidth,$this->_userFaceHeight,$faceArr[$key][$i]['width'],$faceArr[$key][$i]['height']); 
				}
			}
	}
	
	public function MkFolder($path){
	    if(!is_readable($path)){
        	$this->mkFolder(dirname($path));
	        if(!is_file($path)){
	            mkdir($path,0777);
	        }
		}
	}
	
	/**
     * 销毁用户头像资源
     * 
     * CT: 2014-11-21 9:00 by QXL
     */
	public function imageUserDestroy(){
		foreach ($this->_userFace as $key => $value){
			imagedestroy($this->_userFaceInfo[$key]['source']);
		}
	}
	
	/**
     * 销毁群组头像资源
     * 
     * CT: 2014-11-21 9:00 by QXL
     */
	public function imageGroupDestroy(){
		imagedestroy($this->_groupFaceIm);
	}
}