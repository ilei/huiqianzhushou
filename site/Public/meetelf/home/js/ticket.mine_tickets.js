/**
 * Created by ManonLoki1 on 15/9/15.
 */

define(['jquery', 'pagerControlClient', 'langs', 'homeCommon'], function ($, pagerClient) {
    var currentPage = 1;
    var pageSize = $('#sel_pageSize').val();//页容量
    var query = $('#txt_search').val();//查询条件
    var status = $('#tab_status').children('.active').attr('v');//状态
    //URL
    var ticket_download_url = $("#hid_ticket_download_url").val();//票据下载的URL

    var dom_div_pager = $("#div_pager");
    var dom_div_container = $("#div_container");
    var dom_tab_status = $("#tab_status").find("button");
    var dom_btn_search = $("#btn_search");
    var dom_btn_reset = $("#btn_search_reset");
    var dom_txt_search = $("#txt_search");
    var dom_modal_view = $("#preview_modal");
    var dom_modal_preview_label = $("#preview_label");
    var dom_modal_preview_img = $("#preview_img");
    //加载数据
    var loadData = function () {
        $.ajax({
            url: '',
            type: "post",
            data: {
                p: currentPage,
                ps: pageSize,
                q: query,
                s: status
            },
            //beforeSend: function () {
            //    $('#model-loading').modal('show');
            //},
            //error: function () {
            //    $('#model-loading').modal('hide');
            //}
        }).done(
            function (res) {
                //$('#model-loading').modal('hide');
                if (res && res.status && res.data) {
                    //设置内容
                    dom_div_container.html(res.data.content);
                    //设置分页
                    dom_div_pager.html(res.data.pager);

                    //设置页签文字
                    var tab_a = dom_tab_status;

                    $(tab_a[0]).text(res.data.tab_allText);
                    $(tab_a[1]).text(res.data.tab_unuseText);
                    $(tab_a[2]).text(res.data.tab_usedText);

                    ////图片异步加载
                    //$("img").lazyload({
                    //    effect : "show",
                    //    threshold: 200
                    //});

                } else {
                    //设置页签文字
                    var tab_a = dom_tab_status;

                    $(tab_a[0]).text("全部");
                    $(tab_a[1]).text("未使用");
                    $(tab_a[2]).text("已使用");
                }
            }
        );

        return arguments.callee;
    };
//设置分页控件监视 只设置一次
    pagerClient.config({
        element: dom_div_pager,
        sizer: function (ps) {
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
    //Dom变化注册事件
    function register_prieview(){
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
    //监视div_Container 自动注册预览按钮事件
    dom_div_container.bind("DOMNodeInserted", function (e) {
       register_prieview.apply(this);
    });


    //首次打开页面注册事件
    register_prieview.apply(dom_div_container);

    //页签事件
    dom_tab_status.click(function () {
        //修改页签
        $("#tab_status").children().removeClass('active');
        $(this).addClass('active');

        //获取status
        status = $(this).attr('v');
        console.log('status');
    });


    //查询按钮Click
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
        dom_tab_status,
        dom_btn_search
    ]).each(function () {
        $(this).click(function () {
            currentPage = 1;
            loadData();
        });
    });

});
