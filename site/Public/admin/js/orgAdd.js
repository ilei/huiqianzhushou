/**
 * 
 */
$(function(){	
	/**
	 * 验证保存社团信息
	 *
	 * CT: 2014-12-02 10:50 by QXL
	 */
	$('#regorg').validate({
        errorClass: "invalid",
        errorPlacement: function(error, element){
            element.parents('.form-group').find('.error-wrap').append(error);
        },
        rules: {
        	 email: {
                 required: true,
                 email: true,
                 remote:{
                	 url: YM['checkMail'],
                	 type:'post'
                 }
             },
            name: {
                required: true,
                rangelength: [2, 50],
                remote:{
                	 url: YM['checkGroupName'],
                	 type:'post'
                }
            },
             password: {
                 required: true,
                 rangelength: [6, 18]
             },
             repassword:{
            	 required: true,
            	 equalTo:"#passwd"
             }
		},
        messages: {
        	 email: {
                 required: "电子邮箱地址不能为空",
                 email: "电子邮箱格式不正确",
                 remote:"该电子邮箱已存在"
             },
        	name: {
                required: "社团名称不能为空", 
                rangelength: "社团名称不得少于两个字，不得多于五十个字",
                remote:"该社团名称已存在"	
            },
        	password: {
                required: "用户密码不能为空",
                rangelength: "用户密码必须为6到18个字符"
            },
            repassword: {
            	required: "密码确认不能为空",
            	equalTo: "用户密码和密码确认必须一致"
            }
		},
		submitHandler: function(form) { //通过之后回调 
			var obj=$(this);
			var data=$("#regorg").serialize();
			$.ajax({
				 url:YM['regOrg'],
				 type:'POST',
				 data:data,
				 dataType:'json',
				 beforeSend:function(){
					 obj.button('loading');
				 },
				 success:function(data){
					 if(data.code=='200'){
						 	alertTips($('#tips-modal'),'注册成功',YM['redirectPath']);
					 }else if(data.code=='201'){
							alertTips($('#tips-modal'),'注册失败');
					 }
				 },
				 complete:function(){
					 obj.button('reset');
				 }
			 });
        }, 
        invalidHandler: function(form, validator) { //不通过回调 
		       return false; 
        } 
	});
});