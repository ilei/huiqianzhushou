<?php
namespace Admin\Controller;

use Think\Controller;

class OpinionController extends BaseController{

    //意见列表页面
    public function index(){

        //         每页显示数量, 从配置文件中获取
        $num_per_page = C('NUM_PER_PAGE');

        $model_opinion = D('Opinion');
        // 获取意见列表
        $list_opinion = $model_opinion->where('is_del = 0')
                                    ->order('created_at DESC')
                                    ->page(I('get.p', '1').','.$num_per_page)->select();

        // 使用page类,实现分类
        $count      = $model_opinion->where('is_del = 0')->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出

        $this->assign('list_opinion',$list_opinion);
        $this->assign('page',$show);
        $this->display();
    }





    //意见详情页面
    public function content(){

        $model_opinion = D('Opinion');
        $opinion_guid = I('get.opinion_guid');
        //dump($opinion_guid);

        $opinion_where = 'guid = "'.$opinion_guid.'" and is_del = 0';
        $opinion_info = $model_opinion->where($opinion_where)->find();


        $this->assign('opinion_info',$opinion_info);
        $this->display();
    }

    public function opinion_delete(){
        $model_opinion = D('Opinion');
        $opinion_guid = I('get.opinion_guid');

        $opinion_where = 'guid ="'.$opinion_guid.'"';
        $result_opinion = $model_opinion->where($opinion_where)->delete();

        if($result_opinion){
            $this->success('数据删除成功',U('Opinion/index'));
        }else{
            $this->error('数据删除错误',U('Opinion/index'));
        }
    }
}