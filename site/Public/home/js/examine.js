/**
 * 
 */
$(function(){
	$('body').on('click','.js_refuse',function(){
		var guid = $(this).parents('td').attr('data-guid');
		$('textarea[name=refuseMsg]').val('');
		$('#refuseModal').modal('show');
		$('#sendRefuse').attr('data-guid',guid);
	})
	
	$('body').on('click','.js_agree',function(){
		var obj = $(this)
		var user_guid = $(this).parents('td').attr('data-guid');
		var data={user_guid:user_guid};
		$.ajax({
			 url:YM['agree'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				if(data.status=='ok'){
					html='<span class="text-success">已经加入社团</span>';
					obj.parents('.examine_state').html(html);
				}else if(data.status=='ko'){
					alertModal(data.msg);
				}
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	});
	
	$('body').on('click','.js_send',function(){
		var obj = $(this)
		var user_guid = $(this).attr('data-guid');
		var refuseMsg = $('textarea[name=refuseMsg]').val();
		if(refuseMsg == '') {
			alertModal('拒绝理由不能为空。');
		}
		var data={user_guid:user_guid, refuseMsg:refuseMsg};
		$.ajax({
			 url:YM['refuse'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				if(data.status=='ok'){
					$('#refuseModal').modal('hide');
					html='<span class="text-danger">已拒绝加入社团</span>';
					$('.examine_state[data-guid='+user_guid+']').html(html);
				}else if(data.status=='ko'){
					alertModal(data.msg);
				}
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	})
	
	$('body').on('click','.js_pull_black',function(){
		var obj = $(this)
		var user_guid = $(this).parents('td').attr('data-guid');
		var data={user_guid:user_guid};
		$.ajax({
			 url:YM['pull_black'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				if(data.status=='ok'){
					html='<span class="text-danger">已被加入黑名单</span>';
					obj.parents('.examine_state').html(html);
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