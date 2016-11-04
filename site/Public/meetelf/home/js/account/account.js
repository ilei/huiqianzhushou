define(['jquery', 'lang', 'messageControl', 'validate'], function ($, lang, messager) {
    var dom_register_form = $("#form_add");//Form表单
    var dom_btn_submit = $("#save");//提交按钮
    //validate自定义验证用户名格式
    $.validator.addMethod("isusername", function (value, element) {
        var pattern = /^[a-zA-Z0-9_]{1,}$/;
        return pattern.test(value);
    }, '字母数字下划线');
    //注册按钮单击事件，手动触发form提交
    dom_btn_submit.click(function () {

        //提交form
        dom_register_form.submit();
    });
    // 注册FORM验证
    $(dom_register_form).validate({
        errorClass: "invalid",
        errorPlacement: function (error, element) {
            // element.parent().parent().parent().find('.tishinr').append(error);
            element.parent().next('.tishinr').html(error);
        },
        rules: {
            username: {
                isusername: true,
                required: true,
                rangelength: [2, 10]
            },
            password: {
                required: true,
                rangelength: [6, 10]
            },
            remark: {
                rangelength: [0, 25]
            }
        },
        messages: {
            username: {
                isusername: lang.only_num_word,
                required: lang.user_not_empty,
                rangelength: "账号必须为2到10个字符"
            },
            password: {
                required: lang.password_not_empty,
                rangelength: "密码为6到10位字符"
            },
            remark: {
                rangelength: "请少于25个字符"
            }
        },
        submitHandler: function (form) {
            $(dom_btn_submit).attr("disabled", "disabled");
            $.ajax({
                type: 'POST',
                url: $(form).data('href'),
                dataType: 'json',
                data: $(form).serialize(),
            }).done(function (res) {
                if (res.status == 0) {
                    $(dom_btn_submit).removeAttr('disabled');
                    messager.show({
                        type: 'alert',
                        content: res.msg,
                        autoClose: true
                    });
                    //$.alertModal(res.msg);
                } else {
                    window.location.reload();
                }
            });
        }
    });

    $("body").find("form[id*='account_edit']").each(function (i, o) {
        $(o).validate({
            // errorClass: "invalid",
            errorPlacement: function (error, element) {
                // element.parent().parent().parent().find('.tishinr').append(error);
                element.parent().next('.tishinr').html(error);
            },
            rules: {
                username: {
                    isusername: true,
                    required: true,
                    rangelength: [2, 10]
                },
                remark: {
                    rangelength: [0, 25]
                }
            },
            messages: {
                username: {
                    isusername: lang.only_num_word,
                    required: lang.user_not_empty,
                    rangelength: "账号必须为2到10个字符"
                },
                remark: {
                    rangelength: "请少于25个字符"
                }
            }
        });
    });

    // 弹出确认对话框
    //function alertConfirm(msg, ajax) {
    //    var obj = $('#confirm-modal');
    //    obj.modal('show');
    //    obj.find('.tips-msg').html(msg);
    //    //解除绑定事件
    //    $("#confirm_no").unbind('click');
    //    $("#confirm_yes").unbind('click');
    //
    //    $('#confirm_no').click(function(){
    //        obj.modal('hide');
    //    });
    //    $('#confirm_yes').click(function(){
    //        if(ajax && ajax instanceof Function){
    //            ajax();
    //        }
    //    });
    //}

    $(".del").on('click', function () {
        var able = $(this).data('able');
        if (!able) {
            messager.show({
                type: 'alert',
                content: '启用用户不允许删除',
                autoClose: true
            });
            //$.alertModal('启用用户不允许删除');
            return false;
        }
        var obj_r = $(this);
        // if(!alertConfirm("确认要删除？")){
        //     window.event.returnValue = false;
        // }else{

        messager.show({
            type: "confirm",
            title: "删除提示",
            content: "确认要删除?",
            buttonTitle: "删除"
        }, function (type) {
            if (type === 'submit') {
                var href = obj_r.data('href');
                var key = obj_r.data('key');
                $.ajax({
                    type: 'POST',
                    url: href,
                    dataType: 'json',
                }).done(function (res) {
                    if (res.status == 'ok') {
                        $(".key-" + key).remove();
                    }
                    messager.show({
                        type: 'alert',
                        content: res.msg,
                        autoClose: true
                    });
                });
            }
        })
    })
    $(".btn-default").on('click', function () {
        $('#form_add')[0].reset();
        $('.tishinr').text('');
    });
    //编辑
    $(".edit_f").on('click', function () {
        var target = $(this).data('target');
        $(target).find('form')[0].reset();
    });

    //$("#update_login_verify_code_button").click(function(){
    //    if($(this).text() == '修改'){
    //        console.log('修改');
    //        $("#login_verify_code").prop('hidden',true);
    //        $("#update_login_verify_code").attr('type','text');
    //        $(this).text('提交');
    //    }else if($(this).text() == '提交'){
    //        console.log('提交');
    //        $.ajax({
    //            url: $("#update_login_varify_code_url_ajax").val(),
    //            type: 'post',
    //            dataType: 'json',
    //            data: {
    //                uid: $("#hidden_user_guid_ajax").val(),
    //                vcode : $("#login_verify_code").attr('text')
    //            },
    //            success: function(data){
    //                if(data != ''){
    //                    $("#update_login_verify_code").attr('type','hidden');
    //                    $("#login_verify_code").prop('hidden',false);
    //                    $(this).text('修改');
    //                    $("#login_verify_code").text(data.code);
    //                    alert('修改成功了');
    //                }else{
    //                    alert('修改失败了');
    //                }
    //            }
    //        });
    //    }
    //});
    //登录验证码
});
