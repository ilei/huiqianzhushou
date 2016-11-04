define(['jquery', 'validate'], function($){

    var username_tishi = '';
    var username = '';

    $.validator.addMethod("verify_mobile",function(value,element,params){
        console.log(value);//当前值
        console.log(element);//值本身
        console.log(params);
        var mobile =  /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
        if(mobile.test(value)){
            return true;
        }else{
            return false;
        }
    },"手机号格式不正确");
    //表单验证
    $("#username-pwd-form").validate({
        //onkeyup:true,//按键抬起
        //onfocusout:false,//按键抬起
        //onsubmit:false,//提交事件
        //onclick:false,//点击事件
        submitHandler: function(form){
            form.submit();
        },
        errorPlacement: function(error, element){
            element.css('border-color', '#a94442');
        },
        showErrors:function(error, element){
            if(error && element.length){
                $("#error-msg").html('').html($(element)[0].message);
                $("#error-msg").parents(".error-position").show();
            }else{
                $("#error-msg").parents(".error-position").hide();
            }
        },
        //errorLabelContainer: $("#username-pwd-form .tishi"),
        //errorPlacement: function(error, element){
        //    //element.css('border-color', '#a94442');
        //    element.prev(".tishi").html(error);
        //},
        rules: {
            username: {
                required: true,
                rangelength: [11, 11],
                verify_mobile: true,
                remote: {
                    url: $("#check-mobile-url").val(),
                    type: 'post',
                    dataType: 'json',
                    data: {
                        mobile: function(){
                            return $("#username").val()
                        }
                    }
                }
            },
            verify: {
                required: true,
                rangelength: [6, 6],
                remote: {
                    url: $("#ajax_check_mobile_code_url").val(),
                    type: 'post',
                    dataType: 'json',
                    data: {
                        mobile: function(){
                            return $("#username").val()
                        },
                        code: function(){
                            return $("#verify").val()
                        }
                    }
                }
            },
        },
        messages: {
            username: {
                required: "请输入手机号",
                rangelength: "手机号位数不对",
                remote: "手机号不存在"
            },
            verify: {
                required: "校验码不能为空",
                rangelength: "校验码必须为6个字符",
                remote: "校验码不正确"
            },
        }
    });

    //发送验证码
    $("#send_verify_code").click(function () {
        username_tishi = $("#error-msg").text();
        username = $("#username").val();
        //两个校验条件
        if(username == '' || username.length != 11){
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
            $("#send_verify_code").next(".click_text").html('点击发送校验码');
            wait = 120;
        } else {
            o.attr("disabled", true);
            $("#send_verify_code").attr('disabled', true);
            $("#send_verify_code").next(".click_text").html(wait + '秒后可重新操作');
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
                    $("#verify").removeAttr('disabled');
                    //发送验证码
                    time($(this));
                    console.log($(this));
                    $.ajax({
                        type: "post",
                        url: $("#send_button_url").val(),
                        dataType: "json",
                        data: {
                            mobile: function(){
                                return $("#username").val();
                            }
                        },
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

    $("#img_verify").click(function(){
        refresh_verify();
    });

    //表单验证
    $("#pwd_pwd_form").validate({
        //onkeyup:true,//按键抬起
        //onfocusout:false,//按键抬起
        //onsubmit:false,//提交事件
        //onclick:false,//点击事件
        submitHandler: function(form){
            form.submit();
        },
        //errorLabelContainer: $("#username-pwd-form .tishi"),
        //errorPlacement: function(error, element){
        //    //element.css('border-color', '#a94442');
        //    element.prev(".tishi").html(error);
        //},
        errorPlacement: function(error, element){
            element.css('border-color', '#a94442');
        },
        showErrors:function(error, element){
            if(error && element.length){
                $("#error-msg").html('').html($(element)[0].message);
                $("#error-msg").parents(".error-position").show();
            }else{
                $("#error-msg").parents(".error-position").hide();
            }
        },
        rules: {
            password: {
                required: true,
                rangelength: [6, 18]
            },
            re_password: {
                required: true,
                equalTo: "#password"
            },
        },
        messages: {
            password: {
                required: "请输入新密码",
                rangelength: "新密码位数6到18位字符"
            },
            re_password: {
                required: "确认密码不能为空",
                equalTo: "确认密码必须跟新密码一致"
            },
        }
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


