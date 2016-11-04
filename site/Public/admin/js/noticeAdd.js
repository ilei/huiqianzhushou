/**
 * Created by Admin on 2015/2/27.
 */
    $(document).ready(function(){
        var ue = UE.getEditor('ym_editor',{
									initialFrameHeight:250,
									initialFrameWidth:680,
									topOffset: 77, // 设置距页面顶部位置
									initialStyle: ['p,body{line-height:1.8em;font-size:13px;}'],
            serverUrl : YM['ueditor_server_url']
        });

        //表单验证
        $('#noticeAdd').submit(function() {
            UE.getEditor('ym_editor').sync();
        }).validate({
            ignore: '',
            errorPlacement: function(error, element){
                element.parent().parent().next('.error-wrap').append(error);
            },
            rules: {
                title: {
                    required: true,
                    rangelength: [2, 50]
                },
                content: {
                    required: true,
                    rangelength:[2,10000]
                }
            },
            messages: {
                title: {
                    required: "通知标题不能为空",
                    rangelength: "通知标题不得少于两个字，不得多于五十个字"
                },
                content: {
                    required: "通知内容不能为空",
                    rangelength: "通知内容不能少于两个字或不能多于一万个字"
                }
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


			$("#status0").on('click',function(){
				$("#status").val(0);
				$("#noticeAdd").submit();
			})
			$("#status1").on('click',function(){
				$("#status").val(1);
				$("#noticeAdd").submit();
			})
    });