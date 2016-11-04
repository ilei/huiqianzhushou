/**
 * 
 */
$(function(){
	/**
	 * 验证保存等级
	 *
	 * CT: 2014-12-02 10:50 by QXL
	 */
	$('#regorg').validate({
        errorClass: "invalid",
        errorPlacement: function(error, element){
            element.parents('.form-group').find('.error-wrap').append(error);
        },
        rules: {
            name: {
                required: true,
                rangelength: [2, 50]
            },
            sort: {
                required: true,
                digits:true
        	}
        },
        messages: {
        	name: {
                required: "等级名称不能为空", 
                rangelength: "权限名称不得少于2个字，不得多于50个字"
            },
	    	sort: {
	            required:  "等级排序不能为空", 
	            digits: "等级排序必须是数字"
	        }
		},
		submitHandler: function(form) { //通过之后回调 
			var obj=$(this);
			var data=$("#regorg").serialize();
			$.ajax({
				 url:YM['saveLevel'],
				 type:'POST',
				 data:data,
				 dataType:'json',
				 beforeSend:function(){
					 obj.button('loading');
				 },
				 success:function(data){
					 if(data.code=='200'){
						 	alertTips($('#tips-modal'),'保存成功',YM['redirectPath']);
					 }else if(data.code=='201'){
							alertTips($('#tips-modal'),'保存失败');
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