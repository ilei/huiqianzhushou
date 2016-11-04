<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/2
 * Time: 下午4:38
 */

namespace Admin\Controller;

use Think\Controller;
use Admin\Behavior\LoadConfigBehavior;

class SettingsController extends Controller
{

    public function index()
    {
        $this->display();
    }

    //加载数据
    public function load_data()
    {
        //父级ID
        $parent_id = I('post.pid', '');

        //获取配置
        $settings = M('AppSettings')
            ->where(array(
                'parent_id' => $parent_id
            ))
            ->order('update_at desc,category asc,type asc')
            ->select();


        $parent_name = "";

        if (!empty($parent_id)) {
            $item = M("AppSettings")->where(array('id' => $parent_id))->find();
            if (!empty($item)) {
                $parent_name = empty($item['description']) ? $item['key'] : $item['description'];
            }
        }


        //返回数据
        $this->ajaxResponse(array(
            'pid' => $parent_id,
            'pname' => $parent_name,
            'items' => $settings
        ), "json");
    }

    //检查重复的Key
    public function  check_duplicate_key()
    {
        $parent_id = I('post.pid', '');
        $key = I('post.key');

        $count = M('AppSettings')
            ->where(array(
                'parent_id' => $parent_id,
                'key' => $key
            ))
            ->count();

        $this->ajaxResponse(array(
            'duplicate' => ($count > 0)
        ), 'json');
    }

    //添加配置
    public function add_item()
    {
        $parent_id = I('post.pid', '');
        $key = I('post.key', '');
        $value = I('post.value', '');
        $description = I('post.description', '');
        $type = I('post.type');
        $category=I('post.category');

        $data = array(
            'parent_id' => $parent_id,
            'key' => $key,
            'value' => ($type == 'Array' || $type == 'Object') ? '' : $value,
            'description' => $description,
            'type' => $type,
            'category'=>$category,
            'created_at'=>time(),
            'updated_at'=>time()
        );


        $insertRes = M('AppSettings')->data($data)->add();

        if ($insertRes) {
            $id = M('AppSettings')->getLastInsID();

            $item = M('AppSettings')
                ->where(array(
                    'id' => $id
                ))
                ->find();

            $this->ajaxResponse(array(
                'isSuccess' => true,
                'data' => $item
            ));

        } else {
            $this->ajaxResponse(array(
                'isSuccess' => false
            ), 'json');

        }
    }

    //更新配置
    public function update_item()
    {
        $id = I('post.id', '');
        $value = I('post.value', '');
        $description = I('post.description', '');
        $type = I('post.type', '');
        if (empty($id)) {
           return;
        }

        $data = array(
            'id' => $id,
            'value' => $value,
            'description' => $description,
            'type' => $type,
            'updated_at'=>time()
        );

        $saveRes = M('AppSettings')
            ->save($data);

        if (empty($saveRes)) {
            $this->ajaxResponse(array('isSuccess' => false), 'json');
        } else {
            $this->ajaxResponse(array('isSuccess' => true), 'json');
        }
    }

    public function  reload_settings()
    {

        if (IS_POST) {
            $behavior = new LoadConfigBehavior();

            $behavior->reloadSettings();
        }
    }


    //删除配置
    public function  delete_item()
    {
        $id = I('post.id', '');

        if (empty($id)) {
           return;
        }


        //获取所有的数据源
        $source = M('AppSettings')->select();

        $idList = $this->findChild($source, $id);


        $delRes = M('AppSettings')
            ->where(array(
                'id' => array('IN', $idList)
            ))
            ->delete();

        if (empty($delRes)) {
            $this->ajaxResponse(array('isSuccess' => false), 'json');

        } else {
            $this->ajaxResponse(array('isSuccess' => true), 'json');
        }

    }

//递归所有子节点
    private function  findChild($source, $id)
    {
        $result = array();
        $result[] = $id;

        //遍历
        foreach ($source as $value) {


            if ($value['parent_id'] == $id) {
                //继续遍历子节点是否存在下一节点
                $result = array_merge($result, $this->findChild($source, $value['id']));
            }
        }
        //返回结果
        return $result;
    }


}