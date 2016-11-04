define(['jquery', 'langs', 'validate','homeCommon'], function($){
    var code = '';//校验码填写栏
    var verify = '';//验证码填写栏
    var checkcode_tishinr = '';//校验码验证提示栏
    var verify_tishinr = '';//验证码验证提示栏
	$(function(){

        //表单验证
        $("#MobileForm").validate({
            errorPlacement: function(error, element){
                if(error){
                    element.parents().parents().prev('.tishinr').append(error);
                }
            },
            rules: {
                checkcode: {
                    required: true,
                    rangelength: [6, 6],
                    remote: {
                        type: "POST",
                        url: $("#check_mobile_code_url").val(),
                        dataType: "json",
                        data: {
                            mobile: function(){ return $('#mobile').val()},
                            code: function(){ return $("#checkcode").val()}
                        }
                    }
                },
                verify: {
                    required: true,
                    rangelength: [4, 4],
                    remote: {
                        url: $("#verify_url").val(),
                        type: "post",
                        data: {
                            verify: function(){
                                return $("#verify").val()
                            }
                        }
                    }
                }
            },
            messages: {
                checkcode: {
                    required: $.lang.mobile_verify_not_empty,
                    rangelength: $.lang.mobile_verify_format_err,
                    remote: $.lang.click_code_error
                },
                verify: {
                    required: $.lang.verify_not_empty,
                    rangelength: $.lang.verify_format_not,
                    remote: $.lang.verify_error
                }
            },
            success: function(data){
                code = $("#checkcode").val();
                verify = $("#verify").val();
                // username = $("#username").val();
                // username_tishinr = $("#username-error").text();
                checkcode_tishinr = $("#checkcode-error").text();
                verify_tishinr = $("#verify-error").text();

                // if(reg.test(username)){
                //     is_email = '1';
                //     $("#send_verify_code").text(lang.send_email);//改变按钮文字内容
                // }else{
                //     is_email = '0';
                //     $("#send_verify_code").text(lang.send_code);//改变按钮文字内容
                // }

                var form_action = $("#MobileForm").attr('action');
                $('#MobileForm').attr('action',form_action);

                //is_true_text = $("#username-error").text();
                //if(is_true_text == ''){
                //    is_true_status = '1';
                //}else{
                //    is_true_status = '0';
                //}

                //判断是邮箱的话则按钮可以点击
                // if(username != '' && username_tishinr == ''){
                //     $("#send_verify_code").removeAttr("disabled");
                // }else{
                //     $("#send_verify_code").attr("disabled","disabled");
                // }

                // //判断是否是邮箱是邮箱的话，则隐藏其他栏
                // if(is_email == '1' && username_tishinr == ''){
                //     $(".email_hidden").hide();
                //     $("#send_code_div").attr('class','col-xs-12');
                // }else{
                //     $("#send_code_div").attr('class','col-xs-8');
                //     $(".email_hidden").show();
                // }

                if(code != '' && verify != '' &&  checkcode_tishinr == '' && verify_tishinr == ''){
                    $("#next_submit").removeAttr('disabled');
                }else{
                    $("#next_submit").attr('disabled','disabled');
                }
            }
        });
    });

$("#verifyimg").click(function(){
        refresh_verify();
    });

// });
//刷新验证码
function refresh_verify(){
    var verifyimg = $(".verifyimg").attr("src");
    if( verifyimg.indexOf('?')>0){
        $(".verifyimg").attr("src", verifyimg+'&random='+Math.random());
    }else{
        $(".verifyimg").attr("src", verifyimg.replace(/\?.*$/,'')+'?'+Math.random());
    }
}
$("#click_verify").click(function(){
    refresh_verify();
});

//异步发送校验码
    $("#send_verify_code").click(function(){
    	var mobile = $("#mobile").val();
        time($(this));
        $.ajax({
            type: "post",
            url: $("#send_button_url").val(),
            dataType: "json",
            data:{mobile:mobile},
            success: function (data) {
                if(data.status==0){
                	// $(".click_text").css("display", "block");
                	$(".click_text").hide();
                	$.alertModal(data.msg);
                }
            }
        });
    });
//按钮倒计时
    var wait = 120;
    function time(o) {
        if (wait == 0) {
            o.removeAttr('disabled');
            $("#send_verify_code").removeAttr('disabled');
            o.next(".click_text").html($.lang.resent_agin);
            wait = 120;
        } else {
            o.attr("disabled", true);
            $("#send_verify_code").attr('disabled', true);

                o.next(".click_text").html(wait + $.lang.next_time_send+$.lang.two_free_access);
      
            wait--;
            setTimeout(function() {
                    time(o)
                },
                1000)
        }
    }

});    
