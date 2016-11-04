define(['jquery', 'lang', 'messageControl', 'validate', 'MTFCropper'], function ($, lang, messager) {
    var dom_register_form = $("#form_add");//Form表单
    var dom_btn_submit = $("#save");//提交按钮
    //validate自定义验证手机号
    $.validator.addMethod("isMobile", function (value, element) {
        var length = value.length;
        return this.optional(element) || length == 11 && /^1[358]\d{9}$/.test(value);
    }, lang.mobile_format_err);

    //注册按钮单击事件，手动触发form提交
    dom_btn_submit.click(function () {

        //提交form
        dom_register_form.submit();
    });

    $(dom_register_form).validate({
        // errorClass: "invalid",
        errorPlacement: function (error, element) {
            element.parent().next('.tishinr').html(error);
        },
        rules: {
            account: {
                required: true,
                rangelength: [2, 25],
            },
            mobile: {
                required: true,
                isMobile: true,
            },
            desc: {
                rangelength: [0, 50],
            }
        },
        messages: {
            account: {
                required: $.lang.accout_empry,
                rangelength: $.lang.account_num,
            },
            mobile: {
                required: $.lang.mobile_not_empty,
            },
            desc: {
                rangelength: $.lang.desc_num,
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
                    //$.alertModal('主办方已存在，请修改后添加');
                    messager.show({
                        type: "alert",
                        content: "主办方已存在，请修改后添加",
                        autoClose: true
                    });
                } else {
                    window.location.reload();
                }
            });
        }
    });

    $("body").find("form[id*='edit_form']").each(function (i, o) {
        $(o).validate({
            // errorClass: "invalid",
            errorPlacement: function (error, element) {
                element.parent().next('.tishinr').html(error);
            },
            rules: {
                account: {
                    required: true,
                    rangelength: [2, 25],
                },
                mobile: {
                    required: true,
                    isMobile: true,
                },
                desc: {
                    rangelength: [0, 50],
                }
            },
            messages: {
                account: {
                    required: $.lang.accout_empry,
                    rangelength: $.lang.account_num,
                },
                mobile: {
                    required: $.lang.mobile_not_empty,
                },
                desc: {
                    rangelength: $.lang.desc_num,
                }
            }
        });
    });

    $(".del").on('click', function () {
        var obj_r = $(this);

        messager.show({
            type: "confirm",
            title: "删除提示",
            content: "确认要删除?",
            buttonTitle: "删除",

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
                    //alert(res.msg);
                });
            }
        });

    });
    $(".btn-default").on('click', function () {
        $('#form_add')[0].reset();
        $('.tishinr').text('');
        $(".imgpro").attr('src', '/Public/common/images/noportrait.png');
    });
    //编辑
    var img = null;
    $(".edit_f").on('click', function () {
        var target = $(this).data('target');
        $(target).find('form')[0].reset();
        img = $(this).data('id');
    });
    $("#user_photoV").mtfImgUpload('avatar', 'ImgPr', '120,120', {
        aspectRatio: 1 / 1,
        minContainerHeight: 200,
        minContainerWidth: 300
    }, upload_callback);
    var account = '';
    var mobile = '';
    var desc = '';
    //获得输入信息
    $("#account").change(function () {
        account = $("#account").val();
    });
    $("#mobile").change(function () {
        mobile = $("#mobile").val();
    });
    $("#desc").change(function () {
        desc = $("#desc").val();
    });
    // var mobile = $("#mobile").val();
    function upload_callback(data) {

        //隐藏input id
        $("#poster").val(data.data.val);
        //赋值给input
        $("#account").attr('value', account);
        $("#mobile").attr('value', mobile);
        $("#desc").attr('value', desc);
        $("#" + img).val(data.data.val);

        $(".imgpro").attr('src', data.data.path);
    }
});
