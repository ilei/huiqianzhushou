define(['jquery', 'pagerControlClient', 'homeCommon'], function ($, pagerClient) {
    var currentPage = 1;
    var pageSize = 5;//页容量
    //设置分页控件监视 只设置一次
    pagerClient.config({
        element: "#div_pager",
        sizer: function (ps) {
            //更新pageSize
            pageSize = ps;
            page_list();
        },
        pager: function (p) {
            //更新当前页码
            currentPage = p;
            page_list();
        }
    });

       //分页
    $("#ul_pager").find("a").click(function(){
        //组装数据
        currentPage = $(this).attr('p');
        console.log(currentPage);
        pageSize = $("#sel_pageSize option:selected").text();
        console.log(pageSize);
        // page_list();
    });

    function page_list(){
        $.ajax({
            url: $('#url_wallet').val(),
            type: "post",
            data: {
                p:currentPage,i:pageSize
                
            },
            success: function(data){
                    //设置分页
                    $("#div_pager").html(data.pager);
                    $("#list_tbody").html(data.data);
            }

        });
    }


    //Dom加载完毕后加载一起数据
    page_list();
});
