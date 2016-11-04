define(['jquery', 'homeCommon'], function($){
    $("body").find("input[type='checkbox']").attr('checked', false);
    var set_account_url = $("#set_account_url").val();
    var money = new Array;
    var ck_act_guids = new Array;

    //var ck_all_status = $("#ck_all").prop('checked');
    //加载更多刷新
    $("#ajax_get_act").click(function(){
        $.ajax({
            url: $("#get_not_settle_act_url").val()
        });
    });
    /**
     * 待结算页面活动结算金额统计多选
     */
    $(".ct").click( function (){
        money = [];
        ck_act_guids = [];
        if ($(".ct").prop("checked") == true) {
            $(".ct").prop("checked",true);
            $("#sum_money").text(0);
            var i=0;
            $(".ct1").each(function (){
                $(this).prop("checked",true);
                money[i] = $(this).parent().parent().parent().parent().find($("span[name='one_money']")).text();
                ck_act_guids[i] = $(this).parent().find("input[name='act_guid']").val();
                i++;
            })
            var sum = 0;
            for(var j = 0;j < money.length;j++){
                sum += parseFloat(money[j])*100;
            }
            $("#ck_act_guids").attr('value',ck_act_guids);
            $("#sum_money").text(sum/100);
        }
        else {
            $(".ct").prop("checked",false);
            $(".ct1").each(function (){
                $(this).prop("checked",false)
            })
            $("#ck_act_guids").attr('value','');
            $("#sum_money").text(0);
        }
    });


    //单选
    $(".ct1").click(function(){
        if ($(this).prop("checked") == true) {
            $(this).prop("checked",true);
            money = $(this).parent().parent().parent().parent().find($("span[name='one_money']")).text();
            ck_act_guids[ck_act_guids.length] = $(this).parent().find("input[name='act_guid']").val();
            var sum = $("#sum_money").text();

            sum = parseFloat(money)*100 + parseFloat(sum)*100;
            $("#ck_act_guids").attr('value',ck_act_guids);
            $("#sum_money").text(sum/100);
        }else {
            $(this).prop("checked",false);
            money = $(this).parent().parent().parent().parent().find($("span[name='one_money']")).text();
            var ckguid = $(this).parent().find("input[name='act_guid']").val();
            var sum = $("#sum_money").text();
            console.log(sum);
            console.log(money);
            sum = parseFloat(sum)*100 - parseFloat(money)*100;
            ck_act_guids.splice(ck_act_guids.indexOf(ckguid),1);
            $("#ck_act_guids").attr('value',ck_act_guids);
            if(sum <= 0){
                $("#sum_money").text(0);
            }else{
                $("#sum_money").text(parseInt(sum)/100);
            }
        }
    });

    //去结算页面数据提交
    $("#go_set_account").click(function(e){
        e.preventDefault();
        if(ck_act_guids == ''){
            alert('没有结算的活动');
            return false;
        }else{
            $("#hidden_form").submit();
        }
    });


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

