/**
 * Created by ManonLoki1 on 15/9/10.
 */
define(['jquery', 'lang'], function ($, lang) {

    var page = 1;//默认页码
    var status = '0,1';//默认状态
    var query = '';//查询条件

    //缓存选项卡的标签对象 后面操作会用到
    var nav_tab_link = $("#tab_status").find('a');


    //处理Tab标签Click
    nav_tab_link.click(function () {
        //变更样式
        nav_tab_link.parent().removeClass('active');
        $(this).parent().addClass('active');

        //重新设置状态
        page = 1;
        status = $(this).attr('v');//默认状态
        //重新加载数据
        loadData();
    });

    //处理查询按钮Click
    $("#btn_search").click(function () {
        query = $("#txt_search").val();
        loadData();
    });


    //加载数据
    function loadData() {
        //清理历史数据
        $("#act_record").html("");
        //ajax获取数据
        $.ajax({
            url: "",
            type: 'post',
            data: {
                p: page,
                q: query,
                s: status
            }
        }).done(function (res) {
            if (res && res.status && res.data) {
                //存在数据
                //填充内容
                $("#act_record").html(res.data.content);
                //修改页签文字
                var tab_a = $("#tab_status").find('a');
                $(tab_a[0]).text(res.data.tab_allText);
                $(tab_a[1]).text(res.data.tab_successText);
                $(tab_a[2]).text(res.data.tab_unsuccessText);

                //增加分页事件
                $("#nav_pagerControl").find("a").click(function () {
                    page += parseInt($(this).attr("v"), 10);
                    loadData(page, status, $("#txt_search").val());

                });
            } else {
                //不存在数据 显示空模板
                $("#act_record").html('<div class="tab-content-cont"> <div class="tab-nodata"> <p class="text-center">暂无活动</p> <button class="btn btn-important center-block" type="submit" ><i class="fa fa-plus-circle"></i>发现活动</button> </div> </div>');
                //修改页签文字
                var tab_a = $("#tab_status").find('a');
                $(tab_a[0]).text("全部");
                $(tab_a[1]).text("已完成");
                $(tab_a[2]).text("未完成");
            }
        });
    }


    //页面加载时默认加载一次数据
    loadData(page, status, '');
});