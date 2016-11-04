define(['jquery', 'validate', 'icheck'], function($){
	 $('input').iCheck({
	     checkboxClass: 'icheckbox_flat-green',
	     radioClass: 'iradio_flat-green'
	   });

    $("#sub").click(function () {
		text = $("input[type='checkbox']").is(':checked') 
       	if(text) {
       		$("#report_form").submit();
       	}else{
       		alert("举报内容不能为空");
       	};
    });
});
