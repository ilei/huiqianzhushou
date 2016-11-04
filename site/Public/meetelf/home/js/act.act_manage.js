/**
 * Created by ManonLoki1 on 15/9/18.
 */
define(["jquery", 'zeroClipboard', 'messageControl', 'homeCommon'], function ($, Clipboard, messager) {

    //获取Dom数据
    var start_time = parseInt($("#hid_startTime").val(), 10);
    var end_time = parseInt($("#hid_endTime").val(), 10);
    var activity_status = parseInt($("#hid_status").val(), 10);
    var aid = $("#hid_aid").val();
    var ajax_manage_close_activity_url = $("#hid_manage_close_activity_url").val();
    var ajax_manage_cancel_activity_url = $("#hid_manage_cancel_activity_url").val();
    var ajax_manage_del_activity_url = $("#hid_manage_del_activity_url").val();
    var manage_copy_activity_url = $("#hid_manage_copy_activity_url").val();
    var activity_is_verify = $("#activity_is_verify").val();
    var dom_img_shared_qrcode = $("#img_shared_qrcode");


    //计算服务端时间与当前客户端时间的时间差
    var server_time_diff = parseInt($("#hid_server_time").val()) - parseInt((new Date()).valueOf() / 1000, 10);


    //倒计时
    (function coundown() {
        //非已发布活动 不进行计算
        if (activity_status != 1) {
            return;
        }

        //计算两个时间戳的差值
        var diffTimestamp = function (timestamp1, timestamp2) {
            var diffTimestamp = timestamp1 - timestamp2;

            var diffDay = parseInt(diffTimestamp / (24 * 60 * 60), 10);
            var diffHours = parseInt(diffTimestamp / (60 * 60) % 24, 10);
            var diffMinutes = parseInt(diffTimestamp / 60 % 60, 10);
            var diffSeconds = parseInt(diffTimestamp % 60);
            return {
                day: diffDay < 0 ? 0 : diffDay,
                hours: diffHours < 0 ? 0 : diffHours,
                minutes: diffMinutes < 0 ? 0 : diffMinutes,
                seconds: diffSeconds < 0 ? 0 : diffSeconds
            };
        };
        var countdownFormat = function (diffTime) {
            var text = "";
            var interval = 0;

            if (diffTime.day > 0) {
                text = diffTime.day + "天" + diffTime.hours + "小时" + diffTime.minutes + "分";
                interval = 60 * 1000;
            } else {
                text = diffTime.hours + "小时" + diffTime.minutes + "分" + diffTime.seconds + "秒";
                interval = 1000;
            }

            return {
                text: text,
                interval: interval
            };

        }


        var nowTimestamp = parseInt((new Date()).valueOf() / 1000, 10) + server_time_diff;//获取当前时间戳 并转换单位为秒 使用时间差进行修正

        var text = "";//显示文本


        //永不结束
        if (isNaN(start_time) || isNaN(end_time)) {
            text = "活动中";
        } else if (nowTimestamp > end_time) {
            text = "已结束";
        } else if (nowTimestamp > start_time) {
            text = "进行中";
        } else {
            $("#p_time").text("距离活动开始还有");
            var diffTime = diffTimestamp(start_time, nowTimestamp);//转换日期对象
            var formatResult = countdownFormat(diffTime);

            text = formatResult.text;
            //倒计时
            setTimeout(coundown, formatResult.interval);
        }

        $("#span_countdown").text(text);


    })();//闭包 尽可能的减少对外部变量的影响


    //剪切板
    var client = new Clipboard($("#a_copyButton"));
    client.on("ready", function (readyEvent) {

        client.on("aftercopy", function (sender) {
            $("#a_copyButton").text('已复制');
            setTimeout(function () {
                $("#a_copyButton").text("重新复制");
            }, 2000);
        });

    });

    //点击图片下载
    dom_img_shared_qrcode.click(function () {
        window.open($(this).prop('src') + "?type=d");
    });

    //取消活动
    $("#btn_cancel_activity").click(function () {
        messager.show(
            {
                type: "confirm",
                title: "取消活动",
                content: "取消活动会影响报名者"
            },
            function (type) {
                if (type === 'submit') {
                    $.ajax({
                        url: ajax_manage_cancel_activity_url,
                        type: "post",
                        data: {
                            "aid": aid
                        }
                    }).done(function (res) {
                        if (res && res.status) {
                            window.location.reload(true);
                        }
                    });
                }
            }
        );
    });

    //关闭活动
    $("#btn_close_activity").click(function () {
        messager.show(
            {
                type: "confirm",
                title: "关闭活动",
                content: "已关闭的活动不能再编辑"
            }, function (type) {
                if (type === 'submit') {
                    $.ajax({
                        url: ajax_manage_close_activity_url,
                        type: "post",
                        data: {
                            "aid": aid
                        }
                    }).done(function (res) {
                        if (res && res.status) {
                            window.location.reload(true);
                        }
                    });
                }
            });
    });
    //删除活动
    $("#btn_del_activity").click(function () {

        messager.show({
            type: "confirm",
            title: "删除活动",
            content: "删除的活动不可再查看与编辑"
        }, function (type) {
            if (type === 'submit') {
                $.ajax({
                    url: ajax_manage_del_activity_url,
                    type: "post",
                    data: {
                        "aid": aid
                    }
                }).done(function (res) {
                    if (res && res.status) {
                        window.location.reload(true);
                    }
                });
            }
        });
    });

    //复制活动
    $("#btn_copy_activity").click(function () {
        messager.show({}, function (type) {
            if (type === 'submit') {
                window.location.href = manage_copy_activity_url;
            }
        });
    });

    //RTH_________________________________________________________________________
    // 弹出modal框
    //function alertModal(msg, obj) {
    //    if (!obj) {
    //        obj = $('#tips-modal');
    //    }
    //    obj.modal('show');
    //    obj.find('.tips-msg').html(msg);
    //}

    //活动未发布提示框
    $("#signup_user_info_li").click(function () {
        if (activity_status == '0') {
            messager.show({
                type:"alert",
                content:"未发布活动不能查看",
                autoClose:true
            });
        }
        if (activity_is_verify == 0 && activity_status != 0) {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });
        };
    });
    $("#order_verify_li").click(function () {
        if (activity_status == '0') {
            messager.show({
                type:"alert",
                content:"未发布活动不能查看",
                autoClose:true
            });
        }
        if (activity_is_verify == 0 && activity_status != 0) {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });   
        };
    });
    $("#signup_and_verify_ticket").click(function () {
        if (activity_status == '0') {
            messager.show({
                type:"alert",
                content:"未发布活动不能查看",
                autoClose:true
            });
        }
        if (activity_is_verify == 0 && activity_status != 0) {
            messager.show({
                type:"alert",
                content:"审核中活动不能进行签到",
                autoClose:true
            });   
        };
    });
    $("#signup_ticket_li").click(function () {
        if (activity_is_verify == 0 && activity_status != '0') {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });
        };
    });  //活动未发布提示框
    $("#signin_form_li").click(function () {
        if (activity_is_verify == 0 && activity_status != '0') {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });
        };
    });
    $("#look_ticket_info_id").click(function () {
        if (activity_status == '0') {
            messager.show({
                type:"alert",
                content:"未发布活动不能查看",
                autoClose:true
            });
        }
        if (activity_is_verify == 0 && activity_status != 0) {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });   
        };
    });
    $("#look_all_order_id").click(function () {
        if (activity_status == '0') {
            messager.show({
                type:"alert",
                content:"未发布活动不能查看",
                autoClose:true
            });
        }
        if (activity_is_verify == 0 && activity_status != 0) {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });   
        };
    });

    //RTH_________________________________________________________________________
});
