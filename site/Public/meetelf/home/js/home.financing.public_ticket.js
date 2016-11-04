define(['jquery', 'homeCommon'], function($){

    var ticket_guid = 'all';//当前选择的票名guid
    var ticket_status = 'all';//当前选择的票名guid
    var public_ticket_url = $("#public_ticket_url").val();

    //下拉筛选
    $("a[name='li_guid']").click(function(){
        ticket_guid = $(this).attr('ticket_guid');
        console.log(ticket_guid);
        $.ajax({
            type: 'post',
            url: public_ticket_url,
            data: {
                ticket_guid:ticket_guid,
                ticket_status:ticket_status
            },
            dataType: 'json',
            success: function(data){
                if(data != ''){
                    console.log(data.sum_money);
                    $("#sum_money_ajax").text(data.sum_money);
                    $("#ticket_name_ajax").text(data.ticket_name);
                    $("#ticket_tbody").html(data.tbody);
                    console.log('dasdasdsadas');
                }
            }
        });
    });
    $("a[name='li_status']").click(function(){
        ticket_status = $(this).attr('ticket_status');
        //console.log(ticket_status);
        $.ajax({
            type: 'post',
            url: public_ticket_url,
            data: {
                ticket_guid:ticket_guid,
                ticket_status:ticket_status
            },
            dataType: 'json',
            success: function(data){
                if(data != ''){
                    $("#sum_money_ajax").text(data.sum_money);
                    $("#ticket_status_ajax").text(data.t_status_name);
                    $("#ticket_tbody").html(data.tbody);
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

