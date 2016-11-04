<?php
namespace Home\Controller;
use Home\Controller\BaseController;

class FinancingController extends BaseController{

    public function __construct(){
        parent::__construct();
        layout('layout_new');
        $this->css[] = 'meetelf/home/css/home.financing.css';

        $this->title = '等待结算';
    }

    public function index(){
        $this->main = '/Public/meetelf/home/js/home.financing.index.js';
        $this->get_not_settle_act();
        $this->show();
    }


    public function get_not_settle_act($ck_act_guids = '',$action = ''){

        $activity_model = D('Activity');
        $order_model = D('Order');
        $financing_activity_model = D('FinancingActivity');
        $user_info = session('auth');
        $user_guid = $user_info['guid'];

        //查询已经结算或结算完毕的活动
        $fin_act_guids = $financing_activity_model->field('guid')->where(array('user_guid' => $user_guid))->select();
        foreach ($fin_act_guids as $k=>$v) {
            $fin_guids[] = $v['guid'];
        }

        //支付表查询付费活动guid(全部活动  包含已结束、已关闭。)
        $order_list = $order_model  //获取付费活动guid
            ->field('target_guid,guid')
            ->where(array(
                'seller_guid' => $user_guid,
                'goods_price' => array('gt',0),
                'status' => '1',//支付成功
                'is_del' => 0,
                'target_guid' => array('NOTIN',$fin_guids)
            )
        )
        ->select();

        if(empty($ck_act_guids)){  //判断是否是从结算详情页查询
            if(!empty($order_list)){
                foreach($order_list as $k=>$v){
                    $act_guids[] = $v['target_guid'];
                }
            }
            //
            if(empty($act_guids)){

                $this->assign('activity_list','');
                $this->assign('tbody',$this->fetch('_financing_tbody'));
            }

        }else{
            $act_guids = $ck_act_guids;
        }

        //判断页面
        if($action != ''){
            $ck_act_status = 'set_account';
        }


        //查询活动信息
        $activity_list = $activity_model  //去除已关闭的活动，全部已结束活动的数据
            ->field('guid,name,end_time')
            ->where(array(
                'user_guid' => $user_guid,
                'guid' => array('in',$act_guids),
                'is_del' => '0',
                'status' => '2'))
                ->select();

        foreach($activity_list as $k=>$v){
            $one_act_guids[] = $v['guid'];
        }

        //查询单独活动的金额
        $order_money_list = $order_model
            ->field('total_price as money,target_guid')
            ->where(array(
                'seller_guid' => $user_guid,
                'goods_price' => array('gt',0),
                'status' => '1',//支付成功
                'is_del' => 0,
                'target_guid' => array('in',$one_act_guids)
            ))
            ->select();


        if(!empty($order_money_list)){ //组装数据
            foreach ($order_money_list as $k=>$v) {
                $moneys[$v['target_guid']][] = $v['money']/100;
                $befor_act_guids[] = $v['target_guid'];
            }
        }


        foreach($moneys as $k=>$v){
            $act_moneys = 0;
            foreach($v as $i=>$j){
                $act_moneys += $j;
            }
            $sum_act_moneys[$k][] = $act_moneys;
        }
        $sum_money = '';
        foreach ($activity_list as $k=>$v) {
            $activity_list[$k]['money'] = $sum_act_moneys[$v['guid']][0];//单独票价
            $sum_money += $activity_list[$k]['money'];//总计
        }

        $this->assign('act_guids',$befor_act_guids);
        $this->assign('ck_act_status',$ck_act_status);//页面区分
        $this->assign('sum_money',$sum_money);
        $this->assign('activity_list',$activity_list);
        $this->assign('page_status','1');//公用页面内容显示区分
        $this->assign('tbody',$this->fetch('_financing_tbody'));
    }

    //账户管理页面
    public function info_set(){

        $express_model = D('FinancingExpress');
        $invoice_model = D('FinancingInvoice');
        $bank_model = D('FinancingBank');

        //获取用户默认发票、快递、银行信息
        $bank_info = $this->find_default($bank_model);
        $express_info = $this->find_default($express_model);
        $invoice_info = $this->find_default($invoice_model);

        $this->assign('bank_info',$bank_info);
        $this->assign('invoice_info',$invoice_info);
        $this->assign('express_info',$express_info);
        $this->show();
    }

    //银行列表
    public function bank_list(){

        $this->main = '/Public/meetelf/home/js/home.financing.public_list.js';

        $user_info = session('auth');
        $list = D('FinancingBank')->where(array('user_guid' => $user_info['guid'],'is_del' => '0'))->order('is_default desc ,updated_at desc')->select();

        $this->assign('public_list_title','银行账户管理');
        $this->assign('new_content','银行账户');
        $this->assign('public_list_describe','银行账户首条信息将自动设为默认信息');
        $this->assign('add_edit_type','bank');
        $this->assign('list',$list);
        $this->show('_public_list');
    }

    //发票列表
    public function invoice_list(){

        $this->main = '/Public/meetelf/home/js/home.financing.public_list.js';

        $user_info = session('auth');
        $list = D('FinancingInvoice')->where(array('user_guid' => $user_info['guid'],'is_del' => '0'))->order('is_default desc ,updated_at desc')->select();

        $this->assign('public_list_title','发票管理');
        $this->assign('new_content','发票');
        $this->assign('public_list_describe','发票首条信息将自动设为默认信息');
        $this->assign('add_edit_type','invoice');
        $this->assign('list',$list);
        $this->show('_public_list');
    }

    //收票地址列表
    public function express_list(){

        $this->main = '/Public/meetelf/home/js/home.financing.public_list.js';

        $user_info = session('auth');
        $list = D('FinancingExpress')->where(array('user_guid' => $user_info['guid'],'is_del' => '0'))->order('is_default desc ,updated_at desc')->select();

        $this->assign('public_list_title','快递信息管理');
        $this->assign('new_content','快递地址');
        $this->assign('public_list_describe','快递首条信息将自动设为默认信息');
        $this->assign('add_edit_type','express');
        $this->assign('list',$list);
        $this->show('_public_list');
    }

    //银行账号添加
    public function bank_add(){

        $this->main = '/Public/meetelf/home/js/home.financing.add_edit.js';

        $post = I('post.');
        if($post){
            $res = $this->private_ae_data('bank','add');
            if($res){
                $this->success('添加成功',U('Financing/bank_list'));
            }else{
                $this->error('添加失败了');
            }
        }

        $this->assign('add_edit_title','银行账户添加');
        $this->assign('add_edit_return_url',U('Financing/bank_list'));
        $this->assign('add_edit_describe','银行账户首条信息将自动设为默认信息');
        $this->assign('add_edit_type','bank');
        $this->assign('add_edit_status','add');

        $this->show('_add_edit');
    }

    //银行账号添加
    public function bank_edit(){

        $this->main = '/Public/meetelf/home/js/home.financing.add_edit.js';

        $info = D('FinancingBank')->where(array('guid' => I('get.guid')))->find();

        $post = I('post.');
        if($post){
            $res = $this->private_ae_data('bank','edit');
            if($res){
                $this->success('添加成功',U('Financing/bank_list'));
            }else{
                $this->error('添加失败了');
            }
        }

        $this->assign('info',$info);
        $this->assign('add_edit_title','银行账户编辑');
        $this->assign('add_edit_return_url',U('Financing/bank_list'));
        $this->assign('add_edit_describe','银行账户首条信息将自动设为默认信息');
        $this->assign('add_edit_type','bank');
        $this->assign('add_edit_status','edit');

        $this->show('_add_edit');
    }

    //收发票地址添加
    public function express_add(){

        $this->main = '/Public/meetelf/home/js/home.financing.add_edit.js';

        $post = I('post.');
        if($post){
            $res = $this->private_ae_data('express','add');
            if($res){
                $this->success('添加成功',U('Financing/express_list'));
            }else{
                $this->error('添加失败了');
            }
        }

        $this->assign('add_edit_title','快递信息');
        $this->assign('add_edit_return_url',U('Financing/express_list'));
        $this->assign('add_edit_describe','快递信息首条信息将自动设为默认信息');
        $this->assign('add_edit_type','express');
        $this->assign('add_edit_status','add');

        $this->show('_add_edit');
    }

    //收发票地址编辑
    public function express_edit(){

        $this->main = '/Public/meetelf/home/js/home.financing.add_edit.js';

        $info = D('FinancingExpress')->where(array('guid' => I('get.guid')))->find();

        $post = I('post.');
        if($post){
            $res = $this->private_ae_data('express','edit');
            if($res){
                $this->success('添加成功',U('Financing/express_list'));
            }else{
                $this->error('添加失败了');
            }
        }

        $this->assign('info',$info);
        $this->assign('add_edit_title','快递信息');
        $this->assign('add_edit_return_url',U('Financing/express_list'));
        $this->assign('add_edit_describe','快递信息首条信息将自动设为默认信息');
        $this->assign('add_edit_type','express');
        $this->assign('add_edit_status','edit');

        $this->show('_add_edit');
    }

    //发票信息添加
    public function invoice_add(){

        $this->main = '/Public/meetelf/home/js/home.financing.add_edit.js';

        $post = I('post.');
        if($post){
            $res = $this->private_ae_data('invoice','add');
            if($res){
                $this->success('添加成功',U('Financing/invoice_list'));
            }else{
                $this->error('添加失败了');
            }
        }
        $this->assign('add_edit_title','发票信息');
        $this->assign('add_edit_return_url',U('Financing/invoice_list'));
        $this->assign('add_edit_describe','发票信息首条信息将自动设为默认信息');
        $this->assign('add_edit_type','invoice');
        $this->assign('add_edit_status','add');

        $this->show('_add_edit');
    }

    //发票信息编辑
    public function invoice_edit(){

        $this->main = '/Public/meetelf/home/js/home.financing.add_edit.js';

        $info = D('FinancingInvoice')->where(array('guid' => I('get.guid')))->find();

        $post = I('post.');
        if($post){
            $res = $this->private_ae_data('invoice','edit');
            if($res){
                $this->success('添加成功',U('Financing/invoice_list'));
            }else{
                $this->error('添加失败了');
            }
        }

        $this->assign('info',$info);
        $this->assign('add_edit_title','发票信息');
        $this->assign('add_edit_return_url',U('Financing/invoice_list'));
        $this->assign('add_edit_describe','发票信息首条信息将自动设为默认信息');
        $this->assign('add_edit_type','invoice');
        $this->assign('add_edit_status','edit');


        $this->show('_add_edit');
    }

    /**
     * @param $type 区分类别  发票 invoice、银行账号 bank、快递地址 express
     * @param $status  区分add、edit
     **/

    public function private_ae_data($type,$status){
        $user_guid = session('auth')['guid'];
        $post = I('post.');
        $time = time();
        if($type == 'invoice'){
            if($status == 'add'){
                $data['guid'] = create_guid();
                $data['user_guid'] = $user_guid;
                $data['name'] = $post['invoice_name'];
                $data['address'] = $post['invoice_address'];
                $data['number'] = $post['invoice_num'];
                $data['mobile'] = $post['invoice_mobile'];
                $data['is_default'] = $post['is_default'];
                $data['created_at'] = $time;
                $data['updated_at'] = $time;
            }else{
                $data['user_guid'] = $user_guid;
                $data['name'] = $post['invoice_name'];
                $data['address'] = $post['invoice_address'];
                $data['number'] = $post['invoice_num'];
                $data['mobile'] = $post['invoice_mobile'];
                $data['is_default'] = $post['is_default'];
                $data['updated_at'] = $time;
            }

            $res = $this->add_edit_model(D('FinancingInvoice'),$data,$status);
            if($res){
                return true;
            }else{
                return false;
            }
        }elseif($type == 'bank'){
            if($status == 'add'){
                $data['guid'] = create_guid();
                $data['user_guid'] = $user_guid;
                $data['opening_bank'] = $post['open_bank'];
                $data['bank_account'] = $post['bank_num'];
                $data['is_default'] = $post['is_default'];
                $data['created_at'] = $time;
                $data['updated_at'] = $time;
            }else{
                $data['user_guid'] = $user_guid;
                $data['opening_bank'] = $post['open_bank'];
                $data['bank_account'] = $post['bank_num'];
                $data['is_default'] = $post['is_default'];
                $data['updated_at'] = $time;
            }

            $res = $this->add_edit_model(D('FinancingBank'),$data,$status);
            if($res){
                return true;
            }else{
                return false;
            }
        }else{
            if($status == 'add'){
                $data['guid'] = create_guid();
                $data['user_guid'] = $user_guid;
                $data['name'] = $post['express_name'];
                $data['mobile'] = $post['express_mobile'];
                $data['address'] = $post['express_address'];
                $data['postcode'] = $post['express_postcode'];
                $data['is_default'] = $post['is_default'];
                $data['created_at'] = $time;
                $data['updated_at'] = $time;
            }else{
                $data['user_guid'] = $user_guid;
                $data['name'] = $post['express_name'];
                $data['mobile'] = $post['express_mobile'];
                $data['address'] = $post['express_address'];
                $data['postcode'] = $post['express_postcode'];
                $data['is_default'] = $post['is_default'];
                $data['updated_at'] = $time;
            }

            $res = $this->add_edit_model(D('FinancingExpress'),$data,$status);
            if($res){
                return true;
            }else{
                return false;
            }
        }

    }

    /**
     * @param $this_model  要操作的表
     * @param $data    打包好的数据
     * @param $type    add,edit 区分
     **/

    public function add_edit_model($this_model,$data,$status){
        $time = time();
        if($data['is_default'] == '1'){
            $this_model->where(array('user_guid' => $data['user_guid']))->data(array('is_default' => 0,'updated_at' => $time))->save();
        }
        if($status == 'add'){
            $result = $this_model->where(array('user_guid' => $data['user_guid'],'is_del' => 0))->find();
            if(empty($result)){
                $data['is_default'] = '1';
            }
            $res = $this_model->data($data)->add();
        }else{
            $result = $this_model->where(array('user_guid' => $data['user_guid'],'is_del' => 0))->order('updated_at desc')->find();
            $res = $this_model->where(array('guid' => I('get.guid')))->data($data)->save();
        }
        if(!empty($res)){
            return true;
        }else{
            return false;
        }
    }


    //删除银行信息
    public function bank_del(){

        $bank_model = D('FinancingBank');
        $guid = I('get.guid');

        $this->del_model($bank_model,$guid);
    }

    //删除发票信息
    public function invoice_del(){

        $invoice_model = D('FinancingInvoice');
        $guid = I('get.guid');

        $this->del_model($invoice_model,$guid);
    }

    //删除快递信息
    public function express_del(){

        $express_model = D('FinancingExpress');
        $guid = I('get.guid');

        $this->del_model($express_model,$guid);
    }


    /**
     * @param $this_model    要删除的model类型
     * @param $this_guid     要删除的guid
     **/

    public function del_model($this_model,$this_guid){

        $user_guid = session('auth')['guid'];
        $res = $this_model->where(array('guid' => $this_guid))->data(array('is_del' => 1,'updated_at' => time()))->save();
        $is_default_guid = $this_model->field('guid')->where(array('user_guid' => $user_guid,'is_del' => 0))->order('updated_at desc')->find()['guid'];
        $this_model->where(array('guid' => $is_default_guid))->data(array('updated_at' => time(),'is_default' => 1))->save();
        //        var_dump($resss,$this_guid,$is_default_guid);die;

        if(!empty($res)){
            $this->success('信息删除成功');
            exit;
        }else{
            $this->error('信息删除失败');
            exit;
        }
    }


    //结算处理页面
    public function set_account(){
        $this->main = '/Public/meetelf/home/js/home.financing.set_account.js';

        $this->get_not_settle_act(I('post.ck_act_guids'),'set_account');

        $ratio = D('FinancingDiscount')->field('ratio')->where(array('status'=>1,'is_del'=>0))->find()['ratio'];
        $invoice_info = $this->find_default(D('FinancingInvoice'));
        $express_info = $this->find_default(D('FinancingExpress'));
        $bank_info = $this->find_default(D('FinancingBank'));

        $this->assign('invoice_info',$invoice_info);
        $this->assign('express_info',$express_info);
        $this->assign('bank_info',$bank_info);
        $this->assign('ratio',$ratio);
        $this->show();
    }

    public function find_default($model){
        $user_guid = session('auth')['guid'];

        $res = $model->where(array('user_guid' => $user_guid,'is_del' => '0','is_default'=>1))->find();
        if($res){
            return $res;
        }else{
            return false;
        }
    }

    /**
     * 结算的计算逻辑  暂时无验证补全
     **/

    public function compute_logic(){

        $user_info = session('auth');
        $post = I('post.');
        $activity_model = D('Activity');
        $financing_activity_model = D('FinancingActivity');
        $financing_ticket_model = D('FinancingTicket');
        $order_model = D('Order');
        $ticket_model = D('ActivityUserTicket');
        $time = time();
        if($post){

            //记得数据库加两个状态      ticket 加是否收费票
            if(empty($post['bank_guid'])){
                $this->error('银行信息不能为空');
            }
            if($post['is_invoice'] == '1'){
                if(empty($post['invoice_guid']) || empty($post['express_guid'])){
                    $this->error('发票信息和快递信息不能为空');
                }
            }
            //查询单独活动的金额
            $order_money_list = $order_model
                ->field('total_price as money,target_guid')
                ->where(array('target_guid' => array('in',$post['act_guids']),'status' => '1'))
                ->select();

            if(!empty($order_money_list)){ //组装数据
                foreach ($order_money_list as $k=>$v) {
                    $moneys[$v['target_guid']][] = $v['money']/100;
                    $befor_act_guids[] = $v['target_guid'];

                    if(isset($act_moneys[$v['target_guid']])){
                        $act_moneys[$v['target_guid']] = $act_moneys[$v['target_guid']] + $v['money'];
                    }else{
                        $act_moneys[$v['target_guid']] = $v['money'];
                    }
                }
            }


            //验证钱数是否正确
            $sum_money = 0;
            foreach($order_money_list as $k=>$v){
                $sum_money += $v['money'];
            }
            if($sum_money != $post['sum_money']*100){
                $this->error('数据错误，请重新结算',U('Financing/index'));
            }

            //组装明细表数据
            $data['guid'] = create_guid();
            $data['user_guid'] = $user_info['guid'];
            $data['activity_guids'] = json_encode($post['act_guids']);
            $data['charge_before_money'] = $post['sum_money']*100;//结算前金额
            $data['charge_after_money'] = $post['sum_money']*(1-$post['ratio'])*100;//结算后金额
            $data['charge'] = $post['ratio'];//收费比例
            $data['charge_money'] = $post['charge_money']*100;//收费金额
            $data['account_at'] = $time;
            $data['status'] = 0;
            $data['is_invoice'] = $post['is_invoice'];
            if($post['is_invoice'] == 1){
                $data['invoice_guid'] = $post['invoice_guid'];
                $data['bank_guid'] = $post['bank_guid'];
                $data['express_guid'] = $post['express_guid'];
            }
            $data['created_at'] = $time;
            $data['updated_at'] = $time;


            //组装活动明细表
            $financing_act = $financing_activity_model->where(array('guid' => array('in',$post['act_guids'])))->select();
            if(!empty($financing_act)){
                $this->error('数据错误，请检查后再提交');
            }
            $act_list = $activity_model->field('guid,name,end_time')->where(array('guid' => array('in',$post['act_guids'])))->select();
            foreach($act_list as $k=>$v){
                $act_data[$v['guid']]['guid'] = $v['guid'];
                $act_data[$v['guid']]['activity_name'] = $v['name'];
                $act_data[$v['guid']]['end_time'] = $v['end_time'];
            }
            foreach($order_money_list as $k=>$v){
                $act_data[$v['target_guid']]['user_guid'] = $user_info['guid'];
                $act_data[$v['target_guid']]['account_at'] = $data['account_at'];
                $act_data[$v['target_guid']]['account_at'] = $data['account_at'];
                $act_data[$v['target_guid']]['detail_guid'] = $data['guid'];

                $act_data[$v['target_guid']]['charge_before_money'] = $act_moneys[$v['target_guid']];
                $act_data[$v['target_guid']]['status'] = 0;
                $act_data[$v['target_guid']]['created_at'] = $time;
                $act_data[$v['target_guid']]['updated_at'] = $time;
            }

            //查询收费票数据
            $order_act_ticket = $order_model
                ->field('goods_price,buyer_guid,status,goods_name,finished_time,target_guid,payment_type')
                ->where(array('target_guid' => array('in',$befor_act_guids),'total_price' => array('gt',0),'finished_time' => array('gt',0)))
                ->order('finished_time desc')
                ->select();


            foreach ($order_act_ticket as $k=>$v) {
                $userinfo_guids[] = $v['buyer_guid'];
                $ticket_status[$v['buyer_guid']]['status'] = $v['status'];
            }

            //查询状态对应名称
            foreach($ticket_status as $k=>$v){
                switch ($v['status']){
                case 0: $ticket_status[$k]['name'] = '新订单';
                break;
                case 1: $ticket_status[$k]['name'] = '支付成功';
                break;
                case 2: $ticket_status[$k]['name'] = '支付失败';
                break;
                case 3: $ticket_status[$k]['name'] = '取消';
                break;
                case 4: $ticket_status[$k]['name'] = '已发货';
                break;
                case 5: $ticket_status[$k]['name'] = '交易成功';
                break;
                case 6: $ticket_status[$k]['name'] = '待审核';
                break;
                case 7: $ticket_status[$k]['name'] = '审核通过';
                break;
                case 8: $ticket_status[$k]['name'] = '审核拒绝';
                break;
                case 9: $ticket_status[$k]['name'] = '被删除';
                break;
                }
            }

            //查询该活动的票名称种类

            foreach($ticket_status as $k=>$v){
                $ticket_order_status[$v['status']]['name'] = $v['name'];
            }
            //查询购票者名称
            $user_ticket_name = $ticket_model
                ->field('real_name,userinfo_guid,ticket_guid,ticket_name')
                ->where(array('userinfo_guid' => array('in',$userinfo_guids)))
                ->select();


            $i=0;
            foreach($user_ticket_name as $k=>$v){
                $real_names[$v['userinfo_guid']]['real_name'] = $v['real_name'];
                $ticket_name_guid[$i]['name'] = $v['ticket_name'];
                $real_names[$v['userinfo_guid']]['ticket_guid'] = $v['ticket_guid'];
                $i++;
            }

            //组装前台页面数据
            foreach($order_act_ticket as $k=>$v){
                $order_act_ticket[$k]['ticket_guid'] = $real_names[$v['buyer_guid']]['ticket_guid'];
                $order_act_ticket[$k]['real_name'] = $real_names[$v['buyer_guid']]['real_name'];
                $order_act_ticket[$k]['ticket_status_name'] = $ticket_status[$v['buyer_guid']]['name'];
            }
        }

        //插入数据库
        D()->startTrans();
        foreach($act_data as $k=>$v){
            $res = $financing_activity_model->data($v)->add();
            if(!empty($res)){
                $res_status = true;
            }else{
                $res_status = false;
                break;
            }
        }

        foreach($order_act_ticket as $k=>$v){
            $ticket_data[$k]['guid'] = create_guid();
            $ticket_data[$k]['user_guid'] = $user_info['guid'];
            $ticket_data[$k]['detail_guid'] = $data['guid'];
            $ticket_data[$k]['target_guid'] = $v['target_guid'];
            $ticket_data[$k]['goods_price'] = $v['goods_price'];
            $ticket_data[$k]['buyer_guid'] = $v['buyer_guid'];
            $ticket_data[$k]['status'] = $v['status'];
            $ticket_data[$k]['goods_name'] = $v['goods_name'];
            $ticket_data[$k]['finished_time'] = $v['finished_time'];
            $ticket_data[$k]['payment_type'] = $v['payment_type'];
            $ticket_data[$k]['ticket_guid'] = $v['ticket_guid'];
            $ticket_data[$k]['real_name'] = $v['real_name'];
            $ticket_data[$k]['ticket_status_name'] = $v['ticket_status_name'];
            $ticket_data[$k]['is_del'] = 0;
            $ticket_data[$k]['created_at'] = $time;
            $ticket_data[$k]['updated_at'] = $time;

        }

        $ticket_res[] = $financing_ticket_model->addAll($ticket_data);


        $res = D('FinancingDetail')->data($data)->add();
        if(!empty($res) && $res_status && !empty($ticket_res)){
            D()->commit();
            send_email('service@yunmai365.com','酷客会签结算系统','新结算','新提交结算处理');
            $this->success('结算成功,等待酷客会签财务人员审核',U('Financing/index'));
            exit;
        }else{
            D()->rollback();
            $this->error('结算失败，请重新操作',U('Financing/index'));
            exit;
        }
    }


    //公共电子票状态页
    public function public_ticket(){

        $post = I('post.');
        $this->main = '/Public/meetelf/home/js/home.financing.public_ticket.js';

        $user_guid = session('auth')['guid'];
        $aid = I('get.aid');
        $order_model = D('Order');
        $ticket_model = D('ActivityUserTicket');
        $activity_model = D('Activity');
        $attr_ticket_model = D('ActivityAttrTicket');

        $return_url = U('Financing/index');
        $act_name = $activity_model->where(array('guid' => $aid))->find()['name'];
        //活动金额总计
        $act_sum_money = $order_model
            ->field('sum(total_price) as money')
            ->where(array('target_guid' => $aid,'status' => '1'))
            ->find()['money'];

        $order_act_ticket = $order_model
            ->field('goods_price,buyer_guid,status,goods_name,finished_time')
            ->where(array('target_guid' => $aid,'total_price' => array('gt',0),'finished_time' => array('gt',0)))
            ->order('finished_time desc')
            ->select();

        //查询收费票数据
        if(!empty($post)){
            if($post['ticket_status'] == 'all' && $post['ticket_guid'] == 'all'){
                $order_act_ticket = $order_model
                    ->field('goods_price,buyer_guid,status,goods_name,finished_time')
                    ->where(array('target_guid' => $aid,'total_price' => array('gt',0),'finished_time' => array('gt',0)))
                    ->order('finished_time desc')
                    ->select();
            }else if($post['ticket_status'] != 'all' &&  $post['ticket_guid'] == 'all'){
                $order_act_ticket = $order_model
                    ->field('goods_price,buyer_guid,status,goods_name,finished_time')
                    ->where(array('target_guid' => $aid,'total_price' => array('gt',0),'finished_time' => array('gt',0),'status' => $post['ticket_status']))
                    ->order('finished_time desc')
                    ->select();
            }else if($post['ticket_status'] == 'all' &&  $post['ticket_guid'] != 'all'){
                $order_act_ticket = $order_model
                    ->field('goods_price,buyer_guid,status,goods_name,finished_time')
                    ->where(array('target_guid' => $aid,'total_price' => array('gt',0),'finished_time' => array('gt',0)))
                    ->order('finished_time desc')
                    ->select();

                $act_user_guids = $ticket_model->field('userinfo_guid')
                    ->where(array('ticket_guid' => $post['ticket_guid']))
                    ->select();
                foreach($act_user_guids as $k=>$v){
                    foreach($order_act_ticket as $j=>$i){
                        if($i['buyer_guid'] == $v['userinfo_guid']){
                            $shift[] = $i;
                        }
                    }
                }
                $order_act_ticket = $shift;
            }else if($post['ticket_status'] != 'all' &&  $post['ticket_guid'] != 'all'){
                $order_act_ticket = $order_model
                    ->field('goods_price,buyer_guid,status,goods_name,finished_time')
                    ->where(array('target_guid' => $aid,'total_price' => array('gt',0),'finished_time' => array('gt',0),'status' => $post['ticket_status']))
                    ->order('finished_time desc')
                    ->select();

                $act_user_guids = $ticket_model->field('userinfo_guid')
                    ->where(array('ticket_guid' => $post['ticket_guid']))
                    ->select();
                foreach($act_user_guids as $k=>$v){
                    foreach($order_act_ticket as $j=>$i){
                        if($i['buyer_guid'] == $v['userinfo_guid']){
                            $shift[] = $i;
                        }
                    }
                }
                $order_act_ticket = $shift;
            }
        }

        foreach ($order_act_ticket as $k=>$v) {
            $userinfo_guids[] = $v['buyer_guid'];
            $ticket_status[$v['buyer_guid']]['status'] = $v['status'];
        }

        //查询状态对应名称
        foreach($ticket_status as $k=>$v){
            switch ($v['status']){
            case 0: $ticket_status[$k]['name'] = '新订单';
            break;
            case 1: $ticket_status[$k]['name'] = '支付成功';
            break;
            case 2: $ticket_status[$k]['name'] = '支付失败';
            break;
            case 3: $ticket_status[$k]['name'] = '取消';
            break;
            case 4: $ticket_status[$k]['name'] = '已发货';
            break;
            case 5: $ticket_status[$k]['name'] = '交易成功';
            break;
            case 6: $ticket_status[$k]['name'] = '待审核';
            break;
            case 7: $ticket_status[$k]['name'] = '审核通过';
            break;
            case 8: $ticket_status[$k]['name'] = '审核拒绝';
            break;
            case 9: $ticket_status[$k]['name'] = '被删除';
            break;
            }
        }

        //查询该活动的票名称种类

        foreach($ticket_status as $k=>$v){
            $ticket_order_status[$v['status']]['name'] = $v['name'];
            $t_status_name[$v['name']][] = $v['name'];
        }
        //查询购票者名称
        $user_ticket_name = $ticket_model
            ->field('real_name,userinfo_guid,ticket_guid,ticket_name')
            ->where(array('userinfo_guid' => array('in',$userinfo_guids)))
            ->select();


        foreach($user_ticket_name as $k=>$v){
            $real_names[$v['userinfo_guid']]['real_name'] = $v['real_name'];
            $ticket_name_guid[$v['ticket_guid']]['name'] = $v['ticket_name'];
            $ticket_name_guid[$v['ticket_guid']]['guid'] = $v['ticket_guid'];

        }

        //组装前台页面数据
        foreach($order_act_ticket as $k=>$v){
            $order_act_ticket[$k]['real_name'] = $real_names[$v['buyer_guid']]['real_name'];
            $order_act_ticket[$k]['ticket_status_name'] = $ticket_status[$v['buyer_guid']]['name'];

        }

        $this->assign('aid',$aid);
        $this->assign('return_url',$return_url);
        $this->assign('act_name',$act_name);
        $this->assign('ticket_order_status',$ticket_order_status);
        $this->assign('ticket_name_guid',$ticket_name_guid);
        $this->assign('sum_money',$act_sum_money/100);
        $this->assign('ticket_list',$order_act_ticket);
        if(!$post){
            $this->assign('tbody',$this->fetch('_ticket_tbody'));
        }else{

            //电子票状态
            switch ($post['ticket_status']){
            case '0': $return_data['t_status_name'] = '新订单';
            break;
            case '1': $return_data['t_status_name'] = '支付成功';
            break;
            case '2': $return_data['t_status_name'] = '支付失败';
            break;
            case '3': $return_data['t_status_name'] = '取消';
            break;
            case '4': $return_data['t_status_name'] = '已发货';
            break;
            case '5': $return_data['t_status_name'] = '交易成功';
            break;
            case '6': $return_data['t_status_name'] = '待审核';
            break;
            case '7': $return_data['t_status_name'] = '审核通过';
            break;
            case '8': $return_data['t_status_name'] = '审核拒绝';
            break;
            case '9': $return_data['t_status_name'] = '被删除';
            break;
            case 'all': $return_data['t_status_name'] = '全部';
            break;
            }

            //电子票名称
            if($post['ticket_guid'] == 'all'){
                $return_data['ticket_name'] = '全部';
            }else{
                $return_data['ticket_name'] = $attr_ticket_model->field('name')->where(array('guid' => $post['ticket_guid']))->find()['name'];
            }

            $return_data['sum_money'] = $order_model
                ->field('sum(total_price) as money')
                ->where(array('target_guid' => $aid,'buyer_guid' => array('in',$userinfo_guids),'status' => '1'))
                ->find()['money']/100;

            $return_data['tbody'] = $this->fetch('_ticket_tbody');
            $this->ajaxResponse($return_data);
        }
        $this->show('_public_ticket');

    }


    //正在结算中
    public function underway_detail(){

        $user_guid = session('auth')['guid'];
        $detail_model = D('FinancingDetail');

        $detail_list = $detail_model
            ->where(array('user_guid' => $user_guid,'status' => 0,'is_del' => 0))
            ->select();
        $this->assign('detail_list',$detail_list);
        $this->show();
    }

    //进行中的结算活动
    public function underway_act(){

        $detail_guid = I('get.dguid');//结算明细的guid
        $financing_detail_model = D('FinancingDetail');
        $detail_act_model = D('FinancingActivity');

        $act_guids = $financing_detail_model
            ->where(array('guid' => $detail_guid))
            ->find()['activity_guids'];

        $detail_act_list = $detail_act_model
            ->field('charge_before_money,end_time,activity_name as name,guid,account_at,charge')
            ->where(array('guid' => array('in',json_decode($act_guids)),'status' => 0,'is_del' => 0))
            ->select();

        foreach($detail_act_list as $k=>$v){
            $detail_act_list[$k]['money'] = $v['charge_before_money']/100;
        }

        $this->assign('ck_act_status','set_account');//页面区分   等于set_account时与结算页面显示一致
        $this->assign('act_status_page','act_status_page');//页面区分   等于set_account时与结算页面显示一致
        $this->assign('page_money_status','1');//公用页面内容显示区分
        $this->assign('activity_list',$detail_act_list);
        $this->assign('tbody',$this->fetch('_financing_tbody'));
        $this->show();
    }


    //结算中和已结束的活动电子票公用页
    public function underway_history_ticket(){

        $this->main = '/Public/meetelf/home/js/home.financing.underway_history_ticket.js';

        $aid = I('get.aid');
        $action = I('get.action');//区分跳转页面
        $post = I('post.');
        $fin_ticket_model = D('FinancingTicket');

        $act_name = D('Activity')->where(array('guid' => $aid))->find()['name'];

        //获取该活动的数据库json数据
        $ticket_list = $fin_ticket_model
            ->where(array('target_guid' => $aid,'is_del' => 0))
            ->select();
        if($post){
            $ticket_name = $post['goods_name'];
            $ticket_status = $post['ticket_status_name'];

            //单独筛选票类型
            if($ticket_status == '全部' && $ticket_name == '全部'){
                //$ticket_name  电子票名称   $ticket_status  电子票状态
            }elseif($ticket_status == '全部' && $ticket_name != '全部'){
                foreach ($ticket_list as $k=>$v) {
                    if($v['goods_name'] == $ticket_name){
                        $t_res[] = $v;
                    }
                }
                $ticket_list = $t_res;

            }elseif($ticket_status != '全部' && $ticket_name == '全部'){
                foreach ($ticket_list as $k=>$v) {
                    if($v['ticket_status_name'] == $ticket_status){
                        $t_res[] = $v;
                    }
                }
                $ticket_list = $t_res;
            }else{
                foreach ($ticket_list as $k=>$v) {
                    if($v['ticket_status_name'] == $ticket_status){
                        if($v['goods_name'] == $ticket_name){
                            $t_res[] = $v;
                        }
                    }
                }
                $ticket_list = $t_res;
            }
        }


        //电子票名
        $sum_money = 0;
        foreach ($ticket_list as $k=>$v) {
            $goods_name[] = $v['goods_name'];
            $ticket_status_name[] = $v['ticket_status_name'];
            if($v['status'] == 1){
                $sum_money += $v['goods_price'];
            }
        }

        $goods_name = array_unique($goods_name);
        $ticket_status_name = array_unique($ticket_status_name);

        $this->assign('aid',$aid);
        if($action == 'u'){
            $this->assign('return_url',U('Financing/underway_act',array('dguid' => $ticket_list[0]['detail_guid'])));
        }else{
            $this->assign('return_url',U('Financing/history_act',array('dguid' => $ticket_list[0]['detail_guid'])));
        }

        $this->assign('act_name',$act_name);
        $this->assign('goods_name',$goods_name);
        $this->assign('ticket_status_name',$ticket_status_name);
        $this->assign('ticket_list',$ticket_list);

        if(!$post){
            $this->assign('sum_money',$sum_money/100);
            $this->assign('tbody',$this->fetch('_ticket_tbody'));
        }else{
            $return_data['sum_money'] = $sum_money/100;
            $return_data['tbody'] = $this->fetch('_ticket_tbody');
            $return_data['ticket_name'] = $ticket_name;
            $return_data['ticket_status'] = $ticket_status;
            $this->ajaxResponse($return_data);
        }
        $this->show("_underway_history_ticket");
    }

    //历史结算的活动
    public function history_detail(){

        $user_guid = session('auth')['guid'];
        $detail_model = D('FinancingDetail');

        $detail_list = $detail_model
            ->where(array('user_guid' => $user_guid,'status' => 1,'is_del' => 0))
            ->select();


        $this->assign('detail_list',$detail_list);
        $this->show();
    }


    //进行中的结算活动
    public function history_act(){

        $detail_guid = I('get.dguid');//结算明细的guid
        $financing_detail_model = D('FinancingDetail');
        $detail_act_model = D('FinancingActivity');

        $act_guids = $financing_detail_model
            ->where(array('guid' => $detail_guid))
            ->find()['activity_guids'];

        $detail_act_list = $detail_act_model
            ->field('charge_before_money,end_time,activity_name as name,guid,account_at,charge')
            ->where(array('guid' => array('in',json_decode($act_guids)),'status' => 1,'is_del' => 0))
            ->select();

        foreach($detail_act_list as $k=>$v){
            $detail_act_list[$k]['money'] = $v['charge_before_money']/100;
        }

        $this->assign('ck_act_status','set_account');//页面区分   等于set_account时与结算页面显示一致
        $this->assign('act_status_page','act_status_page');//页面区分   等于set_account时与结算页面显示一致
        $this->assign('page_money_status','1');//公用页面内容显示区分
        $this->assign('activity_list',$detail_act_list);
        $this->assign('tbody',$this->fetch('_financing_tbody'));
        $this->show();
    }

}
