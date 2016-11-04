/**
 * Created by qqyy on 2015/9/18.
 */

define(['jquery', 'homeCommon'], function ($) {

    //设置Document域
    document.domain = $("#document_domain").val();

    //开始时间
    var start_time = parseInt($("#hid_start_time").val(), 10);
    //计算服务端时间与当前客户端时间的时间差
    var server_time_diff = parseInt($("#hid_server_time").val()) - parseInt((new Date()).valueOf() / 1000, 10);

    var dom_sp_day = $("#sp_day");
    var dom_sp_hour = $("#sp_hour");
    var dom_sp_minute = $("#sp_minute");


    //添加参会人员————————————————————————————————————————————START
    $("#add_user_btn").click(function () {

        //验证活动状态
        var status = $("#hid_activity_status").val();


        if (status == 0) {
            alertModal("未发布的活动不能报名");
        } else if (status == 2) {
            alertModal("已结束的活动不能报名");
        } else if (status == 3) {
            alertModal("已关闭的活动不能报名");
        } else {

            //继续判断票据状态
            var can_signup = $("#hid_can_signup").val();
            var now_signup_type = $("#hid_now_signup_type").val();

            if (can_signup != 0) {
                if (now_signup_type == 'start') {
                    alertModal("未开始报名");
                } else {
                    alertModal("报名已截止");
                }
            } else {
                //弹出报名窗体
                var element = $('#modal_add_signup_user_ajax');
                var container_frame = element.find("iframe");
                container_frame.attr('src', container_frame.data('src'));
                element.modal('show');
            }


        }
    });

//处理Tootips
    (function () {
        $('[data-toggle="tooltip"]').tooltip();
    })();

//倒计时
    (function coundown() {

        //计算两个时间戳的差值
        var diffTimestamp = function (timestamp1, timestamp2) {
            var diffTimestamp = timestamp1 - timestamp2;

            var diffDay = parseInt(diffTimestamp / (24 * 60 * 60), 10);
            var diffHours = parseInt(diffTimestamp / (60 * 60) % 24, 10);
            var diffMinutes = parseInt(diffTimestamp / 60 % 60, 10);
            return {
                day: diffDay < 0 ? 0 : diffDay,
                hours: diffHours < 0 ? 0 : diffHours,
                minutes: diffMinutes < 0 ? 0 : diffMinutes,
            };
        };


        var nowTimestamp = parseInt((new Date()).valueOf() / 1000, 10) + server_time_diff;//获取当前时间戳 并转换单位为秒 使用时间差进行修正

        if (nowTimestamp < start_time) {

            var diffTime = diffTimestamp(start_time, nowTimestamp);//转换日期对象

            dom_sp_day.text(diffTime.day);
            dom_sp_hour.text(diffTime.hours);
            dom_sp_minute.text(diffTime.minutes);


            //倒计时
            setTimeout(coundown, 1000);
        } else {
            dom_sp_day.text(0);
            dom_sp_hour.text(0);
            dom_sp_minute.text(0);
        }


    })();//闭包 尽可能的减少对外部变量的影响

//分享
    window._bd_share_config = {
        common: {
            bdText: '',// 自定义分享内容
            bdDesc: '',// 自定义分享摘要
            bdUrl: $('#hid_shared_url').val(),// 自定义分享url地址
            bdPic: ''// 自定义分享图片
        },
        share: [{
            "bdSize": 24
        }],
        slide: [{
            bdImg: 2,
            bdPos: "right",
            bdTop: 100
        }]
    };
    with (document)0[(getElementsByTagName('head')[0] || body).appendChild(createElement('script')).src = 'http://bdimg.share.baidu.com/static/api/js/share.js?cdnversion=' + ~(-new Date() / 36e5)];


    // 弹出modal框
    function alertModal(msg, obj) {
        if (!obj) {
            obj = $('#tips-modal');
        }
        obj.modal('show');
        obj.find('.tips-msg').html(msg);
    }


});

