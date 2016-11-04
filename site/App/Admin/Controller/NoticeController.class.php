<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;

vendor('getui.YmPush');

/**
 * 通知管理控制器 
 * CT: 2015-05-07 15:00 by wangleiming
 *
 */
class NoticeController extends BaseController{

	public function __construct(){
		parent::__construct();
	}

    /**
     * 获取通知列表
     *
 	 * CT: 2015-05-07 15:00 by wangleiming
     */
	public function index(){
		
        //每页显示数量, 从配置文件中获取
        $num_per_page = C('NUM_PER_PAGE');
        
        // 实例化模型
        $model = D('Notice');
        
        // 获取通知列表
        $where = array('is_del'=>0, 'is_internal'=>'1', 'from_guid' => C('ORG_GUID'));
        $list = $model->where($where)
                      ->order('updated_at desc')
                      ->page(I('get.p', '1').','.$num_per_page)->select();
        
        // 使用page类,实现分类
        $count      = $model->where($where)->count();// 查询满足要求的总记录数
        $Page       = new \Think\Page($count,$num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出

        // 渲染模板
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('list', $list);
        $this->assign('meta_title', '通知管理');
        $this->display();
	}
	
	/**
	 * 新增通知
	 * 
	 * CT: 2015-05-07 15:00 by wangleiming
	 *
	 */
	public function add(){
		if(IS_POST){
			$time = time();
            // 获取数据
            $data = array();
            $data['guid'] = create_guid();
            $data['title'] = I('post.title');
            $data['is_internal'] = 1;
            $data['from'] =  C('NOTICE_FROM_NAME');
            $data['from_guid'] = C('ORG_GUID');
            $data['from_type'] = 1;
            $data['content'] = I('post.content');
            $data['created_at'] = $time;
            $data['updated_at'] = $time;
			$data['is_multiple'] = 1;
			$data['internal_status'] = I('post.status');

            // 实例化模型
            $model = D("Notice");
            
            // 创建数据对像
            if (!$model->create($data)) {
                exit($this->error($model->getError()));
            }
            
            // 保存到数据库
            $res = $model->add();
            if($res){
                
                // 判断status是否为1, 若为1则发送通知
                if ($data['internal_status'] == '1'){
                    $this->save_msg($data['guid'], $data['title']);
                }
                
                $this->success('添加成功', U('Notice/index'));
            }else{
                $this->error(' 添加失败',U('Notice/add'));
            }
            exit;
        }
		$this->display();
	}
	
	/**
	 * 发送消息 
	 *
	 * CT: 2014-12-04 14:14 by QXL
	 */
	public function send(){
	    if(IS_AJAX){
	        $guid=I('post.guid');
			$time = time();
	        if(M('Notice')->where(array('guid'=>$guid))->save(array('updated_at'=>$time,'status'=>1))){
				$title = M('Notice')->where(array('guid' => $guid))->getField('title', true);
				$this->save_msg($guid, $title[0]);
	            $this->ajaxReturn(array('code'=>'200','Msg'=>'发送成功', 'time' => date('Y-m-d H:i', $time)));
	        }else{
	            $this->ajaxReturn(array('code'=>'201','Msg'=>'发送失败'));
	        }
	    }else{
	        $this->error('非法请求');
	    }
	}
	
	public function save_msg($guid, $title){
        //保存聊天记录
		$time = time();
	   	$msg = array(
            'from_id'  => C('ADMIN_GUID'),
            'from_name'  => C('ADMIN_NAME'),
            'from_iconID' => '',
            'to_id'    => '',
            'to_name'    => '',
            'to_iconID'    => '',
            'content'    => array('title' => $title, 'url' => u_abs('Mobile/Notice/index/', array('guid' => $guid))),
            'send_time'  => $time,
            'msg_type'  => '11101',
            'type' => C('MESSAGE.ADMIN_NOTICE'),
            'is_read' => 0
        );
		$push = new \YmPush();
		$res = $push->pushMessageToApp(json_encode($msg));
        // 更改发送消息发送状态为1
        D('Notice')->where(array('guid'=>$guid))->setField(array('internal_status' => '1', 'updated_at' => time()));
	}
	
	public function view(){
		$guid = I('get.guid');
		$notice = D('Notice')->where(array('guid' => $guid, 'is_del' => 0))->find();
		if(!$notice){
			$this->error('通知不存在或者已经被删除', U('Notice/index'));
		}
		$this->assign('notice', $notice);
		$this->display();
	}

	public function del(){
		$guid = I('get.guid');
		$notice = D('Notice')->where(array('guid' => $guid, 'is_del' => 0))->find();
		if(!$notice){
			$this->error('通知不存在或者已经被删除', U('Notice/index'));
		}
		$data = array(
			'is_del' => 1,
			'updated_at' => time(),
		);
		$res = D('Notice')->where(array('guid'=>$guid, 'internal_status' => 0))->setField($data);
		if($res){
			$this->success('删除成功', U('Notice/index'));
       	}else{
           	$this->error('删除失败');
		}
	}

	
	public function edit(){
		$guid = I('get.guid');
		$notice = D('Notice')->where(array('guid' => $guid, 'is_del' => 0))->find();
		if(!$notice){
			$this->error('通知不存在或者已经被删除', U('Notice/index'));
		}
		if(IS_POST){
			$data = array(
				'title' => I('post.title'),
				'content' => I('post.content'),
				'updated_at' => time(),
			 	'internal_status' => I('post.status') ? 1 : 0,	
			);
			$res = D('Notice')->where(array('guid'=>$guid))->setField($data);
			if($data['internal_status'] && $res){
				$this->save_msg($guid, $data['title']);
			}
            if($res){
				$this->success('添加成功', U('Notice/index'));
            }else{
                $this->error(' 添加失败',U('Notice/edit'));
			}
			exit();
		}
		$this->assign('notice', $notice);
		$this->display();
	}	
	
	/**
     * Ueditor图片上传插件
     *
     * CT: 2014-11-24 17:07 by ylx
     */
    public function ueditor(){
        $data = new \Org\Util\Ueditor();
        echo $data->output();
    }
    


}
