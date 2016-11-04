define('lang',['jquery'],function ($){
  var lang = {
    user_not_empty:'用户名不能为空',
    user_not_exist:'该账号未注册',
    password_not_empty:'密码不能为空',
    password_len_error:'密码长度为6到18位', 
  };
  $.extend({lang:lang});
});
