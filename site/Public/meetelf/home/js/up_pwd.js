define(['jquery', 'validate'], function($){
    $("#pwdForm").validate({
      errorPlacement: function(error, element){
          element.siblings().html(error);
      },
      rules: {
          password: {
              required: true,
              rangelength: [6, 18]
        },
          re_password: {
              required: true,
              equalTo: "#password"
        }
      },
      messages: {
        password: {
          required: "密码不能为空",
          rangelength: "密码位数不得小于6个，不得大于18个"
        },
        re_password: {
          required: "确认密码不能为空",
          equalTo: "两次密码不一致"
        }
      }
    });
});

$('document').ready(function(){
    $
});
