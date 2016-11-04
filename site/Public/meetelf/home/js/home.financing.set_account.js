define(['jquery','validate','messageControl', 'homeCommon'], function($,_,message){

    var bank_info = $("#bank_info").val();
    var express_info = $("#express_info").val();
    var invoice_info = $("#invoice_info").val();
    var is_invoice = $('#radio_hidden').val();
    var $dn = $(".sub-info-content .row .moren");

    $(".hide_info").css("display","block");

    $(".radio_yes").click(function(){
        if(express_info == '' || invoice_info == ''){
            message.show({
                type: 'alert',
                title:"操作确认",
                content:"<b>发票信息、快递信息不全，请去账户管理补全信息。</b>",
                autoClose: false
            });
            location.href = $('#Financing_info_set').val();
            window.onbeforeunload = false;
            return false;
            $('.radio_no input').prop('checked',true);
            }
    });


    //判断发票和快递信息是否完备
    if(bank_info == ''){
        message.show({
            type:"confirm",
            title:"操作确认",
            content:"<b>银行信息不全，请去补全信息。如果不补全，则不能结算。</b>",
            //buttonTitle:"认证"
        }, function (type) {
            if(type==="submit"){
                location.href = $('#Financing_info_set').val();
                window.onbeforeunload = false;
                return false;
            }else{
                $(".hide_info").css("display","none");
                $('.radio_no input').prop('checked',true);
                $(".sub-info-content .row .moren").css("display", "none");
            }
        });
    }else{
        if(express_info == '' || invoice_info == ''){
            message.show({
                type:"confirm",
                title:"操作确认",
                content:"<b>发票信息、快递信息不全，是否去补全信息？如果不补全，则不能开取发票。</b>",
                //buttonTitle:"认证"
            }, function (type) {
                if(type==="submit"){
                    location.href = $('#Financing_info_set').val();
                    window.onbeforeunload = false;
                    return false;
                }else{
                    $(".hide_info").css("display","none");
                    $('.radio_no input').prop('checked',true);
                    $(".sub-info-content .row .moren").css("display", "none");
                }
            });
        }
    }

    $(".radio_yes input").click(function () {
        $(".hide_info").css("display","block");


        if ($(this).prop("checked")) {
            $("#radio_hidden").attr('value',$(this).val());
            $dn.each(function () {
                $(this).css("display", "block");
            })
        }
    });
    $(".radio_no input").click(function () {
        $(".hide_info").css("display","none");

        $(".modal .hide_info").css("display", "none");
        if ($(this).prop("checked")) {
            $("#radio_hidden").attr('value',$(this).val());
            $dn.each(function () {
                $(this).css("display", "none");
            })
        }
    });


    $("#add_submit").click(function(){
        window.onbeforeunload = false;
        if(bank_info == ''){
            message.show({
                type: 'alert',
                title:"操作确认",
                content:"<b>银行信息不能为空</b>",
                autoClose: false
            });
        }else{
            $("#hidden_form").submit();
        }
    });


    //页面刷新控制
    window.onbeforeunload = function(){
        window.event.returnValue='此页面为结算页面，';
    }


    //javascript:window.history.forward(1);

    //all_choice.js
    $(function () {
        $input_r = $(".check_s");
        $input_s = $(".check")
        $input_r.click(function () {
            if ($input_r.prop("checked") == true) {
                $input_s.each(function () {
                    $(this).prop("checked", true)
                })
            }
            else {
                $input_s.each(function () {
                    $(this).prop("checked", false)
                })
            }
        });
        $(".form-con").change(function () {
            $t = $(this).val();
            if ($t == "vip1") {
                $(".table_data").remove();
                $(".bottom_element").remove();
                $(".main_head").after($select_1);
            }
            else if ($t == "vip2") {
                $(".table_data").remove();
                $(".bottom_element").remove();
                $(".main_head").after($select_1);
            }
        });

        var haha = '<div class="form-group col-sm-12 add-form-template">' +
            '<div class="col-sm-2 sign">' +
            '<label>name00</label>' +
            '</div>' +
            '<div class="col-sm-7">' +
            '<input type="text" class="form-control" placeholder="text">' +
            '</div>' +
            '<div class="col-sm-2">' +
            '<span class="circle-delete"><i class="fa fa-minus"></i></span>' +
            '</div>' +
            '</div>';
        $(".add-form").click(function () {
            $a = $(this).parent();
            $a.before(haha);
        });

        $("body").on("click", ".circle-delete", function () {
            $(this).parent().parent().remove();
        });

        $("#or1").change(function () {
            if ($(this).prop("checked") == true) {
                $(".sub_check").css("display", "block");
            }
        });
        $("#or2").change(function () {
            if ($(this).prop("checked") == true) {
                $(".sub_check").css("display", "none");
            }
        });
        $("#addModa2").click(function () {
            console.log("fuck");
            $(".ul_data_list ul").append($add_li);

        });

        $(".div_hover").hover(function () {
            $(this).find(".edit").css("display", "inline-block");
            $(this).find(".edit-left").css("display", "block");
        }, function () {
            $(this).find(".edit").css("display", "none");
            $(this).find(".edit-left").css("display", "none");
        });



    });
});

