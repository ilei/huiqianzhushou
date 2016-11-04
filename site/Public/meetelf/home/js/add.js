define(['jquery', 'lang', 'messageControl', 'ckeditor', 'validate', 'pin', 'datepicker', 'MTFCropper', 'combobox'], function ($, lang, messager) {
    document.domain = $("#document_domain").val();

    var operation_type = 0;


    var editor = CKEDITOR.replace('ym_editor', {
        extraPlugins: 'uploadimage,image2',
        height: 400,
        customConfig: '/Public/meetelf/ckeditor/config.js',
    });
    //取消对表单按钮的禁用
    $(".act-save").removeAttr('disabled');
    $(".act-pub").removeAttr('disabled');

    //百度地图API功能
    var map = new BMap.Map("mapzoom");
    var point = new BMap.Point(117.217415, 39.145267);
    map.centerAndZoom(point, 15);
    map.enableScrollWheelZoom();
    //获取当前城市中心点
    function myFun(result) {
        var cityName = result.name;
        map.setCenter(cityName);
    }

    var myCity = new BMap.LocalCity();
    myCity.get(myFun);
    //显示经纬度
    map.addEventListener("click", function (e) {
        $("#modal-lat").val(e.point.lat);
        $("#modal-lng").val(e.point.lng);
    });

    var local = new BMap.LocalSearch(map, {
        renderOptions: {map: map}
    });
    local.setPageCapacity(15);
    $(".address-search").on('click', function () {
        var value = $("#keywords").val();
        local.search(value);
    });
    $(".address-save").on('click', function () {
        $('input[name=lng]').val($("#modal-lng").val());
        $('input[name=lat]').val($("#modal-lat").val());
        $("#mapModal").modal('hide');
    });

    $('[data-toggle="tooltip"]').tooltip()
    $('#mt_dtbox').DateTimePicker({
        beforeShow: function (element) {
            var obj = this;
            obj.settings.defaultDate = new Date();
        }
    });
    //联动菜单
    $("#act_form").delegate("select[name='area_1']", 'change', function () {
        linkage('area_1', 'area_2');
    });

    //联动函数
    function linkage(s, d) {
        var sobj = $("select[name='" + s + "']");
        var dobj = $("select[name='" + d + "']");
        $.ajax({
            type: 'POST',
            url: '/act/ajax_get_area',
            dataType: 'json',
            data: {id: sobj.val()},
        }).done(function (res) {
            if (res.status) {
                dobj.html('').html(res.msg);
            } else {
                dobj.html('<option value>市/区</option>');
                console.log(res.msg);
            }
        });
    }

    //选择海报
    $("#postersModal").delegate(".btn-save", 'click', function () {
        var img = $("#tabitem1").find(".active > img").attr('src');
        $(".upload-posters").attr('src', img);
        $("#poster").val(img);
        $(this).siblings(".btn-default").trigger('click');
    });

    //活动流程
    var index = $("#item-process").find(".row").length;

    function create_flow($obj) {
        $('#mt_dtbox').DateTimePicker({
            addEventHandlers: function () {
                var dtPickerObj = this;
                $('body').delegate('.datePicker', 'click', function (e) {
                    e.stopPropagation();
                    dtPickerObj.showDateTimePicker($(this));
                });
            },
            beforeShow: function (element) {
                var obj = this;
                obj.settings.defaultDate = new Date();
            }
        });
        var html = $("#flow-default").html();
        var obj = $(html);
        obj.find("input[name='start_time']").attr('name', "op_flow[" + index + "][start_time]");
        obj.find("input[name='end_time']").attr('name', "op_flow[" + index + "][end_time]");
        obj.find("input[name='title']").attr('name', "op_flow[" + index + "][title]");
        obj.find("#btn-del").attr('id', "btn-del-" + index);
        $($obj).parent().before(obj);
        index++;
    }

    $(".create-flow").on('click', function () {
        create_flow(this);
    });
    $("#item-process").delegate("button[id*='btn-del-']", 'click', function (e) {
        e.preventDefault();
        $(this).parent().parent().parent(".st-li-process-item").remove();
    });

    //票务

    var tindex = 1;

    function create_ticket() {
        $('#mt_dtbox').DateTimePicker({
            addEventHandlers: function () {
                var dtPickerObj = this;
                $('body').delegate('.datePicker', 'click', function (e) {
                    e.stopPropagation();
                    dtPickerObj.showDateTimePicker($(this));
                });
            },
            beforeShow: function (element) {
                var obj = this;
                obj.settings.defaultDate = new Date();
            }
        });
        var html = $("#ticket-tr").find("tbody").html();
        var obj = $(html);
        obj.find("input[name='name']").attr('name', "op_ticket[new][" + tindex + "][name]");
        obj.find("input[name='num']").attr('name', "op_ticket[new][" + tindex + "][num]");
        obj.find("input[name='is_need_verify']").attr('name', "op_ticket[new][" + tindex + "][is_need_verify]");
        obj.find("input[name='start_time']").attr('name', "op_ticket[new][" + tindex + "][start_time]");
        obj.find("input[name='end_time']").attr('name', "op_ticket[new][" + tindex + "][end_time]");
        obj.find("input[name='price']").attr('name', "op_ticket[new][" + tindex + "][price]");
        obj.find(".collapsed").attr('data-target', "#op_ticket_" + tindex);
        obj.find(".collapsed").attr('aria-controls', "op_ticket_" + tindex);
        obj.find(".target-class").attr('id', "op_ticket_" + tindex);
        obj.find("#ticket-btn-del").attr('id', 'ticket-btn-del-' + tindex);
        $("#ticket-hook").before(obj);
        tindex++;
    }

    $("body").delegate("#add-free-ticket", 'click', function () {
        create_ticket();
    });
    $("body").delegate("button[id*='ticket-btn-del-']", 'click', function () {


        var ticket_count = $(this).parents("table").find(".ticket-tr-for").length;
        if (ticket_count <= 1) {

            messager.show({
                type: "alert",
                content: "票不能少于1张",
                autoClose: true
            });

            //$.alertModal("票不能少于1张");
            ////不能小于一张票  考虑是否提示
            console.log("票据不能小于1");
            return;
        }

        $(this).parent().parent("tr").remove();
        var target = $(this).siblings("button").data('target');
        $(target).parents(".table-collapse").remove();
    });

    //主办方
    var uindex = 2;

    function create_undertaker() {
        var html = $("#undertaker-default").html();
        var obj = $(html);
        obj.find("input[name='name']").attr('name', "op_undertaker[" + uindex + "][name]");
        obj.find("#undertaker-del").attr('id', "undertaker-del-" + uindex);
        obj.find('.add-combobox').combobox({
            name: 'op_undertaker[' + uindex + '][type]',
            guid: 'op_undertaker[' + uindex + '][guid]'
        });
        uindex++;
        $("#undertaker-add-button").parent().before(obj);
    }

    $("body").delegate("#undertaker-add-button", 'click', function () {
        create_undertaker();
    });
    $("body").delegate("button[id*='undertaker-del-']", 'click', function () {
        $(this).parent().parent().parent(".st-li-process-item").remove();
    });

    //表单验证
    $.validator.addMethod("startafternow", function (value, element) {
        var start_time = new Date($('#start_time').val().replace(/-/g, "/")).getTime();
        var now = new Date().getTime();
        return start_time + 60 * 1000 > now;
    }, $.lang.start_not_lt_now);

    $.validator.addMethod("endafternow", function (value, element) {
        var end_time = new Date($('#end_time').val().replace(/-/g, "/")).getTime();
        var now = new Date().getTime();
        return end_time + 60 * 1000 > now;
    }, $.lang.end_not_lt_now);


    $.validator.addMethod("afterstart", function (value, element) {
        if ($('#start_time').val() == '' || $('#end_time').val() == '') {
            return true;
        }
        var start_time = new Date($('#start_time').val().replace(/-/g, "/")).getTime();
        var end_time = new Date($('#end_time').val().replace(/-/g, "/")).getTime();

        return end_time > start_time;
    }, $.lang.start_not_lt_end);

    //验证票务信息
    $(".mtelf-table-border").delegate("input[name*='name']", 'keyup', function () {
        $("#ticket-error").html('');

        var elements=$(".mtelf-table-border input[name*='name']");

        for (var i=0;i<elements.length;i++){
              var element=$(elements[i]);

            if (!$(element).val()) {
                $(element).focus();
                $("#ticket-error").html('').html($.lang.ticket_name_not_empty);
                return false;
            }
            if ($(element).val().length > 10) {
                $(element).focus();
                $("#ticket-error").html('').html($.lang.ticket_name_len_error);
                return false;
            }
        }

    });
    $(".mtelf-table-border").delegate("input[name*='[num']", 'keyup', function () {
        $("#ticket-error").html('');


        var elements=$(".mtelf-table-border input[name*='num']");

        for (var i=0;i<elements.length;i++) {
            var element = $(elements[i]);
            if (!$(element).val()) {
                $(element).focus();
                $("#ticket-error").html('').html($.lang.ticket_num_not_empty);
                return false;
            }
            if (parseInt($(element).val()) != $(element).val()) {
                $(element).focus();
                $("#ticket-error").html('').html($.lang.ticket_num_format_err);
                return false;
            }
        }

    });
    $(".mtelf-table-border").delegate("input[name*='[price']", 'keyup', function () {
        $("#ticket-error").html('');
        if ($(this).val() && parseInt($(this).val()) != $(this).val()) {
            $(this).focus();
            $("#ticket-error").html('').html($.lang.ticket_money_format_err);
            return false;
        }
    })
    $(".mtelf-table-border").delegate("input[name*='time']", 'change', function () {
        $("#ticket-error").html('');
    });
    function ticket_time() {
        var res = true;
        $(".mtelf-table-border").find("div[id*='collapseExample']").each(function (i, o) {

            var start = new Date($('#start_time').val().replace(/-/g, "/")).getTime();
            var end = new Date($('#end_time').val().replace(/-/g, "/")).getTime();
            var start_obj = $(o).find("input[name*='start_time']");
            var start_time = new Date($(start_obj).val().replace(/-/g, "/")).getTime();
            var end_obj = $(o).find("input[name*='end_time']");
            var end_time = new Date($(end_obj).val().replace(/-/g, "/")).getTime();
            var now = new Date().getTime();
            if (start_time) {
                if (start_time < now) {
                    $(o).collapse('show');
                    $("#ticket-error").html('').html($.lang.ticket_start_lt_now);
                    $(start_obj).focus();
                    res = false;

                } else if (start_time > end) {
                    $(o).collapse('show');
                    $("#ticket-error").html('').html($.lang.ticket_start_rt_aend);
                    $(start_obj).focus();
                    res = false;
                } else if (end_time && start_time > end_time) {
                    $(o).collapse('show');
                    $("#ticket-error").html('').html($.lang.ticket_start_rt_end);
                    $(start_obj).focus();
                    res = false;
                }
            }
            if (end_time && end_time > end) {
                $(o).collapse('show');
                $("#ticket-error").html('').html($.lang.ticket_end_rt_end);
                $(end_obj).focus();
                res = false;
            }
        });
        return res;
    }

    //检测票的名称
    function ticket_name() {
        var res = true;
        $("#ticket-error").html('');
        $(".mtelf-table-border").find(".ticket-tr-for").each(function (i, o) {

            if (!$(o).find("input[name*='name']").val()) {
                $($(o).find("input[name*='name']")).focus();
                $("#ticket-error").html('').html($.lang.ticket_name_not_empty);
                res = false;
            }
            if ($(o).val().length > 10) {
                $(o).focus();
                $("#ticket-error").html('').html($.lang.ticket_name_len_error);
                return false;
            }
        });
        return res;
    }

    $("#item-process").delegate("input", 'change', function () {
        $("#item-process-error").html('');
    })
    function flow_time() {
        var res = true;
        $("#item-process-error").html('');
        var start = new Date($('#start_time').val().replace(/-/g, "/")).getTime();
        var end = new Date($('#end_time').val().replace(/-/g, "/")).getTime();
        var max = null;
        $("body").find('#item-process').find(".row").each(function (i, o) {
            var title = $(o).find("input[name*='title']").val();
            if (title) {
                var start_obj = $(o).find("input[name*='start_time']");
                var start_time = new Date($(start_obj).val().replace(/-/g, "/")).getTime();
                var end_obj = $(o).find("input[name*='end_time']");
                var end_time = new Date($(end_obj).val().replace(/-/g, "/")).getTime();
                if (!start_time) {
                    $("#item-process-error").html('').html($.lang.flow_start_not_empty);
                    $(start_obj).focus();
                    res = false;
                } else if (!end_time) {
                    $("#item-process-error").html('').html($.lang.flow_end_not_empty);
                    $(end_obj).focus();
                    res = false;
                } else if (start_time < start) {
                    $("#item-process-error").html('').html($.lang.flow_start_rt_activity);
                    $(start_obj).focus();
                    res = false;
                } else if (end_time > end) {
                    $("#item-process-error").html('').html($.lang.flow_end_lt_activity);
                    $(end_obj).focus();
                    res = false;
                } else if (start_time > end_time) {
                    $("#item-process-error").html('').html($.lang.flow_start_lt_end);
                    $(end_obj).focus();
                    res = false;
                } else if (max && start_time < max) {
                    $("#item-process-error").html('').html($.lang.flow_time_not_combine);
                    $(start_obj).focus();
                    res = false;
                }
                max = end_time;
            }
        });
        return res;
    }

    //检测票的数目
    function ticket_num() {
        var res = true;
        $("#ticket-error").html('');
        $(".mtelf-table-border").find(".ticket-tr-for").each(function (i, o) {
            var obj = $(o).find("input[name*='[num']");
            var num = obj.val();
            var obj_1 = $(o).find("input[name*='[price']");
            var num_2 = obj.val();

            if (!num) {
                $(obj).focus();
                $("#ticket-error").html('').html($.lang.ticket_num_not_empty);
                res = false;
            }
            if (parseInt(num) != num) {
                $(obj).focus();
                $("#ticket-error").html('').html($.lang.ticket_num_format_err);
                res = false;
            }
            if (num_2 && parseInt(num_2) != num_2) {
                $(obj).focus();
                $("#ticket-error").html('').html($.lang.ticket_money_format_err);
                res = false;
            }
        });
        return res;
    }


    $("body").delegate(".act-save, .act-pub, .act-preview", 'click', function () {
        $("#act-status").val($(this).data('status'));
    });

    $('.act-save').on('click', function () {
        $("#status_post").val(0);
        if ($(this).hasClass('preview-save')) {

            operation_type = 1;
        } else {
            operation_type = 0;

        }
    });

    $('.act-pub').on('click', function () {
        $("#status_post").val(1);
        operation_type = 0;
    });

    //$('.act-save .act-pub').on('click', function () {
    //
    //    if($(this).hasClass(''))
    //
    //    $("#status_post").val(0);
    //    operation_type=0;
    //});
    //$('.btn-preview-sm').on('click', function () {
    //    $("#status_post").val(0);
    //    operation_type=1;
    //});
    //$('.btn-release-sm').on('click',function(){
    //    $("#status_post").val(1);
    //    operation_type=0;
    //});


    //$("#act_form").on('submit', function (e) {
    //
    //});
    $(".act-save .act-pub").on("click", function () {

        //console.log($(this).hasClass('preview-save'));
        //
        //return;
        //if($(this).hasClass('preview-save')){
        //    $("#status_post").val(1);
        //}else{
        //    $("#status_post").val(0);
        //}

        console.log("小菊花妈妈课堂开课啦");
        //禁用按钮
        $(".act-save .act-pub").attr('disabled', 'disabled');
        $("#act_form").submit();
    });


    //表单验证
    $("#act_form").validate({
        errorPlacement: function (error, element) {
            if (error) {
                element.attr('class', 'form-control');
                var name = $(element).attr('name');
                if (name == 'area_1' || name == 'area_2') {
                    element.parents(".row").find("#select-error").html($(error).html());
                    element.parents(".row").find("#address-error").html('');
                } else if (name == 'address') {
                    element.parents(".row").find("#select-error").html('');
                    element.parents(".row").find("#address-error").html($(error).html());
                } else {
                    element.parents(".row").siblings("p").html($(error).html());
                }
                $(".act-save .act-pub .act-review").removeAttr('disabled');

            }
        },
        success: function (element) {
            element.parents(".row").siblings("p").html('');
        },
        rules: {
            title: {
                required: true,
                rangelength: [6, 50],
            },
            start_time: {
                required: true,
                afterstart: true,
            },
            end_time: {
                required: true,
                endafternow: true,
                afterstart: true,
            },
            area_1: {
                required: true,
            },
            area_2: {
                required: true,
            },
            address: {
                required: true,
            },
        },
        messages: {
            title: {
                required: $.lang.title_not_empty,
                rangelength: $.lang.title_len_error,
                remote: $.lang.title_ban_error,
            },
            start_time: {
                required: $.lang.start_time_not_empty,
                date: $.lang.time_format_err,
            },
            end_time: {
                required: $.lang.end_time_not_empty,
                date: $.lang.time_format_err,
            },
            area_1: {
                required: $.lang.address_not_empty,
            },
            area_2: {
                required: $.lang.address_not_empty,
            },
            address: {
                required: $.lang.address_not_empty,
            },
        },
        submitHandler: function (form) {
            //e.preventDefault()
            //禁用按钮
            $(".act-save .act-pub ").attr('disabled', 'disabled');
            var value = CKEDITOR.instances.ym_editor.getData();
            if (value < 1 || value > 10000) {
                CKEDITOR.instances.ym_editor.focus();
                $("#ueditor-error").html($.lang.content_len_error);
                $(".act-save, .act-pub").attr('disabled', false);
                return false;
            } else {
                $("#ueditor-error").html('');
            }
            if (!ticket_name() || !ticket_num()) {
                $(".act-save, .act-pub").attr('disabled', false);
                return false;
            }
            if (!flow_time() || !ticket_time()) {
                $(".act-save, .act-pub").attr('disabled', false);
                return false;
            }
            if (!$("input[name='op_undertaker[0][name]']").val()) {
                $(".act-save, .act-pub").attr('disabled', false);
                $("#undertaker-error").html($.lang.undertaker_name_not_empty);
                return false;
            } else {
                $("#undertaker-error").html('');
            }
            if (!$("input[name='lng']").val() || !$("input[name='lat']").val()) {
                //$.alertModal('请进行地图定位');
                messager.show({
                    type: "alert",
                    content: "请进行地图定位",
                    autoClose: true
                }, function () {
                    $(".act-save, .act-pub").attr('disabled', false);
                    $("input[name='lng']").siblings("a").trigger('click');
                });
                return false;
            }
            if ($("#status_post").val() == 1) {
                //if(!confirm('您确定发布活动吗？发布后该活动就不能编辑了！')){
                //  return false;
                //}
                messager.show({
                    type: "confirm",
                    title: "发布确认",
                    content: "您确定发布活动吗？发布后该活动就不能编辑了！",
                    buttonTitle: "发布"
                }, function (type) {
                    if (type === "submit") {
                        realSubmit();
                    }
                });

            } else {
                realSubmit();
            }

            function realSubmit() {
                editor.updateElement();
                $.ajax({
                    type: 'POST',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: $(form).serialize(),
                    success: function (res) {

                        //去掉按钮禁用
                        $(".act-save  .act-pub").removeAttr('disabled');

                        if (res.status) {
                            //if (res.status_post == 1) {
                            //    window.location.href = res.preview_url;
                            //} else {
                            //    window.location.href = res.url;
                            //}
                            if (operation_type == 1) {
                                window.location.href = res.preview_url;
                            } else {
                                window.location.href = res.url;
                            }


                        } else {
                            $(".btn-block").attr('disabled', false);
                            //$.alertModal(res.msg);
                            messager.show({
                                type: "alert",
                                content: res.msg,
                                autoClose: true
                            });
                        }
                    },
                    error: function () {
                        $(".act-save .act-pub").removeAttr('disabled');
                    }
                });
            }
        }
    });
    var checked_undertaker = [], checked_undertaker_guid = [];
    $("#undertaker-all").on('click', function () {
        var checked = $(this).prop('checked');
        $("#zhubanModal").find("input[name*='checkbox-']").prop('checked', checked);
        $("#zhubanModal").find("input[name*='checkbox-']").each(function (i, o) {
            if (checked) {
                checked_undertaker[i] = $(o).data('value');
                checked_undertaker_guid[i] = $(o).data('guid');
            } else {
                checked_undertaker = [];
                checked_undertaker_guid = [];
            }
        })
    });
    $("#zhubanModal").find("input[name*='checkbox-']").on('click', function () {
        if ($(this).prop('checked')) {
            checked_undertaker[$(this).data('key')] = $(this).data('value');
            checked_undertaker_guid[$(this).data('key')] = $(this).data('guid');
        } else {
            delete checked_undertaker[$(this).data('key')];
            delete checked_undertaker_guid[$(this).data('key')];
        }
    });
    var save = false;
    $("#zhubanModal").delegate(".btn-save", 'click', function () {
        save = true;
        $(this).siblings(".btn-default").trigger('click');
    });
    $("#zhubanModal").on('hide.bs.modal', function () {
        if (checked_undertaker && save) {
            var tmp = [], tmp_1 = [];
            var j = 0;
            for (var i = 0; i < checked_undertaker.length; i++) {
                if (checked_undertaker[i]) {
                    tmp[j] = checked_undertaker[i];
                    tmp_1[j] = checked_undertaker_guid[i];
                    j++;
                }
            }
            $("#undertaker-master").val(tmp.join(','));
            $("#zhubanfang_input").val(tmp_1.join(','));
        }
    }).on('show.bs.modal', function () {
        save = false;
    });
    $("#mapModal").on('show.bs.modal', function () {
        var str = '';
        if ($("select[name='area_1']").val()) {
            str += $("select[name='area_1']").find("option:selected").text();
        }
        if ($("select[name='area_2']").val()) {
            str += $("select[name='area_2']").find("option:selected").text();
        }
        str += $("input[name='address']").val();
        $("#keywords").val(str);
    })
    $('.combobox').combobox({name: 'op_undertaker[1][type]', guid: 'op_undertaker[1][guid]'});

    function upload_callback(data) {
        $("#poster").val(data.data.val);
        $("#defaultPoster").attr('src', data.data.path);
    }

    $(".edit-row").find("select").on('change', function () {
        $(this).siblings("input").val($(this).find("option:selected").data("value"));
    });
    $("#upload-poster-modal").mtfImgUpload('activity_poster', 'defaultPoster', '540,324', {
        aspectRatio: 5 / 3,
        minContainerHeight: 300,
        minContainerWidth: 400
    }, upload_callback);

    $("#act_form").find("input[name='title']").blur(function () {
        var words = $(this).val();
        if (words) {
            $.ajax({
                type: 'POST',
                url: $("#censor_words_url").val(),
                dataType: 'json',
                data: {words: words},
            }).done(function (res) {
                if (!res.status) {
                    //$.alertModal(res.msg);
                    messager.show({
                        type: "alert",
                        content: res.msg,
                        autoClose: true
                    });
                }
            });
        }
    });

    //报名收集项效果
    $("#signin-form").delegate(".btn-option", 'click', function (e) {
        e.stopPropagation();//阻止冒泡

        var cb_count = $(this).find(":checkbox").length;

        if (cb_count === 0) {
            //不是可选按钮
            return;
        } else if (cb_count === 1) {

            //是否为必选
            var required_oldValue = $(this).children().prop("checked");//获取变化前的选中情况
            if (required_oldValue) {
                //之前是被选中的
                $(this).removeClass("active");
            } else {
                //之前未选中
                $(this).addClass("active");


                ////需要考虑外层
                //var required_parentOldValue = $(this).siblings(":checkbox").prop("checked");//获取父级当前的状态
                //if (!required_parentOldValue) {
                //外层未选中
                $(this).parent().addClass('active');//选中外层
                $(this).siblings(":checkbox").prop("checked", true);//选中外层
                //}
            }

            $(this).children(":checkbox").prop("checked", !required_oldValue);//重设当前选中值


        } else if (cb_count === 2) {
            //种类
            var type_oldValue = $(this).children(":checkbox").prop("checked");//获取变化前的选中状况
            if (type_oldValue) {
                //之前是被选中的
                $(this).removeClass("active");

                //var type_childOldValue=$(e).children("span").children().prop("checked");
                //if(type_childOldValue){
                //子选项被选中
                $(this).children("span").removeClass('active');//取消内层选中
                $(this).children("span").children().prop("checked", false);//取消内层选中
                //}

            } else {
                //之前未选中
                $(this).addClass('active');
            }

            $(this).children(":checkbox").prop("checked", !type_oldValue);
        }

    });


});

