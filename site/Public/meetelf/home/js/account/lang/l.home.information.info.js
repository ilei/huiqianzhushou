define(['jquery'],function ($){
	var lang = {
		nickname_num:'昵称为2到8个字符',
		nickname_type:'昵称只能为汉字或字母',
		mobile_format_err:'请填写正确格式',
		accout_empry:'主办方不能为空',
		mobile_not_empty:'电话不能为空',
		account_num:'名字不得少于2个超过25个字符',
		desc_num:'简介内容不得超过50字符',
		position:'职位名称不得少于2个超过10个字符',
		company:'公司名称不得少于2个超过20个字符',
		address:'地址不得少于2个超过30个字符',
		password_empry:'密码不能为空',
		next_time_send:'秒后可重新操作，',
		next_time_send_look:'请前往邮箱点击链接认证',
		remove_email:'认证已解除',
		email_format_err:'邮箱格式不正确',
		two_free_access:'每日限更换3次',
		resent_agin:'点击重新发送',
		mobile_existing:'该手机号已存在',

	};
  $.extend({lang:lang});
});
