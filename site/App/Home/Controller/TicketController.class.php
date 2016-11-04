<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/9/14
 * Time: 上午11:39
 */

namespace Home\Controller;

use Home\Controller\BaseController;
use Think\Controller;
use Controls\Control\PagerControl;
use Controls\Model\PagerControlModel;
use Think\Image\Driver\Gd;
use Think\Image\Driver\GIFDecoder;

/**
 * 电子票
 * CT 2015.09.14 11:45 by manonloki
 * Class TicketController
 * @package Home\Controller
 */
class TicketController extends BaseController
{

    /**
     * 我的电子票
     * CT 2015.09.15 09:39 by manonloki
     * UT 2015.09.24 15:15 by manonloki myTicket (rename) ->mine_ticket  change js file path
     */
    public function  mine_tickets()
    {
        //获取数据源
        $pageSize = intval(I('param.ps', 10));//分页大小
        $page = intval(I('param.p', 1));//当前页码
        $query_condation = I('param.q','');//条件
        $status = I('param.s','1,2,3,4');//票据状态
        $user_guid = $this->get_auth_session('guid');

        //条件
        $condations = array();
        $condations['aut.user_guid'] = $user_guid;
        $condations['aut.is_del'] = 0;

        if ($query_condation != '') {
            $condations['a.name'] = array('LIKE', '%' . $query_condation . '%');
        }

        //Model
        $model_user_ticket=M("ActivityUserTicket");
        $model_userinfo=M("ActivityUserinfo");
        $model_attr_ticket=M("ActivityAttrTicket");
        //获取所有的票据数量
        $condations['aut.status'] = array('IN', '1,2,3,4');
        $ticket_all_count = $model_user_ticket
            ->alias('aut')
            ->where($condations)
            ->join('ym_activity a ON a.guid=aut.activity_guid')//连接ym_activity 因为查询条件有活动名称
            ->count();

        //获取未使用票据数量
        $condations['aut.status'] = array('IN', '1,2,3');
        $ticket_unuse_count = $model_user_ticket
            ->alias('aut')
            ->where($condations)
            ->join('ym_activity a ON a.guid=aut.activity_guid')//连接ym_activity 因为查询条件有活动名称
            ->count();

        //获取已使用票据数量
        $condations['aut.status'] = array('IN', '4');
        $ticket_used_count =$model_user_ticket
            ->alias('aut')
            ->where($condations)
            ->join('ym_activity a ON a.guid=aut.activity_guid')//连接ym_activity 因为查询条件有活动名称
            ->count();

        //延迟获取用于分页的真实数量
        if($status===''){
            unset($condations['aut.status']);
        }else{
            $condations['aut.status'] = array('IN', $status);
        }
        $ticket_page_count = $model_user_ticket
            ->alias('aut')
            ->where($condations)
            ->join('ym_activity a ON a.guid=aut.activity_guid')//连接ym_activity 因为查询条件有活动名称
            ->count();


        $tickets=$model_user_ticket
            ->alias('aut')
            ->where($condations)
            ->join('ym_activity a ON a.guid=aut.activity_guid')
            ->order('aut.created_at desc')
            ->limit($pageSize)
            ->page($page)
            ->field(array(
                'a.id'=>'activity_id',//活动ID
                'a.guid'=>'activity_guid',//活动唯一标识
                'a.name'=>'activity_name',//活动名称
                'a.status'=>'activity_status',//活动状态
                'aut.ticket_name'=>'ticket_name',//票名
                'aut.status'=>'ticket_status',//票状态
                'aut.ticket_code'=>'ticket_code',//票号
                'aut.guid'=>'user_ticket_guid',//用户票唯一标识
                'aut.ticket_guid'=>'ticket_guid',//真实票唯一标识
                'aut.userinfo_guid'=>'buyer_guid',//报名者唯一标识
            ))
            ->select();

        if(!empty($tickets)){
            //获取ID集合
            $ticket_guid_list=array_columns($tickets,'ticket_guid');
            $buyer_guid_list=array_columns($tickets,'buyer_guid');

            $buyers=$model_userinfo
                ->alias('au')
                ->where(array(
                    'au.guid' =>array('IN',$buyer_guid_list)
                ))
                ->field(array(
                    'au.guid'=>'buyer_guid',
                    'au.real_name'=>'buyer_name',
                    'au.mobile'=>'buyer_mobile',
                    'au.email'=>'buyer_email'
                ))
                ->select();
            $attr_tickets=$model_attr_ticket
                ->alias('aat')
                ->where(array(
                    'aat.guid'=>array('IN',$ticket_guid_list)
                ))
                ->field(array(

                    'aat.guid'=>'ticket_guid',
                    'aat.price'=>'ticket_price'
                ))
                ->select();

            //转换为Map 解决O^n问题
            $buyer_map=array_columns($buyers,null,'buyer_guid');
            $attr_ticket_map=array_columns($attr_tickets,null,'ticket_guid');


            //拼装数据

            foreach($tickets as &$value){
                $buyer=$buyer_map[$value['buyer_guid']];
                $attr_ticket=$attr_ticket_map[$value['ticket_guid']];

                if(!empty($buyer)){
                    $value=array_merge($value,$buyer);
                }
                if(!empty($attr_ticket)){
                    $value=array_merge($value,$attr_ticket);
                }

                //整理数据
                $value['activity_status_string']= kookeg_lang('k__activity.status.' . $value['activity_status']);
                $value['ticket_status_string'] = kookeg_lang('k__activity_user_ticket.status.' . $value['ticket_status']);
                $value['contact'] = $value['buyer_mobile'] . ($value['buyer_email'] == '' ? '' : '<br>' . $value['buyer_email']);
            }

        }

        //处理分页
        $pager_model = new PagerControlModel($page, $ticket_page_count, $pageSize);
        $pager = new PagerControl($pager_model, PagerControl::$Enum_First_Prev_Next_Last);
        $pager_html=$pager->fetch();


        //标签标题
        $tab_titles=array(
            'tab_allText' => kookeg_lang('_TAB_ALL_') . '(' . $ticket_all_count . ')',
            'tab_unuseText' => kookeg_lang('_TAB_UNUSE_') . '(' . $ticket_unuse_count . ')',
            'tab_usedText' => kookeg_lang('_TAB_USED_') . '(' . $ticket_used_count . ')'
        );

        //增加数据源
        $this->assign('datasource', array(
            'tickets' => $tickets,
        ));
        $this->assign('pager',$pager_html);
        $this->assign('tab_titles',$tab_titles);


        if(IS_GET){
            layout("layout_new");
            $this->title = '我的票券';
            $this->main = '/Public/meetelf/home/js/build/ticket.mine_tickets.js';
            $this->show();

        }else if(IS_POST){
            layout(false);
            $this->ajaxResponse(array(
                'status' => C('ajax_success'),
                'data' => array(
                    'content' => $this->fetch('Ticket:_mine_tickets_item'),
                    'pager' => $pager_html,
                    'tab_allText' => kookeg_lang('_TAB_ALL_') . '(' . $ticket_all_count . ')',
                    'tab_unuseText' => kookeg_lang('_TAB_UNUSE_') . '(' . $ticket_unuse_count . ')',
                    'tab_usedText' => kookeg_lang('_TAB_USED_') . '(' . $ticket_used_count . ')'
                ),

            ));

            $this->ajaxResponse(array('msg'=>'hello'),'json');
        }



    }
    /**
     * 下载电子票
     * UT 2015.09.24 15:15 by manonloki ticket_download (change) -> download   fixed string bug
     */
    public function download()
    {

        $ticket_guid = I('param.guid');
        $out_type = I('param.t', 'd');

        if (empty($ticket_guid)) {
            die();
        }

        $ticket = M('ActivityUserTicket')
            ->alias('aut')
            ->where(array(
                'aut.guid' => $ticket_guid
            ))
            ->join('ym_activity a ON a.guid=aut.activity_guid', 'LEFT')
            ->join('ym_activity_attr_undertaker aau ON aau.activity_guid=aut.activity_guid and type=1 ', 'LEFT')
            ->join('ym_activity_userinfo au ON au.guid=aut.userinfo_guid and au.activity_guid=aut.activity_guid ', 'LEFT')
            ->field(array(
                'aut.ticket_code' => 'ticket_code', //票号
                'aut.activity_guid' => 'activity_guid',//活动唯一标识
                'a.start_time' => 'activity_start_time',//活动开始时间
                'a.published_at' => 'activity_published_at',//活动发布时间
                'aut.ticket_name' => 'ticket_name',//票名
                'a.name' => 'activity_name',//活动名称
                'a.address' => 'activity_address',//活动地点
                'aau.name' => 'activity_undertake_name',//举办方
                'au.real_name' => 'activity_user_name',//报名者
                'au.mobile' => 'activity_user_mobile',//手机号
                'a.areaid_1_name'=>'activity_area_1',//省
                'a.areaid_2_name'=>'activity_area_2',//市
            ))
            ->find();

        //处理数据
        $ticket['activity_user_mobile'] = substr_replace($ticket['activity_user_mobile'], '****', 3, 4);//替换电话号码
        $ticket['activity_address']=$ticket['activity_area_1'].$ticket['activity_area_2'].$ticket['activity_address'];//合并完整的省市信息
        if(mb_strlen($ticket['activity_name'],'utf-8')>26){
            $ticket['activity_name']=mb_substr($ticket['activity_name'],0,23,'utf-8').'...';
        }



        //拼地址
        $template_img_path = PUBLIC_PATH . '/ticket_image_tmpl/ticket_bg.png'; //模板地址
        $template_font_path = PUBLIC_PATH . '/ticket_image_tmpl/msyhbd.ttf';//字体地址

        $rootPath = UPLOAD_PATH; //绝对根路径
        $dir_path = '/tickets/' . $ticket['activity_guid'] . '/';//文件夹路径

        //二维码
        $qrcode_file_name = 'qr_' . $ticket['ticket_code'] . '.png';//文件名

        //电子票
        $ticket_file_name = 'ticket_' . $ticket['ticket_code'] . '.png';//文件名


        //判断是否存在缓存好的电子票
        if (!file_exists($rootPath . $dir_path . $ticket_file_name)) {


            $gd = new Gd($template_img_path);
            $gd->text($ticket['ticket_code'], $template_font_path, 26, '#555555', array(98, 72));//票号
            $gd->text($ticket['ticket_name'], $template_font_path, 60, '#333333', array(314, 200));//票名


            //动态处理活动名称
            if (mb_strlen($ticket['activity_name'], 'UTF-8') < 13) {
                $gd->text($ticket['activity_name'], $template_font_path, 24, '#000000', array(488, 356));//活动名
            } else {
                $gd->text(mb_substr($ticket['activity_name'], 0, 13, 'utf-8'), $template_font_path, 24, '#000000', array(488, 336));//活动名
                $gd->text(mb_substr($ticket['activity_name'], 13, null, 'utf-8'), $template_font_path, 24, '#000000', array(488, 375));//活动名
            }
//            //动态处理活动地址
           if(mb_strlen($ticket['activity_address'],'UTF-8')<24){
               $gd->text($ticket['activity_address'], $template_font_path, 18, '#FFFFFF', array(335, 450));//活动地点
           }else{
               $gd->text(mb_substr($ticket['activity_address'],0,24,'utf-8'), $template_font_path, 18, '#FFFFFF', array(335, 440));//活动地点
               $gd->text(mb_substr($ticket['activity_address'],24,null,'utf-8'), $template_font_path, 18, '#FFFFFF', array(335, 460));//活动地点
           }



            $gd->text('举办方:' . $ticket['activity_undertake_name'], $template_font_path, 18, '#999999', array(335, 530));//举办方

            //这俩动态处理 姓名/手机号
            $gd->text($ticket['activity_user_name'], $template_font_path, 30, '#555555', array(1100 - 15 * (mb_strlen($ticket['activity_user_name'], 'UTF-8') - 3), 480));
            $gd->text($ticket['activity_user_mobile'], $template_font_path, 28, '#555555', array(1040 - 14 * (strlen($ticket['activity_user_mobile']) - 11), 530));


            //处理日期时间
            if (empty($ticket['activity_start_time'])) {
                $gd->text(date('d', $ticket['activity_published_at']), $template_font_path, 64, '#555555', array(130, 220));//日期
                $gd->text(weekday($ticket['activity_published_at'], '星期{w}', ''), $template_font_path, 24, '#555555', array(140, 324));//星期
                $gd->text(date('Y年m月', $ticket['activity_published_at']), $template_font_path, 18, '#555555', array(120, 375));//日期

            } else {
                $gd->text(date('d', $ticket['activity_start_time']), $template_font_path, 64, '#555555', array(130, 220));//日期
                $gd->text(weekday($ticket['activity_start_time'], '星期{w}', ''), $template_font_path, 24, '#555555', array(140, 324));//星期
                $gd->text(date('Y年m月', $ticket['activity_start_time']), $template_font_path, 18, '#555555', array(120, 375));//日期

            }

            //镶嵌二维码
            if (!file_exists($rootPath . $dir_path . $qrcode_file_name)) {
                //创建二维码
                qrcode($ticket['ticket_code'], $rootPath . $dir_path, $qrcode_file_name, true, '8', 'H', 2, false);
            }

            //使用二维码打水印 相对路径访问
            $gd->water($rootPath . $dir_path . $qrcode_file_name, array(1040, 180));

            //保存最终图片 相对路径访问
            $gd->save($rootPath . $dir_path . $ticket_file_name);
        }

        //
        $out_img = imagecreatefrompng($rootPath . $dir_path . $ticket_file_name);

        //输出电子票
        switch ($out_type) {
            case 'd': {
                header('Content-type:application/octet-stream');
                header("Content-Disposition: attachment; filename=" . md5($ticket['activity_name'] . '_' . $ticket['activity_user_name']) . '.png');
                break;
            }
            case 'p': {
                header('Content-type:image/png');
                break;
            }
        }

        //输出图片
        imagepng($out_img);
        //释放内存
        imagedestroy($out_img);
        die();
    }


    public function test()
    {
        var_dump($_SERVER);
        var_dump(UPLOAD_PATH);
    }
}
