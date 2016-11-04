<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
use Org\Util\String;
/**
 * 等级控制器
 *
 * CT: 2014-12-03 15:24 by QXL
 *
 */
class LevelController extends BaseController{
    /**
     * show等级列表
     *
     * CT: 2014-12-04 14:00 by QXL
     */
    public function index(){
        $levelList=M('GradeLevel')->order('id DESC')->select();
        $this->assign('levelList',$levelList);
        $this->display();
    }
    
    /**
     *show 创建等级页面
     *
     * CT: 2014-12-04 14:00 by QXL
     */
    public function add(){
        if (I('get.guid')){
            $levelData=M('GradeLevel')->getByGuid(I('get.guid'));
            $this->assign('levelData',$levelData);
        }
        $this->display();
    }
    
    /**
     * ajax保存等级
     *
     * CT: 2014-12-04 14:00 by QXL
     */
    public function save_level(){
        if(IS_AJAX){
            $AuthorityM = D("GradeLevel");
            $data=array();
            $data['name']=I('post.name');
            $data['sort']=I('post.sort');
            if (I('post.guid')){
                $data['updated_at']=time();
                if($AuthorityM->where(array('guid'=>I('post.guid')))->save($data)){
                    $this->ajaxReturn(array('code'=>'200'));
                }else{
                    $this->ajaxReturn(array('code'=>'201','Msg'=>'保存失败'));
                }
            }else{
                $data['guid']=create_guid();
                if($AuthorityM->create($data)){
                    if ($AuthorityM->add()){
                        $this->ajaxReturn(array('code'=>'200'));
                    }else{
                        $this->ajaxReturn(array('code'=>'201','Msg'=>'保存失败'));
                    }
                }else{
                    $this->ajaxReturn(array('code'=>'201','Msg'=>$AuthorityM->getError()));
                }
            }
        }else{
            $this->error('非法请求');
        }
    }
    
    /**
     * show权限分配页面
     *
     * CT: 2014-12-04 14:00 by QXL
     */
    public function distribution(){
        $authorityList=M('GradeAuthority')->order('created_at ASC')->select();
        $valueList=M('GradeValue')->where(array('level_guid'=>I('get.guid')))->select();
        foreach ($authorityList as $k=>$v){
            foreach ($valueList as $key=>$value){
                if(($value['authority_guid']==$v['guid'])  && ($value['level_guid']==I('get.guid'))){
                    $authorityList[$k]['value']=$value['value'];
                }
            }
        }
        $this->assign('authorityList',$authorityList);
        $this->display();       
    }
    
    /**
     * ajax保存权限分配
     *
     * CT: 2014-12-04 14:00 by QXL
     */
    public function save_distribution(){
        if(IS_AJAX){
            $accept =I('post.');
            unset($accept['level_guid']);
            $level_guid=I('post.level_guid');
            $distribution_data=array();
            foreach($accept as $key=>$value){
                $distribution_data[]=array('level_guid'=>$level_guid,'authority_guid'=>$key,'value'=>$value);
            }
            if(M('GradeValue')->addAll($distribution_data,$options=array(),$replace=true)){
                 $this->ajaxReturn(array('code'=>'200'));
            }else{
                  $this->ajaxReturn(array('code'=>'201','Msg'=>'保存失败'));
            }
        }else{
            $this->error('非法请求');
        }
    }
    
    /**
     * ajax生成配置文件
     *
     * CT: 2014-12-04 14:00 by QXL
     */
    public function create_config_file(){
        if(IS_AJAX){
            $gradeValueList=D('GradeValueView')->get_data_value_list();
            $gradeLevelList=M('GradeLevel')->select();
            $res=array();
            foreach ($gradeLevelList as $k=>$v){
                foreach($gradeValueList as $key=>$value){
                    if($v['sort']==$value['sort']){
                        $res[$v['guid']][$value['auth_key']]=$value['value'];
                    }
                }
            }
            if(file_put_contents(CONF_PATH  . 'level.php', "<?php \n return".' '.var_export($res ,true) .';') > 0){
                $this->ajaxReturn(array('code'=>'200','Msg'=>'生成成功'));
            }else{
                $this->ajaxReturn(array('code'=>'201','Msg'=>'生成失败'));
            }
        }else{
            $this->error('非法请求');
        }
    }
}