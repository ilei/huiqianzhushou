$(function(){
	$("input[name='name']").on('change', function(){
		var name = $(this).val();
		var obj = $(this);
		if(!name){
			return false;
		}
		$.ajax({
			type: 'POST',
			url:  check_banned_words_url,
			data: {name: name},
			dataType: "json",
			success: function(data){
				if(data.status=='ko'){
					alert(data.msg);
					obj.val('');
				}
			}
		});
	});	
})