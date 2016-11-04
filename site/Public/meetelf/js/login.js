define(['jquery', 'lang', 'validate'], function($, lang){
	//表单验证

	$("#login-form").validate({
		errorPlacement: function(error, element){
			if(error){
				element.siblings().html(error);
			}
		},
		submitHandler: function(form){
			$(".btn-block").attr('disabled', true);
			$.ajax({
				type: 'POST',
				url: $(form).data('href'),
				dataType: 'json',
				data: $(form).serialize(),
			}).done(function(res) {
				if(res.status){
					window.location.href=res.url;	
				}else{
					$(".btn-block").attr('disabled', false);
					alert(res.msg);
				}
			});
		},
		rules: {
			username: {
				required: true,
				remote: {
					url: $("#username").data('href'),
					type: "post",
					data: {
						username: function() {
							return $("#username").val();
						}
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
				required: lang.user_not_empty,
				remote:   lang.user_not_exist, 
			},
			password: {
				required: lang.password_not_empty,
				rangelength: lang.password_len_error, 
			},
		}
	});
});

