/**
 * 
 */
$(function(){	
	/**
	 * 锁定社团

	 * CT: 2014-12-03 14:34 by QXL
	 *
	 */
	$('body').on('click','.js-lock',function(){
		var obj=$(this);
		var key=$(this).parents('tr').attr('data-guid');
		var data={key:key};
		$.ajax({
			 url:YM['lock'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				 if(data.code=='200'){
					 alertTips($('#tips-modal'),data.Msg);
					 obj.parents('tr').find('.lock').html("<span class='text-danger'> 已锁定</span>（<a href='javascript:void(0);' class='js-unlock'>解锁</a>）");
				 }else if(data.code=='201'){
						alertTips($('#tips-modal'),data.Msg);
				 }
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	});
	
	/**
	 * 解锁社团

	 * CT: 2014-12-03 14:34 by QXL
	 *
	 */
	$('body').on('click','.js-unlock',function(){
		var obj=$(this);
		var key=$(this).parents('tr').attr('data-guid');
		var data={key:key};
		$.ajax({
			 url:YM['unlock'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				 if(data.code=='200'){
					 alertTips($('#tips-modal'),data.Msg);
					 obj.parents('tr').find('.lock').html("<span class='text-success'> 未锁定</span>（<a href='javascript:void(0);' class='js-lock'>锁定</a>）");
				 }else if(data.code=='201'){
						alertTips($('#tips-modal'),data.Msg);
				 }
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	});
	
	/**
	 * 删除社团

	 * CT: 2014-11-27 18:20 by QXL
	 *
	 */
	$('.js-del').click(function(){
		var key=$(this).parents('tr').attr('data-guid');
		alertConfirm($('#confirm-modal'),'确认删除？');
		$('.js-confirm').attr('data-key',key);
	})
	
	$('.js-confirm').click(function(){
		var obj=$(this);
		var key=$(this).attr('data-key');
		var data={key:key};
		$.ajax({
			 url:YM['delOrg'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				 if(data.code=='200'){
					 	alertTips($('#tips-modal'),'删除成功');
					 	$('tr[data-guid='+key+']').remove();
				 }else if(data.code=='201'){
						alertTips($('#tips-modal'),'删除失败');
				 }
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	});
	
	
	
	function alertConfirm(obj,msg,url){
			obj.modal('show');
			obj.find('.tips-msg').html(msg);
	}
});