/**
 * Created by ManonLoki1 on 15/9/21.
 */

define(["jquery", "pagerControlClient", 'homeCommon'], function ($, pagerClient) {
    var aid = $("#hid_aid").val();//活动ID
    var currentPage = 1;//当前页
    var pageSize = 10;//页码
    var query = '';//查询条件
    var status = '';//状态 默认空
    var cachedOrderList=null;//缓存的订单列表
    var cachedOrderNumberList=[];//

    var ajax_review_order_data_url=$("#hid_ajax_review_order_data_url").val();
    var ajax_review_aduit_url=$("#hid_ajax_review_aduit_url").val();
    var dom_order_item_container=$("#table_order_item_container");
    var dom_div_pager=$("#div_pager");
    var dom_btn_search=$("#btn_search");
    var dom_btn_reset = $("#btn_search_reset");
    var dom_txt_search=$("#txt_search");
    var dom_chk_all_select=$("#chk_all_select");
    var dom_ddl_order_status= $("#ddl_order_status").find('a');


    //设置分页控件监视 只设置一次
    pagerClient.config({
        element: dom_div_pager,
        sizer: function (ps) {
            currentPage = '1';
            //更新pageSize
            pageSize = ps;
            loadData();
        },
        pager: function (p) {
            //更新当前页码
            currentPage = p;
            loadData();
        }
    });

    //加载数据
    var loadData = function () {
        //获取订单数据
        $.ajax({
            url: ajax_review_order_data_url,
            type: "post",
            data: {
                "aid": aid,
                "p": currentPage,
                'ps': pageSize,
                'q': query,
                's': status
            },
            //beforeSend: function () {
            //    $('#model-loading').modal('show');
            //},
            //error: function () {
            //    $('#model-loading').modal('hide');
            //}
        }).done(function (res) {
            //$('#model-loading').modal('hide');

            if (res && res.status) {
                //缓存数据源
                cachedOrderList=res.data.orders;

                //设置分页
                dom_div_pager.html(res.data.pager);

                //处理表格
                dom_order_item_container.html(res.data.html);

                //处理全选按钮
               if( res.data.needAduit){
                   dom_chk_all_select.show();

                   //复选框事件
                   $("#table_order_item_container").find(":checkbox").change(function () {
                       $("#sp_order_select_count").text($("#table_order_item_container").find(":checked").length);
                   });

               }else{
                   dom_chk_all_select.hide();
               }

                //单个审批事件
                $("#table_order_item_container").find('a[href="#"]').click(function(){

                    var order_number=$(this).attr('v');//获取订单号
                    var aduit_type=$("#btn_moreoperation").attr('v');//获取操作类型
                    $(cachedOrderList).each(function(){

                        if(this.order_number===order_number){
                            //缓存选中订单
                            cachedOrderNumberList=[this.order_number];
                            //准备数据
                            $("#sp_single_modal_orderNumber").text(this.order_number);
                            $("#sp_single_modal_finishedTime").text(this.order_finished_time_string);
                            $("#sp_single_modal_userName").text(this.buyer_name)
                            $("#sp_single_modal_type").text(this.buyer_type_string);
                            $("#sp_single_modal_buyerName").text(this.buyer_name);
                            $("#sp_single_modal_buyerMoble").text(this.buyer_mobile);
                            $("#sp_single_modal_ticketName").text(this.user_ticket_name);
                            $("#sp_single_modal_ticketPrice").text(this.order_total_price_string);

                            if(aduit_type==7){
                                $("#sp_single_modal_verify_reason").hide();
                                $("#sp_single_modal_verify_reason").val('');
                                $("#btn_single_modal_commit").text('审批通过');
                                $("#btn_single_modal_commit").attr('class','btn btn-save');
                            }else if(aduit_type==8){
                                $("#sp_single_modal_verify_reason").show();
                                $("#sp_single_modal_verify_reason").val(' ');
                                $("#btn_single_modal_commit").text('审批拒绝');
                                $("#btn_single_modal_commit").attr('class','btn btn-important');
                            }


                            //弹出模态窗口
                            $("#orderapproval").modal('show');
                            return false;//结束循环
                        }
                    });

                });
            }
        });
        return arguments.callee;//返回函数自己 实现优雅的闭包
    };


    //查询
    dom_btn_search.click(function () {
        query = dom_txt_search.val();
    });
    //重置按钮
    dom_btn_reset.click(function () {
        $(this).hide();
        dom_txt_search.val('');
        dom_btn_search.click();
    });
    //搜索条件变化
    dom_txt_search.keyup(function(event){
        if($(this).val().length==0){
            dom_btn_reset.hide();
        }else{
            dom_btn_reset.show();
        }
        //处理回车事件
        if(event.keyCode===13){
            dom_btn_search.click();
        }
    });


    //订单选择变化
    dom_ddl_order_status.click(function(){
        currentPage=1;
        status=$(this).attr('v');
        loadData();
    });

    //全选联动
    dom_chk_all_select.change(function () {

        var is_checked = $(this).is(":checked");
        var chk_list = $("#table_order_item_container").find(":checkbox");
        chk_list.prop("checked", is_checked);

        $("#sp_order_select_count").text($("#table_order_item_container").find(":checked").length);
    });


    //审批选项变化
    $("#dropdown_aduit_operation").find("a").click(function(){
        $("#btn_moreoperation")
            .html($(this).text()+'&#8195;<i class="fa fa-angle-down"></i>')
            .attr('v',$(this).attr('v'));
    });
    //设置默认审批选项
    (function(){
        var defualt_aduit_operation= $("#dropdown_aduit_operation").find("a").first();
        $("#btn_moreoperation")
            .html($(defualt_aduit_operation).text()+'&#8195;<i class="fa fa-angle-down"></i>')
            .attr('v',$(defualt_aduit_operation).attr('v'));

    })();


    //批量审批按钮
    $("#btn_batch_aduit").click(function(){

        var order_number_list=[];

        $("#table_order_item_container").find(":checked").each(function(){
            order_number_list.push($(this).attr('v'));
        });

        //缓存选中订单
        cachedOrderNumberList=order_number_list;


        //处理数据
        var aduit_type=$("#btn_moreoperation").attr('v');//获取操作类型
        if(aduit_type==7){
            $("#sp_mutil_modal_verify_reason").hide();
            $("#sp_mutil_modal_verify_reason").val('');
            $("#btn_mutil_modal_commit").text('审批通过');
            $("#btn_mutil_modal_commit").attr('class','btn btn-save');
        }else if(aduit_type==8){
            $("#sp_mutil_modal_verify_reason").show();
            $("#sp_mutil_modal_verify_reason").val('');
            $("#btn_mutil_modal_commit").text('审批拒绝');
            $("#btn_mutil_modal_commit").attr('class','btn btn-important');
        }
        $("#sp_mutil_modal_count").text(order_number_list.length);

        //弹出模态窗体
        $("#allapproval").modal('show');
    });

    //单个审批模态窗口点击
    $("#btn_single_modal_commit").click(function(){

        aduit_request(function(){
            $("#orderapproval").modal("hide");
            console.log("hid");
        },$("#sp_single_modal_verify_reason").val());

    });
    //批量审批模态窗口按钮点击
    $("#btn_mutil_modal_commit").click(function(){
        aduit_request(function(){
            $("#allapproval").modal("hide");
        },$("#sp_mutil_modal_verify_reason").val());

    });

    //审批请求
    function aduit_request(callback,verifyReason){

        $.ajax({
            url:ajax_review_aduit_url,
            type:"post",
            data:{
                "order_number_list":cachedOrderNumberList,
                "verify_type":$("#btn_moreoperation").attr('v'),
                'verify_reason':verifyReason
            },
            //beforeSend: function () {
            //    $('#model-loading').modal('show');
            //},
            //error: function () {
            //    $('#model-loading').modal('hide');
            //}
        }).done(function(res){
            //$('#model-loading').modal('hide');
            if(res&&res.status){

                if(callback){
                    callback(res);
                }

                loadData();//重新获取数据
            }
        });
    }


    //统一解决设置Page为1并且load数据的问题
    $([
        dom_btn_search,
        dom_ddl_order_status
    ]).each(function () {
        $(this).click(function () {
            currentPage = 1;
            loadData();
        });
    });
});
