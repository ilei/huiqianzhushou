/**
 * 
 */
$(function(){
	$('body').on('click','.js_invite',function(){
		var obj = $(this)
		var user_guid = $(this).attr('data-guid');
		var data={user_guid:user_guid};
		$.ajax({
			 url:YM['invite'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				 if(data.status=='ok'){
					 $html = '<span class="org_status_in">已发出邀请</span>	<button class="btn mybtn js_invite" data-guid="' + user_guid + '" data-loading-text="发送中..." type="button">再次邀请</button>';
					 obj.parents('.state_wrap').html($html);
					 obj.remove();
				 }else if(data.status=='ko'){
					 alertModal(data.msg);
				 }
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	})
})