define(['jquery', 'lang', 'validate','lazyload','upload'], function($,lang){
	var guid = $('input[name=guid]').val();	
	//上传主办方头像
	 $('#poster,#edit_poster').ajaxUploadPrompt({
             url : '/common/ajax_upload/t/organizer_pic/'+'token/'+guid,
             // url : '<?php echo U('Common/ajax_upload', array('t'=>'organizer_pic')) ?>',
             type: "POST",
             dataType: "json",
             // data: { '<?php echo session_name()?>':'<?php echo session_id()?>', guid:'<?php echo session('auth')['org_guid']?>' },
             beforeSend : function () {
                 $('#poster').append('<i id="loading" class="fa fa-spinner fa-spin"></i>');
//                $('img#poster_preview').after('<div id="loading-cover"><i id="loading" class="fa fa-spinner fa-spin"></i></div>');
             },
             error : function () {
                 // alertTips($('#tips-modal'),'服务器出错, 请稍后重试!');
                 alert('服务器出错, 请稍后重试。');
             },
             success : function (data) {
                 $('#loading').remove();
                 output = data.data;
                 if(data.status == '1') {
                    // alert('上传成功');
                     $('img#ImgPr').attr('src', output.path+'?'+$.now());
                     $('input[name=poster]').val(output.val);
                 } else {
                     // alertTips($('#tips-modal'), data.msg);
                     alert(data.msg);
                 }
             }
         });
	
});