<?php
namespace Home\Controller;

class DispatcherController extends BaseController
{

    public function  __construct()
    {
        parent::__construct(false);
    }

    public function event()
    {
        if (IS_GET) {

            $id = I("get.id");
            $type = I("get.t");
            $realID = event_id_decode($id);
            $model_activity = M("Activity");
            $activity = $model_activity
                ->where(array(
                    'id' => $realID
                ))
                ->field(array(
                    'guid' => 'guid'
                ))
                ->find();

            if (!empty($activity)) {

                layout(false);

                //跳转页面
                if (is_mobile_request() || $type == 'm') {
                    $url = U('Mobile/Activity/view', array('guid' => $activity['guid']), false, true, true);
                     header("Location:".$url);
                } else {
                    $url = U("Home/Act/preview", array("guid" => $activity['guid']), false, true, true);
                    header("Location:".$url);
                }

            } else {
                $this->error("错误的访问", U("Home/Index/index"));
            }

        } else {
            $this->error("错误的访问", U("Home/Index/index"));
        }
    }

    public function download(){
        $type=I('get.t');//  type: a  android / i ios

        if(empty($type)){
            $this->error('错误的下载地址');
        }
        //查询条件
        $condition=array();
        $condition['status']=1;//已发布
        $condition['is_del']=0;//未被删除
        //获取版本号
        if($type==='a'){
            $condition['type']=APP_DEBUG?2:1;
        }else if($type==='i') {
            $condition['type']=APP_DEBUG?6:5;
        }

        //查询APP信息 更新时间倒序
        $appInfo=M('AppUpload')
            ->where($condition)
            ->order('updated_at desc')
            ->find();


        //获取文件信息
        $file_info=C("APP_TYPE")[$condition['type']];
        if(empty($appInfo)){
            $this->error('无效下载');
        }
        $app_path="";
        $file_path=UPLOAD_PATH.$file_info['save_path'].'/'.$file_info['name_file'].'.'.$file_info['ext'];
        $app_size=sizecount(filesize($file_path));
        //下载地址特殊处理
        if($type==='a'){
           $app_path=fixed_resource_url("/Upload".$file_info['save_path'].'/'.$file_info['name_file'].'.'.$file_info['ext']);
        }else if($type==='i') {
           $app_path=C('DOWNLOAD_ELF_IOS').$appInfo['plist_path'];
        }

        $this->assign("name",'酷客会签');//app名称
        $this->assign("version",$appInfo['external_version']);//app版本号
        $this->assign("size",$app_size);//app大小
        $this->assign("updateTime",date('Y-m-d',$appInfo['updated_at']));//更新时间
        $this->assign("download_path",$app_path);//下载地址
        $this->assign("type",$type);//类型 Android / IOS
        $this->show();
    }


    private function getCookieString(){
        if(!empty($_COOKIE)){
            $cookie_string='';
            foreach ($_COOKIE as $k=>$v) {
                $cookie_string.=$k.'='.$v.';';
            }
            return $cookie_string;
        }else{
            return '';
        }

    }

}
