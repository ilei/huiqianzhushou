/**
 * 权限分配
 *
 * CT: 2014-12-04 14:40 by QXL
 */
$(function(){
	/**
	 * 保存权限
	 *
	 * CT: 2014-12-04 14:40 by QXL
	 */
	$('.js-submit').click(function(){
		var obj=$(this);
		var data=$("#regorg").serialize();
		$.ajax({
			 url:YM['save_distribution'],
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
		})
	})
})