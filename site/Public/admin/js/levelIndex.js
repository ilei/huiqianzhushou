/**
 * 
 */
$(function(){
	/**
	 * 生成配置文件
	 *
	 * CT: 2014-12-02 10:50 by QXL
	 */
	$('.js-creat-file').click(function(){
		var obj=$(this);
		$.ajax({
			 url:YM['create_config_file'],
			 type:'POST',
			 dataType:'json',
			 beforeSend:function(){
				 obj.button('loading');
			 },
			 success:function(data){
				 if(data.code=='200'){
					 alertTips($('#tips-modal'),data.Msg);
				 }else if(data.code=='201'){
						alertTips($('#tips-modal'),data.Msg);
				 }
			 },
			 complete:function(){
				 obj.button('reset');
			 }
		 });
	})
});