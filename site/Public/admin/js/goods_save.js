/**
 * 商品保存相关js
 * CT: 2014-12-02 10:50 by QXL
 */
$(function(){

    // 日常任务开始时间, 结束时间
    $('#ym_dtbox').DateTimePicker();

	// 定义富文本
    var ue = UE.getEditor('content',{
      	initialFrameHeight:250,
        topOffset: 77, // 设置距页面顶部位置
        initialStyle: ['p,body{line-height:1.8em;font-size:13px;}'],
     	onready:function(){
     		ue.setContent(YM['goods_content']);
        }
    });
	
	// 表单验证
	var validator = $('#form_save_goods').submit(function() {
		UE.getEditor('content').sync();
	}).validate({
        errorClass: "invalid",
        errorPlacement: function(error, element){
            element.parents('.form-group').find('.error-wrap').append(error);
        },
        ignore: '',
        rules: {
			name: {
				required: true,
				rangelength: [2, 50]
			},
			category: {
				required: true
			},
            content:{
            	required: true
            },
			price: {
				required: true,
				digits:true,
				min:0
			},
			storage: {
				required: true,
				digits:true,
				min:0
			},
			is_vat:{
				required: true
			},
			is_verify:{
				required: true
			}
        },
        messages: {
			name: {
				required: "商品名称不能为空",
				rangelength: "商品名称不得少于2个字，不得多于50个字"
			},
			category: {
				required: "请选择商品分类",
			},
			content: {
				required: '商品描述不能为空'
			},
			price: {
				required:'商品单价不得为空',
				digits:'商品单价为正整数',
				min:'商品单价为正整数'
			},
			storage: {
				required:'商品库存不得为空',
				digits:'商品库存为正整数',
				min:'商品库存为正整数'
			},
			is_vat: {
				required: '请选择是否提供增值税发票'
			},
			is_verify: {
				required: '请选择是否通过审核'
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
				 url:YM['goods_save_process'],
				 type:'POST',
				 data:data,
				 dataType:'json',
				 beforeSend:function(){
					 obj.button('loading');
				 },
				 success:function(data){
					 if(data.status == 'ok'){
					 	alertTips($('#tips-modal'),'商品保存成功',YM['redirectPath']);
					 }else if(data.status == 'ko'){
						alertTips($('#tips-modal'),'商品保存失败');
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
                    UE.getEditor('content').focus()
                } else {
                    toFocus.filter(":visible").focus();
                }
            } catch(e) {
            }
        }
    }
});