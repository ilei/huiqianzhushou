/**
 * JS模态框弹出
 *
 * CT: 2014-12-02 10:50 by QXL
 */
function alertTips(obj,msg,url){
	obj.modal('show');
	obj.find('.tips-msg').html(msg);
	var t=setTimeout(function(){
		if(url){
			location.href=url;
		}else{
			obj.modal('hide');
		}
		clearTimeout(t);
	},1800);
	
	$('#tips-modal').on('hidden.bs.modal', function (e) {
		if(url){
			location.href=url;
		}else{
			obj.modal('hide');
		}
	})
}