define(['jquery', 'validate'], function($){
	$(window).load(function(){
		if($("input[name='agree']").is(':checked')){
			$(".btn-block").removeAttr('disabled');			
		}else{
			$(".btn-block").attr('disabled', true);			
		}	
	}());
	$(function(){
		var mobile = false;
		//validate自定义验证手机号
		$.validator.addMethod("isMobile", function(value, element){
			var length = value.length;
			if(length == 11 && /^1[358]\d{9}$/.test(value)){
				mobile = true;
				$(".send").attr('disabled', false);
			}else{
				$(".send").attr('disabled', true);
				mobile = false;
			}
			return mobile;
		}, "请填写正确的手机号码");
		//validate自定义验证手机号
		$.validator.addMethod("isEmail", function(value, element){
			var pattern = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
			return pattern.test(value);
		}, "请填写正确的邮箱");

		$("#register-form").delegate("input[name='agree']", 'click', function(){
			if($(this).is(':checked')){
				$(".btn-block").removeAttr('disabled');			
			}else{
				$(".btn-block").attr('disabled', true);			
			}	
		});

		//表单验证
		$("#register-form").validate({
			errorPlacement: function(error, element){
				if($(element).attr('id') != 'phone'){
					element.parent().attr('class', 'form-group has-error');
					element.siblings().html(error);
				}else{
					console.log($(error[0]).html());
				}
			},
			rules: {
				email: {
					required: true,
					isEmail: true,
					remote: {
						url: "/auth/check/type/email",
						type: "post",
						data: {
							username: function() {
								return $("#email").val();
							}
						}
					}
				},
				phone: {
					required: true,
					isMobile: true,
				},
				password: {
					required: true,
					rangelength: [6, 18]
				},
				verify: {
					required: true, 
					rangelength: [6,6],
				},
			},
			messages: {
				email: {
					required: "请输入邮箱",
					remote: "邮箱已存在"
				},
				phone: {
					required: "请输入手机号",
					isMobile: '手机号不正确',
				},
				password: {
					required: "密码不能为空",
					rangelength: "密码必须为6到18个字符"
				},
				verify: {
					required: '验证码不能为空',
					rangelength: '验证码为6位',
				}
			}
		});
		$("#register-form").delegate('.send', 'click', function(){
			if(mobile){
				var phone = $("#phone").val();	
				time($(".send"));
				$.ajax({
					type: 'POST',
					url: '/home/auth/ajax_send_code',
					dataType: 'json',
					data:{phone:phone},
				}).done(function(res) {
					console.log(res);
				});
			}
		});

		var wait = 100; 
		function time(o) {
			if (wait == 0) {
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
});


