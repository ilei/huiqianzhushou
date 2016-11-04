define(['jquery', 'validate','messageControl', 'langs'], function ($,_,messager) {

    //公用DOM定义
    var dom_register_form = $("#register_form");//Form表单
    var dom_image_verify = $("#img_verify"); //图片验证码img标签
    var dom_check_agree = $("#chk_agree");//是否同意条款
    var dom_btn_send_verify = $("#btn_send_verify");//发送手机验证码按钮
    var dom_btn_submit = $("#btn_submit");//提交按钮
    var dom_a_change_image_verify = $("#change_image");//点链接更换验证码
    var dom_text_email = $("#txt_email");//电子邮件
    var dom_text_code_verify = $("#txt_code_verify");//图片验证码输入框
    var dom_text_verify = $("#txt_verify");//手机验证码输入框
    var dom_text_password = $("#txt_password");//密码输入框
    var dom_text_phone = $("#txt_phone");//电话输入框
    var dom_modal_imageVerify = $("#imgcode_verify_modal");//图片验证码弹窗
    var dom_i_eye=$("#i_eye");//魔性的小眼睛

    //初始化
    (function init() {
        //初始化字符串为“”
        dom_text_verify.val("");
        dom_text_password.val("");
        //dom_btn_submit.attr("disabled");
        //注册验证码单击事件
        dom_image_verify.click(function () {
            changeImageVerify();
        });
        dom_a_change_image_verify.click(function () {
            changeImageVerify();
        });

        //魔性的小眼睛单击事件
        dom_i_eye.click(function(){
            if($(this).hasClass('fa-eye')){
                $(this).removeClass('fa-eye');
                $(this).addClass('fa-eye-slash');
                dom_text_password.prop('type','text');
            }else{

                $(this).addClass('fa-eye');
                $(this).removeClass('fa-eye-slash');
                dom_text_password.prop('type','password');
            }

        });


        /*
        //注册统一条款change事件
        dom_check_agree.change(function () {
            if ($(this).is(":checked")) {
                dom_btn_submit.removeAttr("disabled");
            } else {
                dom_btn_submit.attr("disabled", "disabled");
            }
        });

        */
        //发送验证码
        dom_btn_send_verify.click(function () {
            //两个校验条件
            if (dom_text_phone.val().length < 11 || $(this).parent().find('label').text() != '') {
                console.log("error click");
                return;
            }

            changeImageVerify();//更新验证码
            //弹出模态窗体
            dom_modal_imageVerify.modal("show");
        });

        //validate自定义验证手机号
        $.validator.addMethod("isMobile", function (value, element) {
            var isMobile = null;
            var length = value.length;
            if (length == 11 && /^1[3584]\d{9}$/.test(value)) {
                isMobile = true;
            } else {
                isMobile = false;
            }
            return isMobile;
        }, $.lang.mobile_format_err);

        //验证密码

        $.validator.addMethod("formatPwd", function (value, element) {
            var c = /[a-zA-Z]+/;
            var cp = c.test(value);
            var n = /[0-9]+/;
            var np = n.test(value);
            return np && cp;
        }, $.lang.password_format_error);

        //validate自定义验证邮箱格式
        $.validator.addMethod("isEmail", function (value, element) {
            var pattern = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
            return pattern.test(value);
        }, $.lang.email_format_err);

        //validate自定义验证是否存在重复的手机号 用于保持错误信息的显示
        $.validator.addMethod("isDuplicateMobile", function (value, element) {
            console.log($("#txt_phone-error").text());
            if ($("#txt_phone-error").text() === $.lang.mobile_existed) {
                return false;
            } else {
                return true;
            }
        }, $.lang.mobile_existed);

    })();
    function changeImageVerify() {
        var img_src = dom_image_verify.attr("src");
        if (img_src.indexOf('?') > 0) {
            $(dom_image_verify).attr("src", img_src + '&random=' + Math.random());
        } else {
            $(dom_image_verify).attr("src", img_src.replace(/\?.*$/, '') + '?' + Math.random());
        }
    }

    //发送手机验证码
    function sendPhoneVerfiy() {


        countdown(dom_btn_send_verify, 100);//当前控件倒数100秒
        $.ajax({
            type: 'POST',
            url: $(dom_btn_send_verify).data('href'),
            dataType: 'json',
            data: {phone: dom_text_phone.val()},
        }).done(function (res) {
            console.log(res);
        });
    }

    //电话号码校验(Input部分) 仅管理Dom元素的显示隐藏 不参与最后的数值校验
    dom_text_phone.keyup(function () {
        if (/^1[3584]\d{9}$/.test($(this).val())) {
            $.ajax({
                type: 'POST',
                url: $(this).data('href'),
                dataType: 'json',
                data: {mobile: dom_text_phone.val()},
            }).done(function (res) {
                if (res) {
                    //dom_div_code_verify.show();
                    dom_btn_send_verify.removeAttr('disabled');
                    dom_text_phone.siblings(".input-error").find("label").text("");
                } else {
                    dom_text_phone.siblings(".input-error").html('<label id="txt_phone-error" class="error-class" for="txt_phone">' + $.lang.mobile_existed + '</label>');
                    dom_btn_send_verify.attr('disabled', true);

                }
            });
        } else {
            dom_btn_send_verify.attr('disabled', true);
        }
    });
    //图片验证码校验(Input部分)
    dom_text_code_verify.keyup(function () {
        if ($(this).val().length === 4) {
            $.ajax({
                type: "POST",
                url: $(this).data('href'),
                dataType: 'json',
                data: {
                    code: $(this).val()
                }
            }).done(function (res) {
                if (res) {
                    //手动清理错误提示
                    dom_text_code_verify.siblings(".input-error").html('');
                    //发送验证码
                    sendPhoneVerfiy();
                    //关闭模态窗体
                    dom_modal_imageVerify.modal('hide');

                } else {
                    //添加验证码错误提示
                    dom_text_code_verify.siblings(".input-error").html('<label id="txt_code_verify-error" class="error_prompt" for="txt_code_verify">' + $.lang.verify_error + '</label>');
                }
            });
        }

    });

    //注册按钮单击事件，手动触发form提交
    dom_btn_submit.click(function () {
        dom_btn_submit.attr("disabled", "disabled");
        dom_btn_submit.text("注册中...");
        //提交form
        dom_register_form.submit();
    });


    //设置Form表单验证
    dom_register_form.validate({
        errorClass: 'error-class',
        errorPlacement: function (error, element) {
            if (error) {
                //if ($(element).attr('id') == 'txt_phone') {
                    element.siblings(".input-error").html(error);
                //} else {
                //    element.siblings().html(error);
                //}
            }
        },
        showErrors:function(errorMap,errorList){

            if(this.numberOfInvalids()>0) {
                dom_btn_submit.removeAttr("disabled");
                dom_btn_submit.text("注册");

            }
            this.defaultShowErrors();
        },
        rules: {
            email: {
                required: true,
                isEmail: true,
                remote: {
                    url: dom_text_email.data('href'),
                    type: "post",
                    data: {
                        username: function () {
                            return dom_text_email.val();
                        }
                    }
                }
            },
            phone: {
                required: true,
                isMobile: true,
                isDuplicateMobile: true,
            },
            verify: {
                required: true,
                rangelength: [6, 6],
                remote: {
                    url: dom_text_verify.data('href'),
                    type: "post",
                    data: {
                        verify: function () {
                            return dom_text_verify.val();
                        },
                        phone: function () {
                            return dom_text_phone.val();
                        },
                    },
                },
            },
            password: {
                required: true,
                rangelength: [6, 18],
            },
        },
        messages: {
            email: {
                required: $.lang.email_not_empty,
                remote: $.lang.email_existed,
            },
            phone: {
                required: $.lang.mobile_not_empty,
            },
            verify: {
                required: $.lang.verify_not_empty,
                rangelength: $.lang.verify_format_err,
                remote: $.lang.verify_error,
            },
            password: {
                required: $.lang.password_not_empty,
                rangelength: $.lang.password_len_error,
            }
        },
        submitHandler: function (form) {


            $.ajax({
                type: 'POST',
                url: $(form).data('href'),
                dataType: 'json',
                data: $(form).serialize(),
                error: function () {
                    dom_btn_submit.text("注册");
                    dom_btn_submit.removeAttr("disabled");
                }
            }).done(function (res) {
                dom_btn_submit.text("注册");
                dom_btn_submit.removeAttr("disabled");
                if (res.status) {
                    window.location.href = res.url;
                } else {
                    dom_btn_submit.removeAttr("disabled");
                    dom_btn_submit.text("注册");
                    //alertModal(res.msg);
                    messager.show({
                        type:"alert",
                        title:"注册失败",
                        content:res.msg,
                        autoClose:true
                    })
                }
            });
        }
    });


    //验证码发送倒计时
    function countdown(selector, time) {

        if (time <= 0) {
            $(selector).removeAttr("disabled");
            $(selector).text("发送验证码");
        } else {
            //递归
            $(selector).attr("disabled", "disabled");
            $(selector).text(time + "秒后可重发");

            setTimeout(function () {
                countdown(selector, --time);
            }, 1000);
        }
    }


    //// 弹出modal框
    //function alertModal(msg, obj) {
    //    if (!obj) {
    //        obj = $('#tips-modal');
    //    }
    //    obj.modal('show');
    //    obj.find('.tips-msg').html(msg);
    //}
});


