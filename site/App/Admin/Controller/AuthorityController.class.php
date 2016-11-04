<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
/**
 * 权限控制器
 *
 * CT: 2014-12-03 15:24 by QXL
 *
 */
class AuthorityController extends BaseController{
    /**
     * show权限列表
     *
     * CT: 2014-12-04 14:00 by QXL
     */
    public function index(){
        $authorityList=M('GradeAuthority')->select();
        $this->assign('authorityList',$authorityList);
       $this->display();
    }
    
    /**
     * show权限创建页
     *
     * CT: 2014-12-04 14:00 by QXL
     */
    public function add(){
        if (I('get.guid')){
            $authorityData=M('GradeAuthority')->getByGuid(I('get.guid'));
            $this->assign('authorityData',$authorityData);
        }
        $this->display();
    }
    
//     public function check_name(){
//         $AuthorityInfo=M('GradeAuthority')->getByName(I('post.name'));
//         if(empty($AuthorityInfo)){
//             echo 'true';
//             exit();
//         }else{
//             echo 'false';
//             exit();
//         }
//     }
    
    /**
     * ajax保存权限
     *
     * CT: 2014-12-04 14:00 by QXL
     */
    public function save_authority(){
        if(IS_AJAX){
            $AuthorityM = D("GradeAuthority");
            $data=array();
            $data['name']=I('post.name');
            $data['key']=I('post.key');
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
}