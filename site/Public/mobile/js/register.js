define(['jquery', 'validate'], function($){
  $("#register-form")[0].reset();
	var mobile = false;
	var dom_i_eye=$("#i_eye");//魔性的小眼睛

//魔性的小眼睛单击事件
	dom_i_eye.click(function(){
		if($(this).hasClass('fa-eye')){
			$(this).removeClass('fa-eye');
			$(this).addClass('fa-eye-slash');
			$("#inputPassword").prop('type','text');
		}else{

			$(this).addClass('fa-eye');
			$(this).removeClass('fa-eye-slash');
			$("#inputPassword").prop('type','password');
		}

	});

	//validate自定义验证手机号
	$.validator.addMethod("isMobile", function(value, element){
		var length = value.length;
		if(length == 11 && /^1[358]\d{9}$/.test(value)){
			mobile = true;
			$(".btn-block").attr('disabled', false);
		}else{
			$(".btn-block").attr('disabled', true);
			mobile = false;
		}
		return mobile;
	}, "请填写正确的手机号码");

	//validate自定义验证手机号
	$.validator.addMethod("isEmail", function(value, element){
		var pattern = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
		return pattern.test(value);
	}, "请填写正确的邮箱");

	//表单验证
	$("#register-form").validate({
		errorPlacement: function(error, element){
		},
    showErrors:function(error, element){
      if(error && element.length){
        $("#error-msg").html('').html($(element)[0].message); 
        $("#error-msg").parents(".error-position").show(); 
      }else{
        $("#error-msg").parents(".error-position").hide(); 
      }
    },
		submitHandler: function(form){
			$.ajax({
				type: 'POST',
				url: $("#register-url").val(),
				dataType: 'json',
				data: $(form).serialize(),
			}).done(function(res) {
				if(res.status){
					window.location.href=res.url;	
				}else{
					alert(res.msg);	
				}
			});
		},
		rules: {
			email: {
				required: true,
				isEmail: true,
				remote: {
					url: $("#email-url").val(),
					type: "post",
					data: {
						email: function() {
							return $("#inputEmail").val();
						},

					}
				}
			},
			mobile: {
				required: true,
				isMobile: true,
				remote: {
					url: $("#mobile-url").val(),
					type: "post",
					data: {
						mobile: function() {
							return $("#inputMobile").val();
						}
					}
				}
			},
			password: {
				required: true,
				rangelength: [6, 18]
			},
			code: {
				required: true, 
				rangelength: [6,6],
			},
		},
		messages: {
			email: {
				required: "请输入邮箱",
				remote: "邮箱已存在"
			},
			mobile: {
				required: "请输入手机号",
				isMobile: '手机号不正确',
      		    remote: '手机号已注册',
			},
			password: {
				required: "密码不能为空",
				rangelength: "密码必须为6到18个字符"
			},
			code: {
				required: '验证码不能为空',
				rangelength: '验证码为6位',
			}
		}
	});
	$("#register-form").delegate('.send', 'click', function(){
		if(mobile){
			var phone = $("#inputMobile").val();	
			time($(".send"));
			$.ajax({
				type: 'POST',
				url: $("#send-url").val(),
				dataType: 'json',
				data:{mobile:phone},
			}).done(function(res) {
				console.log(res);
				if(res.status==0){
					wait=0;//重新设置wait时间为0
				}
			});
		}
	});

	var wait = 100; 
	function time(o) {
		if (wait <= 0) {
			o.removeAttr('disabled');
			o.html('发送验证码');
			wait = 100;
		} else {
			o.attr("disabled", true);
			o.html(wait + "秒后可重发");
			wait--;
			setTimeout(function () {
				time(o)
			},
			1000)
		}
	}
});


