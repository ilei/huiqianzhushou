<?php
namespace Home\Controller;
use Controls\Control\PagerControl;
use Controls\Model\PagerControlModel;
use Home\Controller\BaseController;
use Pinq\ITraversable,Pinq\Traversable;

class ReleaseController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        layout('layout_new');
    }

    public function index()
    {
        $num_per_page = C('NUM_PER_PAGE', null, 15); // 每页显示数量, 从配置文件中获取
        $this->main = '/Public/meetelf/home/js/build/home.release.js';
        $this->css[] = 'meetelf/home/css/home.release.css';
        $session_auth = $this->kookeg_auth_data();

        //获取活动状态
        $view_status=I('get.s',1);//无参数 默认值是1 进行中的活动
        //我发布的活动列表
        $model_activity = M('Activity');
        $condition = 'a.user_guid="' . $session_auth['guid'] . '" and a.is_del=0';

        // 搜索活动名称 对应前台搜索操作
        $keyword = urldecode(I('get.k'));
        if (!empty($keyword)) {
            $condition .= " and a.name like '%$keyword%'";
        }
        //0未发布，1活动中，2已结束，3已关闭
        $page = I('post.p', '1');
        $list = $model_activity->alias('a')
            ->field('a.*')
            ->where($condition . ' and a.status = '.$view_status . ' and is_verify= ' . 1)
            ->order('a.updated_at DESC, a.start_time DESC')
            ->page($page, $num_per_page)
            ->select();
        $my = $table
            ->where(function ($row) { return $row['status'] <= 1; })
            ->orderByAscending(function ($row) { return $row['updated_at']; })
            ->select(function ($row) {
                return [
                    'name'     => $row['name'] . ' ' . $row['name']
                ];
            })
                ->asArray();
        //统计已经结束
        $list_count_over = $model_activity->alias('a')
            ->field('a.id')
            ->where($condition . ' and  a.status = 2' . ' and ' . 'a.is_verify =' . 1)
            ->count();
        //统计已关闭
        $list_count_close = $model_activity->alias('a')
            ->field('a.id')
            ->where($condition . ' and  a.status=3' . ' and ' . 'is_verify =' . 1)
            ->count();

        //统计未发布
        $list_count_debug = $model_activity->alias('a')
            ->field('a.id')
            ->where($condition . ' and a.status=0')
            ->count();

        $list_count_release = $model_activity->alias('a')
            ->field('a.id')
            ->where($condition . ' and a.status=1' . ' and  a.is_verify =' . 1)
            ->count();

        //统计未审核
        $list_count_verify = $model_activity->alias('a')
            ->field('a.id')
            //                ->where($condition . ' and a.status=1')
            ->where('a.user_guid="' . $session_auth['guid'] . '" and a.is_del=0 and status = 1' . ' and ' . 'is_verify =0' )
            ->count();


        $count       = $model_activity->alias('a')->where($condition  . ' and a.status = 1' . ' and ' . 'is_verify =1' )->count();// 查询满足要求的总记录数

        $model_page=new PagerControlModel($page,$count,$num_per_page);
        $pager_show=new PagerControl($model_page);    //输出HTML    echo $pager->fetch();
        $view_show_page = $pager_show->fetch();

        $this->assign('list', $list);

        $this->assign('viewpage',$view_show_page);
        $this->assign('listOver', $list_count_over);
        $this->assign('listClose', $list_count_close);
        $this->assign('listDebug', $list_count_debug);
        $this->assign('listRelease', $list_count_release);
        $this->assign('list_count_verify', $list_count_verify);
        $this->assign('auth',$session_auth);
        $this->assign('view_status',$view_status);

        $this->title = "活动列表";
        $this->show();

    }


    public function otherlist()
    {
        if (IS_AJAX) {

            $num_per_page = C('NUM_PER_PAGE', null, 15); // 每页显示数量, 从配置文件中获取
            $session_auth = $this->kookeg_auth_data();
            //我发布的活动列表
            $model_activity = M('Activity');

            $condition = 'a.user_guid="' . $session_auth['guid'] . '" and a.is_del=0';
            // 搜索活动名称 对应前台搜索操作
            $keyword = trim(urldecode(I('post.k')));
            if (!empty($keyword)) {
                $condition .= " and a.name like '%$keyword%'";
            }

            //统计里需要加入　keys过滤条件
            //统计已经结束
            $list_count_over = $model_activity->alias('a')
                ->field('a.id')
                ->where($condition . ' and  a.status = 2' . ' and ' . 'is_verify =' . 1)
                ->count();
            //统计已关闭
            $list_count_close = $model_activity->alias('a')
                ->field('a.id')
                ->where($condition . ' and  a.status=3' . ' and ' . 'is_verify =' . 1)
                ->count();

            //统计未发布
            $list_count_debug = $model_activity->alias('a')
                ->field('a.id')
                ->where($condition . ' and a.status=0 ')
                ->count();

            $list_count_release = $model_activity->alias('a')
                ->field('a.id')
                ->where($condition . ' and a.status=1 and ' . 'is_verify =' . 1)
                ->count();

            $list_count_verify = $model_activity->alias('a')
                ->field('a.id')
                ->where('a.user_guid="' . $session_auth['guid'] . '" and a.is_del=0 and status = 1' ." and a.name like '%$keyword%'" . ' and ' . 'is_verify =0')
                ->count();
            // 过滤活动状态 对应前台选项卡操作
            $filter_status = I('post.s', null);
            if (isset($filter_status) && $filter_status != 'all') {
                $condition .= " and a.status=$filter_status";
            }
            //0未发布，1活动中，2已结束，3已关闭
            $pages  =  I('post.p',1);

            if($_POST['s'] == '0'){
                $list = $model_activity->alias('a')
                    ->field('a.*')
                    ->where($condition. ' and a.status=0')
                    ->order('a.updated_at DESC, a.start_time DESC')
                    ->page($pages, $num_per_page)
                    ->select();
            }else if($_POST['s'] == '1'){
                $list = $model_activity->alias('a')
                    ->field('a.*')
                    ->where($condition. ' and a.status=1 and ' . 'is_verify =' . 1)
                    ->order('a.updated_at DESC, a.start_time DESC')
                    ->page($pages, $num_per_page)
                    ->select();
            }else if($_POST['s'] == '2'){
                $list = $model_activity->alias('a')
                    ->field('a.*')
                    ->where($condition. ' and a.status=2 and ' . 'is_verify =' . 1)
                    ->order('a.updated_at DESC, a.start_time DESC')
                    ->page($pages, $num_per_page)
                    ->select();
            }else if($_POST['s'] == '3'){
                $list = $model_activity->alias('a')
                    ->field('a.*')
                    ->where($condition. ' and a.status=3 and ' . 'is_verify =' . 1)
                    ->order('a.updated_at DESC, a.start_time DESC')
                    ->page($pages, $num_per_page)
                    ->select();
            }else if($_POST['s'] == '4'){
                $list = $model_activity->alias('a')
                    ->field('a.*')
                    ->where('a.user_guid="' . $session_auth['guid'] . '" and a.is_del=0 and status = 1' ." and a.name like '%$keyword%'" . ' and ' . 'is_verify =0')
                    ->order('a.updated_at DESC, a.start_time DESC')
                    ->page($pages, $num_per_page)
                    ->select();

            }

            //渲染到页面

            if($_POST['s'] == '4'){
                $list_count = $model_activity->alias('a')->where('a.user_guid="' . $session_auth['guid'] . '" and a.is_del=0 and status = 1' ." and a.name like '%$keyword%'" . ' and ' . 'is_verify =0')->count();// 查询满足要求的总记录数;
                $model = new PagerControlModel($pages,$list_count,$num_per_page);
                $pager = new PagerControl($model);
                $view_show_page = $pager->fetch();
            }else{
                if($_POST['s'] == '0'){
                    $list_count = $model_activity->alias('a')->where($condition. ' and a.status='.$_POST['s'])->count();// 查询满足要求的总记录数;
                }else{
                    $list_count = $model_activity->alias('a')->where($condition. ' and a.status='.$_POST['s'].' and ' . 'is_verify =1')->count();// 查询满足要求的总记录数;
                }

                $model_page=new PagerControlModel($pages,$list_count,$num_per_page);
                $pager_show=new PagerControl($model_page);    //输出HTML    echo $pager->fetch();
                $view_show_page = $pager_show->fetch();
            }

            $view_html = $this->viewListHtml($list);
            $this->ajaxResponse(array('status' => 'ok',
                'msg' => '加载成功。',
                'data' => $view_html,
                'viewpage'=>$view_show_page,
                'countover'=>$list_count_over,
                'countclose'=>$list_count_close,
                'countdebug'=>$list_count_debug,
                'countrelease'=>$list_count_release,
                'countverify'=>$list_count_verify
            ));
            $this->display();

        }
    }


    function  viewListHtml($list)
    {
        $this->assign('list', $list);
        $_html = $this->fetch('_otherlist_');
        return $_html;

    }

}
