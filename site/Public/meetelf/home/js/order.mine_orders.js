/**
 * Created by ManonLoki1 on 15/9/10.
 */
define(['jquery', 'pagerControlClient', 'messageControl', 'langs', 'homeCommon'], function ($, pagerClient, messager) {


    var currentPage = 1;//默认页码
    var status = '0,1,2,3,4,5,6,7,8,9';//默认状态
    var query = '';//查询条件
    //URL
    var ticket_download_url = $("#hid_ticket_download_url").val();//票据下载的URL
    //DOM
    var dom_nav_tab_link = $("#tab_status").find('a');
    var dom_btn_search = $("#btn_search");
    var dom_btn_reset = $("#btn_search_reset");
    var dom_txt_search = $("#txt_search");
    var dom_act_record = $("#act_record");
    var dom_modal_view = $("#preview_modal");
    var dom_modal_preview_label = $("#preview_label");
    var dom_modal_preview_img = $("#preview_img");
    //设置分页控件监视 只设置一次
    pagerClient.config({
        element: dom_act_record,
        pager: function (p) {
            //更新当前页码
            currentPage = p;
            loadData();
        }
    });
    function register_preview() {
        // 重新注册预览按钮事件
        var preview_a = $(this).find("a[t='p']");
        preview_a.unbind();
        preview_a.click(function (element) {
            dom_modal_preview_label.text($(this).attr('aname'));
            var image_path = ticket_download_url + "/guid/" + $(this).attr('tid') + "/t/p";
            dom_modal_preview_img.attr('src', image_path);
            dom_modal_view.modal("show");
        });
    }

    //监视act_record 自动注册预览按钮事件
    dom_act_record.bind("DOMNodeInserted", function (e) {
        register_preview.apply(this);
    });
    register_preview.apply(dom_act_record);


    //加载数据
    var loadData = function () {
        //ajax获取数据
        $.ajax({
            url: '',
            type: 'post',
            data: {
                p: currentPage,
                q: query,
                s: status
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

                //修改页签文字
                var tab_a = $("#tab_status").find('a');
                $(tab_a[0]).text(res.data.tab_allText);
                $(tab_a[1]).text(res.data.tab_successText);
                $(tab_a[2]).text(res.data.tab_unsuccessText);
                $(tab_a[3]).text(res.data.tab_cancelledText);

                //设置内容
                dom_act_record.html(res.data.content);

                $.imgReload();
            } else {

                var tab_a = $("#tab_status").find('a');


                if (res.data) {
                    $(tab_a[0]).text(res.data.tab_allText);
                    $(tab_a[1]).text(res.data.tab_successText);
                    $(tab_a[2]).text(res.data.tab_unsuccessText);
                    $(tab_a[3]).text(res.data.tab_cancelledText);
                } else {
                    $(tab_a[0]).text("全部");
                    $(tab_a[1]).text("已完成");
                    $(tab_a[2]).text("未完成");
                    $(tab_a[3]).text("已取消");

                }
                //不存在数据 显示空模板
                //dom_act_record.html('<div class="tab-content-cont"> <div class="tab-nodata"> <p class="text-center">暂无活动</p> <button class="btn btn-important center-block" type="submit" style="display:none"><i class="fa fa-plus-circle"></i>发现活动</button> </div> </div>');
                dom_act_record.html('<div class="tab-content-cont"> <div class="tab-nodata"> <p class="text-center">暂无活动</p> </div> </div>');
            }


        });

        return arguments.callee;
    };


    //处理Tab标签Click
    dom_nav_tab_link.click(function () {
        //变更样式
        dom_nav_tab_link.parent().removeClass('active');
        $(this).parent().addClass('active');
        //重新设置状态
        status = $(this).attr('v');//默认状态

    });

    //处理查询按钮Click
    dom_btn_search.click(function () {
        query = dom_txt_search.val();
    });

    //处理重置按钮click
    dom_btn_reset.click(function () {
        $(this).hide();
        dom_txt_search.val('');
        dom_btn_search.click();
    });

    //处理txt_search输入事件
    dom_txt_search.keyup(function (event) {
        if ($(this).val().length == 0) {
            dom_btn_reset.hide();
        } else {
            dom_btn_reset.show();
        }
        //处理回车事件
        if (event.keyCode === 13) {
            dom_btn_search.click();
        }
    });


    //统一解决设置Page为1的问题
    $([
        dom_nav_tab_link,
        dom_btn_search
    ]).each(function () {
        $(this).click(function () {
            currentPage = 1;
            loadData();
        });
    });

});
