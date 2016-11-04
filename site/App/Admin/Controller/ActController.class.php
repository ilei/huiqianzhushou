<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Model;

class ActController extends BaseController{

    /**
     * 详细列表页
     * CT: 2015-08-12 16:49 by QY
     *
     */
    public function index(){
        //         每页显示数量, 从配置文件中获取
        $num_per_page = C('NUM_PER_PAGE', null, 20);
        $act_model = D('Activity');
        // $act_where = array('is_del' => 0);
        $act_where['is_del'] = 0;
        $act_where['status'] = array('NEQ',0);
//        $act_where['is_verify'] = 0;
        $keyword =  I('post.search');
        if(!empty($keyword)) {
            $act_where['_string'] = "(name like '%$keyword%')";
        }

        $act_list = $act_model
            ->join('LEFT JOIN ym_user_attr_info ON ym_activity.user_guid= ym_user_attr_info.user_guid')
            ->where($act_where)
            ->order('updated_at desc')
            ->page(I('get.p', '1').','.$num_per_page)
            ->field('ym_activity.*,ym_user_attr_info.realname')
            ->select();

        //$eee =  M("Activity")->getLastSql();
        // 使用page类,实现分类
        $count      = $act_model->where($act_where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出



        $this->assign('act_list',$act_list);
        $this->assign('page',$show);
        $this->display();
    }

    //用户信息详细页
    public function content(){
        $user_guid = $_GET['user_guid'];
        if(empty($user_guid)) {
            $this->error('用户不存在.');
        }
        $user_model = D('User');
        $user_info = $user_model->getDetailForWeb($user_guid);
        if(empty($user_info)) {
            $this->error('用户不存在.');
        }
        //获取用户实名认证信息
        $user_attribute_model = D('UserAttribute');
        $user_attribute_info = $user_attribute_model->where(array('user_guid' => $user_guid))->find();

        $this->assign('user_attribute_info',$user_attribute_info);
        $this->assign('user_info',$user_info);
        $this->display();
    }

    /**
     * 用户姓名认证审核页
     * CT 2015-08-12 17:44 by QY
     */
    public function verify(){

        //获取用户个人信息
        $user_model = D('UserView');
        $act_model = D('Activity');
        //$user_attribute_model = D('UserAttribute');
        $act_guid = $_GET['act_guid'];
        //$user_info = $user_model->getDetailForWeb($user_guid);
        $act_info = $act_model->where(array('guid'=>$act_guid))->find();
        $user_guid = $act_info['user_guid'];
        $user_info = $user_model->where(array('guid'=>$user_guid))->find();
        $str=htmlspecialchars_decode($act_info['content']);
        //获取用户公司信息
        //$user_company_name = $user_model->getCompanyNames($user_guid);
        //获取用户社团信息
        //$user_org_name = $user_model->getOrgNames($user_guid);
        //获取用户实名认证信息
        //$user_attribute_info = $user_attribute_model->where(array('user_guid' => $user_guid))->find();

        //$this->assign('user_attribute_info',$user_attribute_info);
        //$this->assign('user_org_name',$user_org_name);
        //$this->assign('user_company_name',$user_company_name);
        $this->assign('act_info_w',$act_info);
        $this->assign('str',$str);
        $this->assign('user_info_w',$user_info);
        $this->display();
    }

    /**
     * 活动审核通过
     * CT: 2015-08-13 10:24 by QY
     */
    public function verify_pass(){

        $act_model = D('Activity');
        if($_GET){
            $act_guid = I('get.act_guid');
            $data = array(
//                'status' => 1,
                'is_verify' => 1, //0 未审核 1 已审核 2 已提交 3 已拒绝
                'updated_at'       => time()
            );
            $act_res = $act_model->where(array('guid' => $act_guid))->data($data)->save();
            if($act_res){
                $this->success('活动审核成功',U('Act/index'));
                exit();
            }else{
                $this->error('活动审核失败，请重新操作。');
                exit();
            }
        }else{
           $this->error('数据传输错误，请重新操作。');
            exit();
        }
    }

    /**
     * 活动审核 拒绝通过
     * CT:2015-08-13 10:25 by QY
     */
    public function verify_refuse(){

        $act_model = D('Activity');
        //$act_attribute_model = D('UserAttribute');
        if($_POST){
            $act_guid = $_POST['act_guid'];
            $data_2['refuse_reason'] = htmlspecialchars(I('post.identity_refuse_reason'));
            $data_2['is_verify'] = '3';//0 未认证 1 已认证 2 已提交 3 已拒绝
            $data_2['status'] = '0';//0 未发布
            $data_2['updated'] = time();//
            //更新用户实名认证状态字段
            $act_res = $act_model->where(array('guid' => $act_guid))->data($data_2)->save();
            //将认证拒绝理由添加到数据库
            //$user_attribute_res = $user_attribute_model->where(array('user_guid' => $user_guid))->data($data_1)->save();
            if($act_res){
                $this->success('拒绝状态提交成功',U('Act/index'));
            }else{
                $this->error('拒绝状态提交失败，请重新操作。');
            }
        }
    }

    //用户锁定功能处理
    public function is_lock(){
        $user_model = D('User');
        $user_guid = I('post.user_guid');
        $lock_status = $user_model->field('is_active')->where(array('guid' => $user_guid))->find();
        if($lock_status['is_active'] == 1){
            $data['is_active'] = 0;
        }else{
            $data['is_active'] = 1;
        }
        $res = $user_model->where(array('guid' => $user_guid))->data($data)->save();
        if($res){
            $data['status'] = 'ok';
            $this->ajaxReturn($data);
        }else{
            $data['status'] = 'ko';
            $this->ajaxReturn($data);
        }
    }

    //用户删除功能  ————————无事务不是很给力
    public function del_user(){
        $user_model = D('User');//用户表 guid
        $org_group_member_model = D('OrgGroupMembers');//用户社团关系表  user_guid
        $contact_model = D('Contacts');//好友表  user_guid_1 user_guid_2
        $org_group_members_log_model = D('OrgGroupMembersLog');//社团分组变动log表  user_guid
        $org_members_log_model = D('OrgMembersLog');//社团成员变动log表  user_guid
        $user_device_model = D('UserDevice');//用户token对应表 user_guid
        $user_interest_model = D('UserInterest');//用户兴趣表 user_guid
        $time_line_model = D('UserTimeline');//用户时间轴表 user_guid
        $user_guid = I('get.user_guid');

        $res1 = $user_model->where(array('guid' => $user_guid))->delete();
        if($org_group_member_model->where(array('user_guid' => $user_guid))->find()){
            $org_group_member_model->where(array('user_guid' => $user_guid))->delete();
        }
        if($contact_model->where("user_guid_1 ='".$user_guid."' or user_guid_2 ='".$user_guid."'")->find()){
            $contact_model->where("user_guid_1 ='".$user_guid."' or user_guid_2 ='".$user_guid."'")->delete();
        }
        if($org_group_members_log_model->where(array('user_guid' => $user_guid))->find()){
            $org_group_members_log_model->where(array('user_guid' => $user_guid))->delete();
        }
        if($org_members_log_model->where(array('user_guid' => $user_guid))->find()){
            $org_members_log_model->where(array('user_guid' => $user_guid))->delete();
        }
        if($user_device_model->where(array('user_guid' => $user_guid))->find()){
            $user_device_model->where(array('user_guid' => $user_guid))->delete();
        }
        if($user_interest_model->where(array('user_guid' => $user_guid))->find()){
            $user_interest_model->where(array('user_guid' => $user_guid))->delete();
        }
        if($time_line_model->where(array('user_guid' => $user_guid))->find()){
            $time_line_model->where(array('user_guid' => $user_guid))->delete();
        }
        if($res1){
            $this->success('用户删除成功了.');
        }else{
            $this->error('用户删除失败了.');
        }
    }
    //活动举报
    public function report(){

         //         每页显示数量, 从配置文件中获取
        $num_per_page = C('NUM_PER_PAGE', null, 20);
        $act_model = D('Report');
        $act_where['ym_report.is_del'] = 0;
        $act_list = array();
        $act_list = $act_model
            ->join('LEFT JOIN ym_activity ON ym_report.obj_guid= ym_activity.guid')
            ->where($act_where)
            ->order('ym_report.updated_at desc')
            ->page(I('get.p', '1').','.$num_per_page)
            ->field('ym_report.*,ym_activity.name,ym_activity.status')
            ->group('ym_report.obj_guid')
            ->select();
            $act_count = $act_model->field('count(guid) as count,obj_guid')->group('obj_guid')->order('ym_report.updated_at desc')->select();
            foreach ($act_list as $key => $value) {
                foreach ($act_count as $count => $val) {
                    if ($value['obj_guid']==$val['obj_guid']) {
                        $act_list[$key]['count'] =$val['count'];
                    }            
                }
            }
        $count = count($act_count);
        // 使用page类,实现分类
        // $count      = $act_model->where($act_where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出



        $this->assign('act_list',$act_list);
        $this->assign('page',$show);
        $this->display();
    }
    //活动举报详情
    public function report_desc(){

         //         每页显示数量, 从配置文件中获取
        $num_per_page = C('NUM_PER_PAGE', null, 20);
        $act_model = D('Report');
        $act_guid = I('get.guid');
        $act_where['ym_report.is_del'] = 0;
        $act_where['ym_report.obj_guid'] = $act_guid;
        $act_list = array();
        $act_list = $act_model
            ->join('LEFT JOIN ym_activity ON ym_report.obj_guid= ym_activity.guid')
            ->where($act_where)
            ->order('updated_at desc')
            ->page(I('get.p', '1').','.$num_per_page)
            ->field('ym_report.*,ym_activity.name,ym_activity.status')
            ->select();

        foreach ($act_list as $key => $value) {
                $check = M('ReportReasonCon')->where(array('report_guid'=>$value['guid']))->field('reason_content')->select();
                $act_list[$key]['req'] =$check;
                $act_list[$key]['len'] = count($act_list[$key]['req']);
            }
            // var_dump($act_list);
        // 使用page类,实现分类
        $count      = $act_model->where($act_where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出



        $this->assign('act_list',$act_list);
        $this->assign('page',$show);
        $this->display();
    }


    /**
     * 关闭活动
     * CT 2015.09.21 09:55 by manonloki
     * UT 2015.09.23 17:45 BY MANONLOKI ActivityMange -> Act
     */
    public function close_activity()
    {
      
        //获取参数
        $aid = I('get.guid');

        if (empty($aid)) {
            $this->error('参数为空.');
            // $this->ajaxReturn(array(
            //     'status' => C('ajax_failed'),
            // ), 'json');
        }

        //更新数据
        $update_result = D("Activity")
            ->where(array(
                'guid' => $aid
            ))
            ->save(array(
                'status' => '3',
                'updated_at' => time(),
                'is_verify' => '1'
            ));

        if ($update_result === false) {
            // $this->ajaxReturn(array(
            //     'status' => C('ajax_failed'),
            // ), 'json');
            $this->error('活动关闭失败.');
        } else {
            // $this->ajaxReturn(array(
            //     'status' => C('ajax_success'),
            // ), 'json');
            $this->success('活动关闭成功.',U('Act/report'));
        }


    }

}


