/**
 * 
 */
$(function(){
	var check_condition = true;

    // 日常任务开始时间, 结束时间
    $('#ym_dtbox').DateTimePicker();

	$('body').on('blur','input[name=condition_name]',function(){
        var text_value=$(this).val();
        if(text_value !='') {
            $(this).removeClass('error');
            $(this).parents('.condition_name_wrap').find('.error-wrap').html('');
        }
    });
    
    $('body').on('blur','input[name=condition_finish_num]',function(){
        var text_num_value=$(this).val();
        if(text_num_value !='') {
            $(this).removeClass('error');
            $(this).parents('.finish_num_wrap').find('.error-wrap').html('');
        }
    });
    
    $('body').on('change','select[name=ym_js]',function(){
        var ym_js=$(this).val();
        if(ym_js !='0') {
            $(this).removeClass('error');
            $(this).parents('.condition_webjs_wrap').find('.error-wrap').html('');
        }
    });
    
    $('body').on('change','select[name=task_sign]',function(){
        var task_type=$(this).val();
        if(task_type !='0') {
            $(this).removeClass('error');
            $(this).parents('.condition_type_wrap').find('.error-wrap').html('');
        }
    });
	
    
    $('#regorg').submit(function(){
    	if($('input[name=condition_name]').size()<1){
    		alertTips($('#tips-modal'),'至少要有一项任务条件');
    		return false;
    	}
    	
	    var is_empty = false;
	    $('input[name=condition_name]').each(function(){
	    	var text_value = $(this).val();
            if(text_value =='') {
                is_empty = true;
                $(this).addClass('error');
                $(this).parents('.condition_name_wrap').find('.error-wrap').html('<label class="custom-error">任务说明不得为空</label>');
            }
        });
	    
	    $('input[name=condition_finish_num]').each(function(){
	    	var text_num_value = $(this).val();
            if(text_num_value == 0 || text_num_value=='') {
                is_empty = true;
                $(this).addClass('error');
                $(this).parents('.finish_num_wrap').find('.error-wrap').html('<label class="custom-error">请填写数量</label>');
            }
        });
	    
	    $('select[name=task_sign]').each(function(){
	    	var task_type = $(this).val();
            if(task_type == '0') {
                is_empty = true;
                $(this).addClass('error');
                $(this).parents('.condition_type_wrap').find('.error-wrap').html('<label class="custom-error">请设置任务类型</label>');
            }
        });
	    
	    $('select[name=ym_js]').each(function(){
	    	var ym_js = $(this).val();
            if(ym_js == '0') {
                is_empty = true;
                $(this).addClass('error');
                $(this).parents('.condition_webjs_wrap').find('.error-wrap').html('<label class="custom-error">请设置任务指向</label>');
            }
        });
	    
	    check_condition = is_empty ? false : true;
    });
	
	$('body').on('click','.js_del_condition',function(){
		$(this).parents('.condition_item').remove();
	})
	
	
    var ue = UE.getEditor('contents',{
      	initialFrameHeight:250,
        topOffset: 77, // 设置距页面顶部位置
        initialStyle: ['p,body{line-height:1.8em;font-size:13px;}'],
     	onready:function(){
     		ue.setContent(YM['task_info_description']);
        }
    });
	
	/**
	 * 验证保存权限
	 *
	 * CT: 2014-12-02 10:50 by QXL
	 */
		var validator = $('#regorg').submit(function() {
			UE.getEditor('contents').sync();
    	}).validate({
        errorClass: "invalid",
        errorPlacement: function(error, element){
            element.parents('.form-group').find('.error-wrap').append(error);     
        },
        ignore:'',
        rules: {
            name: {
                required: true,
                rangelength: [2, 50]
            },
            content:{
            	required: true
            },
            type: {
            	digits:true
            },
            integral: {
            	required: true,
            	digits:true,
            	min:0
            },
            exp: {
            	required: true,
            	digits:true,
            	min:0
            },
            thumb: {
            	required: true
            },
            is_del:{
            	required: true
            }
        },
        messages: {
        	name: {
                required: "任务名称不能为空", 
                rangelength: "任务名称不得少于2个字，不得多于50个字"
            },
            type: {
            	digits: "请选择任务类型"
            },
            integral: {
            	required:'奖励积分不得为空',
            	digits:'奖励积分为正整数',
            	min:'奖励积分为正整数'
            },
            exp: {
            	required:'奖励经验不得为空',
            	digits:'奖励经验为正整数',
            	min:'奖励经验为正整数'
            },
            thumb: {
            	required: '请上传任务缩略图'
            },
            content: {
            	required: '请编辑任务描述'
            },
            is_del: {
            	required: '请选择任务状态'
            }
		},
		submitHandler: function(form) { //通过之后回调 
			if(!check_condition){
				return false;
			}
			
			var obj=$(this);
			var guid = $('input[name=guid]').val();
			var name = $('input[name=name]').val();
			var type = $('select[name=type]').val();
			var integral = $('input[name=integral]').val();
			var exp = $('input[name=exp]').val();
			var thumb = $('input[name=thumb]').val();
			var startTime = $('input[name=startTime]').val();
			var endTime = $('input[name=endTime]').val();
			var is_del = $('input[name=is_del]:checked').val()
			var description = ue.getContent();
			var condition = {};
			$('.condition_item').each(function(i){
				condition[i] = {
								guid:$(this).find('input[name=condition_guid]').val(),
								name:$(this).find('input[name=condition_name]').val(),
								sign:$(this).find('select[name=task_sign]').val(),
								num:$(this).find('input[name=condition_finish_num]').val(),
								type:$(this).find('select[name=info_type]').val(),
								webjs:$(this).find('select[name=ym_js]').val()
						 	  }
			})
			var data={condition:condition, startTime:startTime, endTime:endTime, guid:guid, name:name, type:type, integral:integral, exp:exp, thumb:thumb, description:description, is_del:is_del};
			$.ajax({
				 url:YM['saveTask'],
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
        }, 
        invalidHandler: function(form, validator) { //不通过回调 
		       return false; 
        } 
	});
	
	validator.focusInvalid = function() {
        if( this.settings.focusInvalid ) {
            try {
                var toFocus = $(this.findLastActive() || this.errorList.length && this.errorList[0].element || []);
                if (toFocus.is("textarea")) {
                    UE.getEditor('contents').focus()
                } else {
                    toFocus.filter(":visible").focus();
                }
            } catch(e) {
            }
        }
    }
	
	function get_multi_data(obj){
		var str = '';
		obj.each(function(){
			str+=$(this).val()+',';
		})
		str = str.substring(0,str.length-1);
		return str;
	}
});