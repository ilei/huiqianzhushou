define(['jquery','validate', 'homeCommon'], function($){

    var url = $("#underway_history_ticket_url").val();
    var goods_name = '全部';//票名
    var ticket_status_name = '全部';//票状态

    $("a[name='li_guid']").click(function(){
        goods_name = $(this).text();
        console.log(goods_name);
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
                goods_name: goods_name,
                ticket_status_name: ticket_status_name
            },
            success: function(data){
                if(data != ''){
                    $("#sum_money").text(data['sum_money']);
                    $("#ticket_tbody").html(data['tbody']);
                    $("#ticket_name").html(data['ticket_name']);
                    $("#ticket_status").html(data['ticket_status']);
                }
            }
        });
    });

    $("a[name='li_status']").click(function(){
        ticket_status_name = $(this).text();
        console.log(ticket_status_name);
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
                goods_name: goods_name,
                ticket_status_name: ticket_status_name
            },
            success: function(data){
                if(data != ''){
                    $("#sum_money").text(data['sum_money']);
                    $("#ticket_tbody").html(data['tbody']);
                    $("#ticket_name").html(data['ticket_name']);
                    $("#ticket_status").html(data['ticket_status']);
                }
            }
        });
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

