define(['jquery', 'validate', 'langs'], function($){
  $(function(){
    $("#login-submit").attr('disabled', false); 
  });
	//表单验证
  $("#login-form").on('submit', function(e){
    e.preventDefault(); 
		$(".btn-block").attr('disabled', true);
		$.ajax({
			type: 'POST',
			url: $(this).data('href'),
			dataType: 'json',
			data: $(this).serialize(),
      success:function(res){
        if(res.status){
          window.location.href=res.url;	
        }else{
          $(".btn-block").attr('disabled', false);
          var msg = '<label id="username-error" class="error" for="username">'+res.msg+'</label>';
          $("#error-msg").html('').html(msg);
          return false;
        }
      }
		});
  });
	$("#login-form").validate({
		errorPlacement: function(error, element){
			if(error){
				element.siblings().html(error);
			}
		},
		rules: {
			username: {
				required: true,
			},
			password: {
				required: true,
				rangelength: [6, 18]
			},
		},
		messages: {
			username: {
				required: $.lang.user_not_empty,
			},
			password: {
				required: $.lang.password_not_empty,
				rangelength: $.lang.password_len_error, 
			},
		}
	});
});

