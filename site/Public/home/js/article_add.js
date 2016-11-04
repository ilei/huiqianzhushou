/**
 * Created by Admin on 2015/2/27.
 */
    jQuery.validator.addMethod("ymrangelength", function(value, element, params){
				if(params[2]){
					value = value.replace(/[\s]+/g, ""); 
				}
        var length = value.length;
				var more = length >= params[0] && length <= params[1];
				$(element)[0].value = value;
				if(!more){
					$(element)[0].value = value.substr(0, params[1]);
				}
				return more;
    });
    $(document).ready(function(){
        var ue = UE.getEditor('ym_editor',{
            initialFrameHeight:450,
            serverUrl : YM['ueditor_server_url']
        });

        ue.addListener('contentChange',function(){
              value = ue.getContentLength(true);
                if(value < 1 || value > 10000){
                    $("#editor_error").html('<strong>内容不能为空，且不能多于10000个字</strong>');
                }else{
                    $("#editor_error").html('');
                }
         });

        //表单验证
        $('#article_form').submit(function() {
            if(ue.getContentLength(true) < 1 || ue.getContentLength(true) > 10000){
                $("#editor_error").html('<strong>内容不能为空，且不能多于10000个字</strong>');
                alertModal('内容不能为空，且不能多于10000个字');
                return false;
            }
            UE.getEditor('ym_editor').sync();
        }).validate({
            ignore: '',
            errorPlacement: function(error, element){
                element.parent().parent().next('.tishinr').append(error);
            },
            rules: {
                name: {
                    required: true,
                    ymrangelength: [1, 50, true]
                },
                //startTime: {
                //    required: true
                //},
                endTime: {
                    //required: true,
                    afterstart: true
                }
                //content: {
                //    required: true,
                //}
            },
            messages: {
                name: {
                    required: "文章标题不能为空",
                    ymrangelength: "文章标题不得多于五十个字"
                },
                //startTime: {
                //    required: "开始时间不能为空"
                //},
                //endTime: {
                //    afterstart: "结束时间不能为空"
                //},
                //content: {
                //    required: "内容不能为空",
                //}
            }
        }).focusInvalid = function() {
            if( this.settings.focusInvalid ) {
                try {
                    var toFocus = $(this.findLastActive() || this.errorList.length && this.errorList[0].element || []);
                    if (toFocus.is("textarea")) {
                        UE.getEditor('ym_editor').focus()
                    } else {
                        toFocus.filter(":visible").focus();
                    }
                } catch(e) {
                }
            }
        };
    });