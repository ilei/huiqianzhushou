<?php
/**
 * Created by PhpStorm.
 * User: RTH
 * Date: 2015/7/9
 * Time: 17:50
 */
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Think\Image;

class RepairController extends BaseController{

    public function index(){
        $this->display();
    }

    //修复logo 70px，120px缺失
    public function repair_org_logo(){
        $org_model = D('org');
        $orgs = $org_model->field('guid,logo')->where(array('logo_70' => '0','logo_120' => '0'))->where('logo != "0"')->select();
        if(empty($orgs)){
            $this->error('无修复数据');
        }
        
        //循环生成logo。70,120缩略图
        foreach($orgs as $k=>$v){
                $logo_path = UPLOAD_PATH.$v['logo'];
                $image = new Image();
                $image->open($logo_path);

                $logo = explode('.',$v['logo']);
                $logo_70_name = $logo[0].'_70.jpg';
                $logo_120_name = $logo[0].'_120.jpg';
                $image->thumb('120', '120')->save(UPLOAD_PATH.$logo_120_name);
                $image->thumb('70', '70')->save(UPLOAD_PATH.$logo_70_name);

                $data = array('logo_70' => $logo_70_name,'logo_120' => $logo_120_name,'updated_at' => time());
                $res[] = $org_model->where(array('guid' => $v['guid']))->data($data)->save();
        }

        if(empty($res)){
            $this->error('插入数据失败');
            exit();
        }else{
            $this->success('修复成功');
            exit();
        }
    }

    /*
     * 用户姓名缺失修复
     */
    public function repair_user_name(){
        $act_user_model = D('ActivityUserinfo');
        $user_attr_info_model = D('UserAttrInfo');
        $act_user_list = $act_user_model->field('real_name,mobile')->select();
        $user_attr_info_list = $user_attr_info_model->alias('a')
            ->join('ym_user u on a.user_guid = u.guid','right')
            ->field('u.mobile,a.realname,u.guid')
            ->select();

//        var_dump($act_user_list);
//        var_dump($user_attr_info_list);die;
        foreach($user_attr_info_list as $k=>$v){
            foreach($act_user_list as $j=>$i){
                if($v['mobile'] == $i['mobile'] && $v['realname'] == ''){
                    $res = $user_attr_info_model->
                    where(array('user_guid' => $v['guid']))->
                    data(array('realname' => $i['real_name']))->save();
                }
            }
        }

        if(empty($res)){
            $this->error('修复数据失败或无相关修复数据');
            exit();
        }else{
            $this->success('修复成功');
            exit();
        }

    }
}