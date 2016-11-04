<?php
namespace Admin\Controller;

use Think\Controller;
use Think\Upload;

/**
 * APP 更新管理列表页
 *
 * CT: 2015-01-06 10:00 by RTH
 *
 */
class UploadController extends BaseController
{

    /**
     * 列表页
     * UT: 2015-07-15 12:00 BY YLX
     */
    public function index()
    {
        //每页显示数量, 从配置文件中获取
        $num_per_page = C('NUM_PER_PAGE', null, 10);

        $app_upload_model = M('app_upload');
        $app_upload_where = array('is_del' => 0);
        if (I('get.type') && I('get.type') != 'all') {
            $app_upload_where['type'] = I('get.type');
        }
        $app_upload_list = $app_upload_model->where($app_upload_where)
            ->order('updated_at DESC')
            ->page(I('get.p', '1'), $num_per_page)
            ->select();

        // 使用page类,实现分类
        $count = $app_upload_model->where($app_upload_where)->count();// 查询满足要求的总记录数
        $Page = new \Think\Page($count, $num_per_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show();// 分页显示输出

        $this->assign('page', $show);
        $this->assign('app_upload_list', $app_upload_list);
        $this->display();
    }

    /**
     * 文件上传
     * CT： 2015-05-11 13:00 by RTH
     * UT： 2015-07-15 13:00 by ylx
     */
    public function ajax_upload()
    {
        if (IS_POST) {
            $type = I('get.type');
            if (!in_array($type, array_keys(C('APP_TYPE')))) {
                $this->ajaxResponse(array('status' => 'ko', '该类型APP不存在, 请重试.'));
                exit();
            }
            if (!$_FILES) {
                $this->ajaxResponse(array('status' => 'ko', '没有找到要上传的文件, 请重试.'));
            }

            $file_save_info = C('APP_TYPE.' . $type);
            if (empty($file_save_info)) {
                $this->ajaxResponse(array('status' => 'ko', '该类型APP不存在, 请重试.'));
            }

            //将原来文件名称改为固定样式 
            $savePath = $file_save_info['save_path'] . '/';
            $saveName = $file_save_info['name_tempfile'];
            $ext = $file_save_info['ext'];
            $old_filename = UPLOAD_PATH . $savePath . '/' . $file_save_info['name_file'] . '.' . $ext;
            $config = array(
                'exts' => array($ext),
                'rootPath' => UPLOAD_PATH,   //文件上传保存的根路径
                'savePath' => $savePath,
                'subName' => '',             //
                'saveName' => $saveName,    //文件上传的名字
                'saveExt' => $ext,         //文件的后缀名
                'replace' => true,          //可以覆盖原文件
            );
            $upload = new Upload($config);
            $info = $upload->upload();

            if ($info) {
                $new_filename = UPLOAD_PATH . $savePath . $saveName . '.' . $ext;
                $new_md5file = md5_file($new_filename);
                $old_md5file = md5_file($old_filename);
                if ($old_md5file == $new_md5file) {
                    $this->ajaxResponse(array('status' => 'ok', 'msg' => '文件没有改动,请重新上传', 'md5' => $new_md5file, 'old_md5' => $old_md5file));
                    exit();
                } else {
                    $this->ajaxResponse(array('status' => 'ok', 'msg' => '文件更改成功', 'md5' => $new_md5file, 'old_md5' => $old_md5file));
                    exit();
                }
            } else {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '文件上传失败, ' . $upload->getError()));
                exit();
            }
        }

    }

    /**
     * 新增APP更新
     * ut: 2015-07-15 17:22 by ylx
     */
    public function upload()
    {
        $this->_saveData();
        $this->display();
    }

    /**
     * 保存数据
     * @param bool|true $isNew
     * ut: 2015-07-15 17:22 by ylx
     */
    private function _saveData($isNew = true)
    {
        $app_upload_model = M('AppUpload');
        $time = time();
        if (IS_POST) {
            $type = I('post.type');

            //数据存储到数据库
            $data['guid'] = $isNew == true ? create_guid() : I('post.guid');
            $data['type'] = $type;
            $data['external_version'] = $_POST['external_version'];
            $data['internal_version'] = $_POST['version'];
            $data['content'] = htmlspecialchars($_POST['content']);
            $data['status'] = $_POST['status'];
            $data['is_force'] = $_POST['is_force'];
            $data['created_at'] = $time;
            $data['updated_at'] = $time;
            $data['plist_path'] = I('post.plist_path');
            $res = $app_upload_model->add($data, array(), true);


            if (!$res) {
                $this->error('数据提交错误!');
                exit();
            } else {

//                //QRCode Create Begin
//                $qrcode_logo = PUBLIC_PATH . '/common/images/qr_code_logo.png';
//                $qrcode_path = PUBLIC_PATH . '/common/images/';
//                $qrcode_name = "";
//                $qrcode_url = "";
//
//                //IOS
//                if ($data['type'] == 5 || $data['type'] == 6 && (!empty($data['plist_path']))) {
//                    //重新生成二维码
//
//                    $qrcode_name = 'downloadios.png';
//                    $qrcode_url = C('DOWNLOAD_ELF_IOS') . $data['plist_path'];
//
//                    //Android
//                } else if ($data['type'] == 1 || $data['type'] == 2) {
//                    $qrcode_name = 'downloadandr.png';
//                    $qrcode_url = $data['type'] == 1 ? C('DOWNLOAD_ELF_ANDROID') : C('DOWNLOAD_ELF_ANDROID_BETA');
//                }
//
//
//                if (!empty($qrcode_name) && !empty($qrcode_url)) {
//                    //判断存在文件 则删除
//                    if (is_file($qrcode_path . $qrcode_name)) {
//                        @unlink($qrcode_path . $qrcode_name);
//                    }
//                    qrcode($qrcode_url, $qrcode_path, $qrcode_name, $qrcode_logo, '5', 'Q', 2, false);
//                }
//                //QRCode Create End


                $version = $app_upload_model->where(array('is_del' => '0', 'status' => '1', 'type' => $type))
                    ->order('version DESC')->limit(1)->getField('version');
                $file_info = C('APP_TYPE.' . $type);
                $temp_file = UPLOAD_PATH . $file_info['save_path'] . '/' . $file_info['name_tempfile'] . '.' . $file_info['ext'];
                if (is_file($temp_file)) {
                    $new_file = UPLOAD_PATH . $file_info['save_path'] . '/' . $file_info['name_file'] . '.' . $file_info['ext'];
                    rename($new_file, $new_file . '-' . $version . '_' . date('YmdHis', $time));
                    $rename = rename($temp_file, $new_file);
                }

                if ($isNew && is_file($temp_file)) {
                    if (!$rename) {
                        $this->error('保存失败，请重试。');
                    }
                }
                $this->success('文件更新成功', U('Upload/index'));
                exit();
            }
        }
    }

    /**
     * 文件更新编辑页
     *
     * CT： 2015-01-07 14:30 by RTH
     * ut: 2015-07-15 17:22 by ylx
     */

    public function edit()
    {
        $app_upload_guid = I('get.guid');
        $app_upload_model = M('AppUpload');
        $app_upload_info = $app_upload_model->where('is_del = 0 and guid = "' . $app_upload_guid . '"')->find();
        if (empty($app_upload_info)) {
            $this->error('目标不存在, 请重试.');
        }
        $type = $app_upload_info['type'];
        $this->_saveData(false);
        $this->assign('type', $type);
        $this->assign('info', $app_upload_info);
        $this->display('upload');
    }

    /**
     * ajax获取对应类型的版本信息
     * ct: zyz
     * ut: 2015-07-15 16:42 by ylx
     */
    public function ajaxGetCurrentVersion()
    {
        if (IS_AJAX) {
            $type = I('post.type');
            if (empty($type)) {
                $this->ajaxResponse(array('status' => 'ko', 'msg' => '参数错误，无法获取当前版本信息，请刷新页面并重试。'));
            }
            $app_upload_model = M('AppUpload');
            $versions = $app_upload_model->where(array('is_del' => '0', 'status' => '1', 'type' => $type))
                ->order('updated_at DESC')->limit(1)->getField('guid, version, external_version');
            $versions = array_shift($versions);
            $old_version = $versions['version'];
            $external_version = $versions['external_version'];

            $this->ajaxResponse(array('status' => 'ok', 'version' => $old_version, 'version_external' => $external_version));

        } else {
            $this->ajaxResponse(array('status' => 'ko', 'msg' => '非法请求。'));
        }
    }
}