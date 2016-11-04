/**
 * 
 */
$(function(){
	$('body').on('click','.js_add_flow',function(){
		var guid = $(this).parents('.task_flow_item').attr('data-guid');
		var task_name = $(this).parents('.task_flow_item').find('.task_flow_name').html();
		var html= '<li class="list-group-item"><input type="hidden" name="selected_guid" value="'+guid+'" /><i class="fa fa-trash-o pull-right js_del_task"></i><span class="selected_task_name">'+task_name+'</span></li>';
		$('.selected_task_list').append(html);
	})
	
	$('body').on('click','.js_del_task',function(){
		$(this).parents('.list-group-item').remove();
	})
	
	$('.js-submit').click(function(){
		var obj = $(this);
		var guid = $('input[name=guid]').val();;
		var name = $('input[name=flowname]').val();
		var state = $('input[name=is_del]:checked').val();
		var selected_guid = get_multi_data($('input[name=selected_guid]'));
		var data = {state:state, guid:guid, selected_guid:selected_guid, name:name};
		$.ajax({
			 url:YM['saveFlow'],
			 type:'POST',
			 data:data,
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				 if(data.status == 'ok'){
				 	alertTips($('#tips-modal'),'保存成功',YM['redirectPath']);
				 }else if(data.status == 'ko'){
					alertTips($('#tips-modal'),'保存失败');
				 }
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	})
	
	function get_multi_data(obj){
		var str = '';
		obj.each(function(){
			str+=$(this).val()+',';
		})
		str = str.substring(0,str.length-1);
		return str;
	}
});