<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends BaseController {

    /**
     * 平台首页
     * ut: 2015.06.12 17:11 by ylx
     * ut: 2015.08.10 10:16  by qy
     * ut: 2015.08.13 10:45 by QY
     */
    public function index(){
        $num_per_page = 5;

        //用户
        $userList= D('UserView')->where(array('is_del'=>'0'))->order('created_at DESC')->page(I('get.p', '1').','.$num_per_page)->select();
        // var_dump($orgList);

        //个人认证
        $ActDAL = D('Activity');

        //$Model->join('RIGHT JOIN work ON artist.id = work.artist_id')->select();


        //$act_where = array('is_del' => 0, 'is_verify' => 0);
        $act_where['is_del']= 0;
//        $act_where['is_verify']=0;
        $actList = $ActDAL
            ->join('LEFT JOIN ym_user_attr_info ON ym_activity.user_guid= ym_user_attr_info.user_guid')
            ->where($act_where)
            ->order('updated_at desc')
            ->page(I('get.p', '1').','.$num_per_page)
            ->field('ym_activity.*,ym_user_attr_info.realname')
            ->select();



        //app更新
        $app_upload_model = M('app_upload');
        $app_upload_list =$app_upload_model->query("SELECT A1.* 
                                                        FROM ym_app_upload AS A1 
                                                        INNER JOIN (SELECT A.type,A.updated_at 
                                                        FROM ym_app_upload AS A 
                                                        LEFT JOIN ym_app_upload AS B 
                                                        ON A.type = B.type 
                                                        AND A.updated_at <= B.updated_at 
                                                        GROUP BY A.type,A.updated_at 
                                                        HAVING COUNT(B.updated_at) <= 1 
                                                        ) AS B1 
                                                        ON A1.type = B1.type 
                                                        AND A1.updated_at = B1.updated_at 
                                                        ORDER BY A1.type,A1.updated_at DESC");
        if($app_upload_list){
            $list = array();
            foreach($app_upload_list as $key => $value){
                if(in_array($value['type'], array(1,3,7))){
                    if(!isset($list[0])){
                        $list[0] = array();
                    }
                    array_push($list[0], $value);
                }
                if(in_array($value['type'], array(6,8,4))){
                    if(!isset($list[1])){
                        $list[1] = array();
                    }
                    array_push($list[1], $value);
                }
                if(in_array($value['type'], array(9,11,2))){
                    if(!isset($list[2])){
                        $list[2] = array();
                    }
                    array_push($list[2], $value);
                }
                if(in_array($value['type'], array(10,12,5))){
                    if(!isset($list[3])){
                        $list[3] = array();
                    }
                    array_push($list[3], $value);
                }
            }
        }

        // 获取反馈
        $model_opinion = D('Opinion');
        $list_opinion = $model_opinion->where('is_del = 0')
                                    ->order('created_at DESC')
                                    ->page(I('get.p', '1').','.$num_per_page)->select();

         // 使用page类,实现分类
        //$count      = D('OrgView')->where(array('is_del' => '0'))->count();// 查询满足要求的总记录数
        //$Page       = new \Think\Page($count,$num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        //$show       = $Page->show();// 分页显示输出
        $this->assign('actList',$actList);
        $this->assign('list_opinion',$list_opinion);
        $this->assign('app_upload_list',$list);
        $this->assign('userList',$userList);
        //$this->assign('page',$show);
        $this->assign('meta_title', '后台首页');
        $this->display();
    }
    
    /**
     * 显示数据库表
     *
     * CT: 2014-11-20 09:09 BY YLX
     */
    public function show_table()
    {
        $table = I('get.table');

        $list = M($table)->select();
        $keys = array_keys($list[0]);
//        var_dump($list, $keys);
        $this->assign('keys', $keys);
        $this->assign('list', $list);
        $this->display();
    }
}