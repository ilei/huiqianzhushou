define(['jquery', 'messageControl', 'validate', 'langs'], function($,messager){

    var is_true_text = '';//验证用户名是否存在
    var username = '';
    var reg = /^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/;
    var is_email = '';//验证是否是邮箱
    //var is_true_status = '';//验证用户名是否存在
    var code = '';//校验码填写栏
    var verify = '';//验证码填写栏
    var checkcode_tishinr = '';//校验码验证提示栏
    var verify_tishinr = '';//验证码验证提示栏
    var username_tishinr = '';//用户名验证提示栏
    var form_action = '';//跳转到pwd_pwd的url

        //console.log(username);
    $(function(){

        //表单验证
        $("#pwdMobileForm").validate({
            errorPlacement: function(error, element){
                if(error){
                    element.parents().parents().prev('.tishinr').append(error);
                }
            },
            rules: {
                username: {
                    required: true,
                    remote: {
                        url: $("#ajax_check_username").val(),
                        type: "post",
                        data: {
                            username: function(){
                               return $("#username").val()
                            }
                        }
                    }
                },
                checkcode: {
                    required: true,
                    rangelength: [6, 6],
                    remote: {
                        type: "POST",
                        url: $("#check_mobile_code_url").val(),
                        dataType: "json",
                        data: {
                            mobile: function(){ return $('#username').val()},
                            code: function(){ return $("#checkcode").val()}
                        }
                    }
                }
            },
            messages: {
                username: {
                    required: $.lang.user_not_empty,
                    remote: $.lang.user_not_exist
                },
                checkcode: {
                    required: $.lang.mobile_verify_not_empty,
                    rangelength: $.lang.mobile_verify_format_err,
                    remote: $.lang.click_code_error
                },
                verify: {
                    required: $.lang.verify_not_empty,
                    rangelength: $.lang.verify_format_not,
                }
            },
            success: function(data){
                //console.log(data);
                code = $("#checkcode").val();
                verify = $("#verify").val();
                username = $("#username").val();
                username_tishinr = $("#username-error").text();
                checkcode_tishinr = $("#checkcode-error").text();
                verify_tishinr = $("#verify-error").text();

                if(reg.test(username)){
                    is_email = '1';
                    $(".email_hidden").hide();
                    $("#send_code_div").attr('class','col-xs-12');
                    $("#send_verify_code").text($.lang.send_email);//改变按钮文字内容
                    $("#checkcode-error").text("");
                    $('#email_find_pwd_btn').prop('checked',true);
                    $('#mobile_find_pwd_btn').prop('checked',false);
                }else{
                    is_email = '0';
                    $("#send_code_div").attr('class','col-xs-8');
                    $(".email_hidden").show();
                    $("#send_verify_code").text($.lang.send_code);//改变按钮文字内容
                    $('#mobile_find_pwd_btn').prop('checked',true);
                    $('#email_find_pwd_btn').prop('checked',false);
                }

                form_action = $("#find_pwd_pwd_url").val();
                $('#pwdMobileForm').attr('action',form_action + '?user_type=' +is_email);

                //判断是邮箱的话则按钮可以点击
                if(username != '' && username_tishinr == ''){
                    console.log(username,username_tishinr,$("#verify").val(),$("#verify_tishinr").text());
                    $("#send_verify_code").removeAttr("disabled");
                }else{
                    $("#send_verify_code").attr("disabled","disabled");
                }

                if(code != '' && username != '' && username_tishinr == '' && checkcode_tishinr == ''){
                    $("#next_submit").removeAttr('disabled');
                }else{
                    $("#next_submit").attr('disabled','disabled');
                }
            }
        });
    });

    $('#mobile_find_pwd_btn').click(function () {
        if(is_email == '1'){
            messager.show({
                type: "alert",
                content: "您输入的信息不是认证手机号，不能切换",
                autoClose: true
            },function(){
                $(this).prop('checked',false);
                $('#email_find_pwd_btn').prop('checked',true);
            });
        }else{
            $("#send_code_div").attr('class','col-xs-8');
            $(".email_hidden").show();
            $(this).prop('checked',true);
            $('#email_find_pwd_btn').prop('checked',false);
        }
    });
    $('#email_find_pwd_btn').click(function () {
        if(is_email == '0'){
            messager.show({
                type: "alert",
                content: "您输入的信息不是认证邮箱，不能切换",
                autoClose: true
            },function(){
                $(this).prop('checked',false);
                $('#mobile_find_pwd_btn').prop('checked',true);
            });
        }else{
            $(".email_hidden").hide();
            $("#send_code_div").attr('class','col-xs-12');
            $(this).prop('checked',true);
            $('#mobile_find_pwd_btn').prop('checked',false);
        }
    });

    $("#username").keyup(function(){
        //console.log($("#username-error").text());
        if($("#username-error").text() != ''){
            $("#send_verify_code").attr("disabled","disabled");
        }

        if($("#username").val() == ''){
            is_email = '';
            $("#send_verify_code").attr("disabled","disabled");
        }
    });

    $("#verify").keyup(function(){
        if($("#verify_tishinr").text() != ''){
            $("#send_verify_code").attr("disabled","disabled");
        }
    });

    //发送验证码
    $("#send_verify_code").click(function () {
        //两个校验条件
        if(username == '' || username_tishinr != ''){
            console.log("error click");
            return;
        }
        //弹出模态窗体
        $("#imgcode_verify_modal").modal("show");
    });

    //按钮倒计时
    var wait = 120;
    function time(o) {
        if (wait == 0) {
            o.removeAttr('disabled');
            $("#send_verify_code").removeAttr('disabled');
            $("#send_verify_code").next(".click_text").html($.lang.click_send_code);
            wait = 120;
        } else {
            o.attr("disabled", true);
            $("#send_verify_code").attr('disabled', true);
            if(is_email == 0){
                $("#send_verify_code").next(".click_text").html(wait + $.lang.next_time_send);
            }else{
                $("#send_verify_code").next(".click_text").html(wait + $.lang.next_time_send + $.lang.next_time_send_look);
            }
            wait--;
            setTimeout(function() {
                    time(o)
                },
                1000)
        }
    }

    $("#txt_code_verify").keyup(function(){
        $.ajax({
            url: $("#verify_url").val(),
            type: "post",
            data: {
                verify:$("#txt_code_verify").val()
            },
            success: function(data){
                if(data.status == 'ok'){
                    $("#txt_code_verify_tishinr").text('');
                    //关闭模态窗体
                    $("#imgcode_verify_modal").modal('hide');
                    //发送验证码
                    time($(this));
                    console.log($(this));
                    $.ajax({
                        type: "post",
                        url: $("#send_button_url").val(),
                        dataType: "json",
                        success: function(data){
                            if(data.status == '1'){
                                $("#txt_code_verify_tishinr").html("<nameg>验证码校验成功，等待返回</nameg>");
                                ////关闭模态窗体
                                //$("#imgcode_verify_modal").modal('hide');
                            }
                        }
                    });
                }else{
                    $("#txt_code_verify_tishinr").html("<p class='error_prompt'>验证码校验错误</p>");
                }
            }

        });
    });

    ////异步发送校验码
    //$("#send_verify_code").click(function(){
    //    time($(this));
    //    $.ajax({
    //        type: "post",
    //        url: $("#send_button_url").val(),
    //        dataType: "json"
    //    });
    //});

    $("#img_verify").click(function(){
        refresh_verify();
    });

});
//刷新验证码
function refresh_verify(){
    var verifyimg = $(".verifyimg").attr("src");
    if( verifyimg.indexOf('?')>0){
        $(".verifyimg").attr("src", verifyimg+'&random='+Math.random());
    }else{
        $(".verifyimg").attr("src", verifyimg.replace(/\?.*$/,'')+'?'+Math.random());
    }
}
