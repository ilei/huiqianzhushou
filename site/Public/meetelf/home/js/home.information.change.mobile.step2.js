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
                checkcode_tishinr = $("#checkcode-error").text();
                verify_tishinr = $("#verify-error").text();

                var form_action = $("#MobileForm").attr('action');
                $('#MobileForm').attr('action',form_action);

                //判断是邮箱的话则按钮可以点击
                // if(mobile != '' && username_tishinr == ''){
                //     $("#send_verify_code").removeAttr("disabled");
                // }else{
                //     $("#send_verify_code").attr("disabled","disabled");
                // }

                if(code != '' && verify != '' &&  checkcode_tishinr == '' && verify_tishinr == ''){
                    $("#next_submit").removeAttr('disabled');
                }else{
                    $("#next_submit").attr('disabled','disabled');
                }
            }
        });
    });
//验证手机号
function isMobile(str){ 
        var reg = /^1[358]\d{9}$/; 
        return reg.test(str); 
        }        
//刷新验证码
function refresh_verify(){
    var verifyimg = $(".verifyimg").attr("src");
    if( verifyimg.indexOf('?')>0){
        $(".verifyimg").attr("src", verifyimg+'&random='+Math.random());
    }else{
        $(".verifyimg").attr("src", verifyimg.replace(/\?.*$/,'')+'?'+Math.random());
    }
}

//异步发送校验码
    $("#send_verify_code").click(function(){
    	var mobile = $("#mobile").val();
        var obj = this;
    	if (isMobile(mobile)==true) {
            $.ajax({
            url: $("#ajax_check_mobile").val(),
                        type: "post",
                        dataType: "json",
                        data: {mobile:mobile},
                        success:function(data){
                            if (data==true) {
                                time($(obj));
                                    $.ajax({
                                        type: "post",
                                        url: $("#send_button_url").val(),
                                        dataType: "json",
                                        data:{mobile:mobile},
                                        success: function (data) {
                                            if(data.status==0){
                                                $(".click_text").hide();
                                                $.alertModal(data.msg);
                                            }
                                        }
                                    });
                                }else{
                                    $.alertModal($.lang.mobile_existing);
                                }
                        }
             });           
	        
        }else{
        	$.alertModal('请填写正确手机号');
        };
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

    $("#verifyimg").click(function(){
        refresh_verify();
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

    $("#click_verify").click(function(){
        refresh_verify();
    });
    
});    
