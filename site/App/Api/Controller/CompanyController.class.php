<?php
namespace Api\Controller;

use Api\Controller\BaseController;
use Think\Upload;
use Common\Model\UserModel;
use Org\Api\YmChat;

/**
 * 公司 控制器
 *
 * CT: 2014-10-13 16:00 by YLX
 *
 */
class CompanyController extends BaseUserController
{
    
    /**
     * 增加公司
     * 
     * CT: 2014-10-17 10:16 by YLX
     */
    public function add() {
        $this->check_request_method('post');
        if (IS_POST){
            $data = $this->_request_params;
            $data['user_guid'] = $this->user_info['guid'];
            if (empty($data['name']) || empty($data['position'])) {
                $this->output_error('10003');
            }
            foreach($data as $k => $v) {
                $data[$k] = trim($v);
            }

            $model = D('UserCompany');
            $data['guid'] = create_guid();
            $data['is_verify'] = 1;
            $data['created_at'] = time();
            $data['updated_at'] = time();
            $res = $model->data($data)->add();
            
            if ($res){
                $this->output_data(array('guid'=>$data['guid']));
            } else {
                $this->output_error('10011');
            }
        } else {
            $this->output_error('10025');
        }
    }
    
    /**
     * 编辑公司
     * 
     * CT: 2014-10-17 10:16 by YLX
     */
    public function edit() {
        $this->check_request_method('put');
        if (IS_PUT){
            $params = $this->_request_params;
            $cid = I('get.guid');
            if(empty($cid)) {
                $this->output_error('10003');
            }
            if (empty($params['name']) || empty($params['position'])) {
                $this->output_error('10003');
            }
            $data = array(
                'name' => $params['name'],
                'position' => $params['position'],
                'tel' => $params['tel'],
                'industry_guid' => $params['industry_guid'],
                'updated_at' => time()
            );

            $model = D('UserCompany');
            $res = $model->is_valid($data);
            
            if (!$res){
                $this->output_error('10003');
            }

            $res = $model->where(array('guid'=>$cid))->data($data)->save();
            
            if ($res){
                $this->output_data(array('guid'=> $cid));
            } else {
                $this->output_error('10011', $cid);
            }
        } else {
            $this->output_error('10025');
        }
    }
    
    /**
     * 删除公司
     * 
     * CT: 2014-10-17 10:16 by YLX
     */
    public function delete() {
        $this->check_request_method('delete');
        if(IS_DELETE) {
            $guid = I('get.guid');//$this->_request_params['guid'];//I('post.guid');
            if (empty($guid)) $this->output_error('10003');

            $res = M('UserCompany')->where(array('guid' => $guid))->find();
            if(empty($res)) $this->output_error('10003');

            $res  = M('UserCompany')->where(array('guid' => $guid))->delete();
            if ($res) {
                $this->output_data();
            } else {
                $this->output_error('10011');
            }
        } else {
            $this->output_error('10025');
        }
    }
}