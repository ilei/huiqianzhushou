<?php
namespace Admin\Controller;
use Admin\Controller\BaseController;
class FinancingController extends BaseController {

    //正在结算列表
    public function underway_detail(){
        $detail_model = D('FinancingDetail');

        $detail_list = $detail_model
            ->where(array('status' => 0,'is_del' => 0))
            ->select();

        $this->show();
    }
}