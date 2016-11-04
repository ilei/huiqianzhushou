/**
 * 
 */
$(function(){	
	/**
	 * 修改VIP等级
	 *
	 * CT: 2014-12-04 14:34 by QXL
     * UT: 2015-08-11 09:29 by QY
	 *
	 */
	$('body').on('click','.js-submit',function(){
		var obj=$(this);
		var key=YM['org_guid'];
		var vip=$('select[name=vip]').val();
		var realname = $("#realname").val();
        var nickname = $("#nickname").val();
		var community_types = $("#community_types").val();
		var community_species = $("#community_species").val();
		//var startTime = $("#startTime").val();
		//var endTime = $("#endTime").val();



		var data={key:key,vip:vip,realname:realname,nickname:nickname,community_types:community_types,community_species:community_species};
		$.ajax({
			 url:YM['change_level'],
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
	});
});