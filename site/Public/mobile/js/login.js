define(['jquery', 'validate'], function($){
	var hidden_act_guid = $("#login_act_guid_hidden").val();//活动id

	//表单验证
	$("#login-form").validate({
		errorPlacement: function(error, element){
			element.css('border-color', '#a94442');
		},
		showErrors:function(error, element){
			if(error && element.length){
				$("#error-msg").html('').html($(element)[0].message);
				$("#error-msg").parents(".error-position").show();
			}else{
				$("#error-msg").parents(".error-position").hide();
			}
		},
		success: function(element){
			$("#" + element[0].htmlFor).css('border-color', '#d9dde5');
		},
		submitHandler: function(form){
			$.ajax({
				type: 'POST',
				url: $("#login-url").val() + '/aid/' + hidden_act_guid,
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
			username: {
				required: true,
				remote: {
					url: $("#check-url").val(),
					type: "post",
					data: {
						email: function() {
							return $("#inputUsername").val();
						},

					}
				}
			},
			password: {
				required: true,
				rangelength: [6, 18]
			},
		},
		messages: {
			username: {
				required: "请输入邮箱",
				remote: "用户名不存在或用户名被锁定"
			},
			password: {
				required: "密码不能为空",
				rangelength: "密码必须为6到18个字符"
			},
		}
	});
});


