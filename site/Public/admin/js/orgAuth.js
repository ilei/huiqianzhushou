/**
 * 
 */
$(function(){	
	/**
	 * 同意社团认证
	 *
	 * CT: 2014-12-03 14:30 by QXL
	 */
	$('.js-agree').click(function(){
		var obj = $(this);
		var key=YM['key'];
		var data={key:key};
		$.ajax({
			 url:YM['agree'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				 if(data.code=='200'){
					 	alertTips($('#tips-modal'),data.Msg,YM['redirectPath']);
				 }else if(data.code=='201'){
						alertTips($('#tips-modal'),data.Msg);
				 }
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	})
	
	/**
	 * 拒绝社团认证
	 *
	 * CT: 2014-12-03 14:30 by QXL
	 */
	$('.js-refuse').click(function(){
		$('textarea[name=refuseMsg]').val('');
		$('#refuseModal').modal('show');
	})
	
	/**
	 * 发送拒绝信息
	 *
	 * CT: 2014-12-03 14:30 by QXL
	 */
	$('.js-send-refuse').click(function(){
		var obj=$(this);
		var refuseMsg=$('textarea[name=refuseMsg]').val();
		var key=YM['key'];
		var data={key:key,refuseMsg:refuseMsg};
		$.ajax({
			 url:YM['refuse'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				 if(data.code=='200'){
					 	$('#refuseModal').modal('hide');
					 	alertTips($('#tips-modal'),data.Msg,YM['redirectPath']);
				 }else if(data.code=='201'){
						alertTips($('#tips-modal'),data.Msg);
				 }
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	});
})