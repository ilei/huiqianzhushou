/**
 * Created by qiuyang on 15/9/9.
 */

define(['jquery','lazyload', 'lang'], function($,lazyload,lang){
    //搜索
    //$("img").lazyload();
    $('#submit-keyword-search').click(function(){
        //$(this).removeClass('disabled').addClass('disabled');
        var keys = $('#input-keyword-search').val().trim();
        if (keys!='')
        {
            $('#pager-pre-next').removeClass('show').addClass('hidden');
            //获取当前页码
            //var page_num = $('#act-page').val().trim();
            //获取当前选项卡状态
            var tab_status = $('#act-tab-status').val().trim();
            //获取显示区域
            var act_list_div = $('#act-going');
            switch (tab_status) {
                case  "0":
                    act_list_div = '#act-norelease';
                    break;
                case "1":
                    act_list_div = '#act-going';
                    break;
                case "2":
                    act_list_div = '#act-ended';
                    break;
                case "3":
                    act_list_div = '#act-closed';
                    break;
            }

            getActList(tab_status,$(act_list_div),1,keys,$(this));



        }
        else{
            //alertModal('请输入您要查找的活动名称');
        }
        //$(this).enable();

    });

    //----选项卡切换绑定
    //未发布
    $('#act-norelease-t').click(function(){
        var keys = $('#input-keyword-search').val().trim();

        getActList(0,$('#act-norelease'),1,keys,$(this));
        //设置当前页码 1 因为这是选项卡切换 都是从第一页开始的 不要告诉我你不理解

        $('#act-tab-status').val("0");
        //$(this).enable();

    });

    //已关闭
    $('#act-closed-t').click(function(){

        var keys = $('#input-keyword-search').val().trim();

        getActList(3,$('#act-closed'),1,keys,$(this));
        //$('#act-page').val("1");
        $('#act-tab-status').val("3");
        //$(this).enable();
    });

    //进行中
    $('#act-going-t').click(function(){

        var keys = $('#input-keyword-search').val().trim();

        getActList(1,$('#act-going'),1,keys,$(this));

        $('#act-tab-status').val("1");

    });

    //已结束
    $('#act-ended-t').click(function(){
        //$(this).removeClass('disabled').addClass('disabled');
        var keys = $('#input-keyword-search').val().trim();
        //$('#pager-pre-next').removeClass('show').addClass('hidden');
        //$('#act-next').removeClass('disabled').addClass('');
        getActList(2,$('#act-ended'),1,keys,$(this));
        //$('#act-page').val("1");
        $('#act-tab-status').val("2");

    });
    //绑定事件
    bindpageClick();
})

function bindpageClick(){
    $("#nav_pagerControl ul li").on("click","a",function() {
        var p = $(this).attr('p');//获得页
        var keys = $('#input-keyword-search').val().trim();
        //获取当前页码
        //var page_num = $('#act-page').val();
        //获取当前选项卡状态
        var tab_status = $('#act-tab-status').val().trim();
        //获取显示区域
        var act_list_div = $('#act-going');
        switch (tab_status) {
            case  "0":
                act_list_div = '#act-norelease';
                break;
            case "1":
                act_list_div = '#act-going';
                break;
            case "2":
                act_list_div = '#act-ended';
                break;
            case "3":
                act_list_div = '#act-closed';
                break;
        }
        getActList(tab_status,$(act_list_div),p,keys,this);

    });
}
/**
 *获取活动列表
 * @parame starts 数据状态
 * @parame obj 显示容器
 * @parame p 显示页码
 */
function getActList(starts,obj,p,k,thisobj) {

    var objc = $(obj.children('.tab-content-cont'));

    var data = {s: starts, p: p, k: k};

    //var actCount = "";
    //如果是切换选项卡动作  上一步 必须直接封印
    //if (parseInt(p) <= 1) $('#act-pre').removeClass('disabled').addClass('disabled');
    //if (parseInt(p)<=) $('#act-pre').removeClass('disabled');
    var tab_url = $('#tab_url').val();
    $.ajax({
        url: tab_url,
        type: 'POST',
        data: data,
        dataType: 'json',
        beforeSend: function () {
            objc.button('loading');
            $('#model-loading').modal('show');
        },
        success: function (data) {
            if (data.status == 'ok') {
                html = data.data;
                pagehtml = data.viewpage;
                //var objc = obj.children();
                //$('#act-conut').val(data.countpage);
                objc.html(html);//这里是列表数据


                var objc_page = $('#my_page_next_pre');
                if (objc_page)
                {
                    objc_page.html(pagehtml);
                    bindpageClick();
                }


                //if (thisobj) thisobj.html(pagehtml);
                $('#act-going-t').text('进行中(' + data.countrelease + ')');
                $('#act-norelease-t').text('未发布(' + data.countdebug + ')');
                $('#act-closed-t').text('已关闭(' + data.countclose + ')');
                $('#act-ended-t').text('已结束(' + data.countover + ')');

                //if (thisobj) thisobj.removeClass('disabled').addClass('');


            } else if (data.status == 'ko') {
                //alertModal(data.msg);
                //obj.button('reset');
            }
        },
        complete: function () {
            // obj.button('reset');
            //$('#model-loading').modal('hide');
            //$("img").lazyload();
            //bindpageClick();
        }
    });

}
