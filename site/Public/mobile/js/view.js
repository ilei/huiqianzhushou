define(['jquery'], function($){
	$(".btn-cancel").on('click', function(){
		var con = confirm('确定取消报名吗?');	
		if(con){
			window.location.href=$(this).data('href');	
		}
	})
});
