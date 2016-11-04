define(['jquery', 'lang', 'validate'], function($){
  var $form = $("#login-form"),
      $submit = $("#login-submit");
  $(function(){
    $submit.attr('disabled', false); 
  });
  //表单验证
  $submit.on('click', function(e){
    e.preventDefault(); 
    $submit.attr('disabled', true);
    $form.submit();
  });
  $form.validate({
    errorPlacement: function(error, element){
      if(error){
        element.siblings().html(error);
        $submit.attr('disabled', false);
      }
    },
    submitHandler:function(form){
      $.ajax({
        type: 'POST',
        url: $form.data('href'),
        dataType: 'json',
        data: $form.serialize(),
        success:function(res){
          if(res.status){
            window.location.href=res.url;	
          }else{
            $submit.attr('disabled', false);
            var msg = '<label id="username-error" class="error" for="username">'+res.msg+'</label>';
            $("#error-msg").html('').html(msg);
            return false;
          }
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
        required: $.lang.user_not_empty,
        remote:   $.lang.user_not_exist, 

      },
      password: {
        required: $.lang.password_not_empty,
        rangelength: $.lang.password_len_error, 
      },
    }
  });
});

