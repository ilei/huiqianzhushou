/**
 * Created by qiuyang on 15/9/9.
 */

define(['jquery', 'lang'], function($,lang){
    //搜索
    $('#submit-keyword-search').click(function(){
        //$(this).removeClass('disabled').addClass('disabled');
        var keys = $('#input-keyword-search').val().trim();
        if (keys!='')
        {
            $('#pager-pre-next').removeClass('show').addClass('hidden');
            //获取当前页码
            var page_num = $('#act-page').val().trim();
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
            $('#act-page').val("1");


        }
        else{
            //alertModal('请输入您要查找的活动名称');
        }
        //$(this).enable();

    });

    //----选项卡切换绑定
    //未发布
    $('#act-norelease-t').click(function(){
        //为了显示优化 直接隐藏 上一页下一页按钮
        //$(this).removeClass('disabled').addClass('disabled');
        var keys = $('#input-keyword-search').val().trim();
        $('#pager-pre-next').removeClass('show').addClass('hidden');
        $('#act-next').removeClass('disabled').addClass('');
        getActList(0,$('#act-norelease'),1,keys,$(this));
        //设置当前页码 1 因为这是选项卡切换 都是从第一页开始的 不要告诉我你不理解
        $('#act-page').val("1");

        $('#act-tab-status').val("0");
        //$(this).enable();

    });

    //已关闭
    $('#act-closed-t').click(function(){
        //$(this).removeClass('disabled').addClass('disabled');
        var keys = $('#input-keyword-search').val().trim();
        $('#pager-pre-next').removeClass('show').addClass('hidden');
        $('#act-next').removeClass('disabled').addClass('');
        getActList(3,$('#act-closed'),1,keys,$(this));
        $('#act-page').val("1");
        $('#act-tab-status').val("3");
        //$(this).enable();
    });

    //进行中
    $('#act-going-t').click(function(){
        //$(this).removeClass('disabled').addClass('disabled');
        var keys = $('#input-keyword-search').val().trim();
        $('#pager-pre-next').removeClass('show').addClass('hidden');
        $('#act-next').removeClass('disabled').addClass('');
        getActList(1,$('#act-going'),1,keys,$(this));
        $('#act-page').val("1");
        $('#act-tab-status').val("1");
        //$(this).enable();
    });

    //已结束
    $('#act-ended-t').click(function(){
        //$(this).removeClass('disabled').addClass('disabled');
        var keys = $('#input-keyword-search').val().trim();
        $('#pager-pre-next').removeClass('show').addClass('hidden');
        $('#act-next').removeClass('disabled').addClass('');
        getActList(2,$('#act-ended'),1,keys,$(this));
        $('#act-page').val("1");
        $('#act-tab-status').val("2");

    });
    //下一页
    $('#act-next').click(function(){
        var keys = $('#input-keyword-search').val().trim();
        //获取当前页码
        var page_num = $('#act-page').val();
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

        //下一步要显示的页码
        var page_num_int = parseInt(page_num) +1
        //获取最大页码数
        var act_count =  $('#act-conut').val().trim();
        //点击的是下一页 所以上一页 直接接触封印 崩开
        $('#act-pre').removeClass('disabled').addClass('');
        //当前页码大于等于最大页码 说明娘西皮到最后一页了
        if (page_num_int>=parseInt(act_count))
        {
            page_num_int = act_count;
            //封印 下一页按钮 五星封印
            $(this).removeClass('disabled').addClass('disabled');
        }


        getActList(tab_status,$(act_list_div),page_num_int,keys,null);
        //设置当前页码
        $('#act-page').val(page_num_int);

    });
    //上一页
    $('#act-pre').click(function(){
        var keys = $('#input-keyword-search').val().trim();
        var page_num = $('#act-page').val();
        var tab_status = $('#act-tab-status').val().trim();
        var act_list_div = $('#act-going');
        switch (tab_status) {
            case  "0":
                act_list_div = $('#act-norelease');
                break;
            case "1":
                act_list_div = $('#act-going');
                break;
            case "2":
                act_list_div = $('#act-ended');
                break;
            case "3":
                act_list_div = $('#act-closed');
                break;
        }

        var page_num_int = parseInt(page_num)-1;

        //点击上一步 的同时 下一步应该解开封印
        $('#act-next').removeClass('disabled').addClass('');


        if (page_num_int<=1)
        {
            page_num_int = 1;
            //已经是第一页 封印
            $(this).removeClass('disabled').addClass('disabled');
        }

        getActList(tab_status,$(act_list_div),page_num_int,keys,null);
        $('#act-page').val(page_num_int);

    });

})
/**
 *获取活动列表
 * @parame starts 数据状态
 * @parame obj 显示容器
 * @parame p 显示页码
 */
function getActList(starts,obj,p,k,thisobj) {

    var objc = $(obj.children('.tab-content-cont'));

    var data = {s: starts, p: p, k: k};

    var actCount = "";
    //如果是切换选项卡动作  上一步 必须直接封印
    if (parseInt(p) <= 1) $('#act-pre').removeClass('disabled').addClass('disabled');
    //if (parseInt(p)<=) $('#act-pre').removeClass('disabled');

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
                //var objc = obj.children();
                $('#act-conut').val(data.countpage);
                objc.html(html);
                if (parseInt(data.countpage) >= 2) {
                    $('#pager-pre-next').removeClass('hidden').addClass('show');
                    //如果要显示上一页下一页需要从新获取该状态下的纪录总页数


                }
                else {
                    $('#pager-pre-next').removeClass('show').addClass('hidden');
                    //$('#act-pre').removeClass('disabled').addClass('disabled');
                }

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
            $('#model-loading').modal('hide');
        }
    });

}
