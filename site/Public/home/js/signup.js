/**
 * Created by Admin on 2015/1/29.
 */
var formCommonItems = [];
// 常用表单
formCommonItems[0] = {
    "html_type": "text",
    "ym_type": "email",
    "is_info": 0, // 是否为报名必填项
    "is_required": false, // 该表单是否必填
    "multiple": false,
    "title": "电子邮箱",
    "title_readonly" : true,
    "subitems": [],
    "placeholder": '',
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "单行文本框",
    "description": "此栏位只能用来填写用户电子邮箱。若设为必填，则报名成功后可以通过邮箱来发送电子票，若未设为必填，则只能通过短信发送电子票。"
};
formCommonItems[1] = {
    "html_type": "text",
    "ym_type": "company",
    "is_info": 0,
    "is_required": false,
    "multiple": false,
    "title": "公司",
    "title_readonly" : true,
    "placeholder": '',
    "subitems": null,
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "单行多文本框",
    "description": "此栏位只能用来填写用户公司，限定长度为20个字，第一个公司栏位在用户填写后，将会显示在用户参会签到时打印的用户标签上。"
};
formCommonItems[2] = {
    "html_type": "text",
    "ym_type": "position",
    "is_info": 0,
    "is_required": false,
    "multiple": false,
    "title": "职位",
    "title_readonly" : true,
    "subitems": null,
    "placeholder": '',
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "单行文本框",
    "description": "此栏位只能用来填写用户职位，限定长度为10个字，第一个职位栏位在用户填写后，将会显示在用户参会签到时打印的用户标签上。"
};
formCommonItems[3] = {
    "html_type": "radio",
    "ym_type": "sex",
    "is_info": 0,
    "is_required": false,
    "multiple": false,
    "title": "性别",
    "title_readonly" : true,
    "subitems": ["男", "女"],
    "placeholder": '',
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "单选按钮框",
    "description": "此栏位为单选，用来填写用户性别。"
};
// 自定义表单
formCommonItems[100] = {
    "html_type": "text",
    "ym_type": "text",
    "is_info": 0, // 是否为报名必填项
    "is_required": false, // 该表单是否必填
    "multiple": false,
    "title": "",
    "title_readonly" : false,
    "placeholder": '单行文本框',
    "subitems": [],
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "单行文本框",
    "description": "此栏位为单行文本框，用户填写长度限定为不得超过50个字。"
};
formCommonItems[101] = {
    "html_type": "textarea",
    "ym_type": "textarea",
    "is_info": 0, // 是否为报名必填项
    "is_required": false, // 该表单是否必填
    "multiple": false,
    "title": "",
    "title_readonly" : false,
    "placeholder": '多行文本框',
    "subitems": [],
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "多行文本框",
    "description": "此栏位为多行文本框，用户填写长度限定为不得超过200个字。"
};
formCommonItems[102] = {
    "html_type": "radio",
    "ym_type": "radio",
    "is_info": 0, // 是否为报名必填项
    "is_required": false, // 该表单是否必填
    "multiple": false,
    "title": "",
    "title_readonly" : false,
    "placeholder": '单项选择框',
    "subitems": ['', ''],
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "单选框",
    "description": "此栏位为单项选择框，默认最少有两个选项。"
};
formCommonItems[103] = {
    "html_type": "checkbox",
    "ym_type": "checkbox",
    "is_info": 0, // 是否为报名必填项
    "is_required": false, // 该表单是否必填
    "multiple": false,
    "title": "",
    "title_readonly" : false,
    "placeholder": '多项选择框',
    "subitems": ['', '', ''],
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "复选框",
    "description": "此栏位为多项选择框，默认最少有两个选项。"
};
formCommonItems[104] = {
    "html_type": "text",
    "ym_type": "date",
    "is_info": 0, // 是否为报名必填项
    "is_required": false, // 该表单是否必填
    "multiple": false,
    "title": "",
    "title_readonly" : false,
    "placeholder": '日期选择框',
    "subitems": [],
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "日期选择框",
    "description": "此栏位为日期选择框，将限定用户填写年月日。"
};
formCommonItems[105] = {
    "html_type": "select",
    "ym_type": "select",
    "is_info": 0, // 是否为报名必填项
    "is_required": false, // 该表单是否必填
    "multiple": false,
    "title": "",
    "title_readonly" : false,
    "placeholder": '下拉选择框',
    "subitems": ['',''],
    "note": null,
    "is_hide": false,
    "value": null,
    "type_title": "下拉选择框",
    "description": "此栏位为下拉选择框，默认最少有两个选项。"
};

// 生成相应栏位
function renderFormItemTemplate(commonItem) {
    var itemHtml = '';
    if(commonItem){

        if(commonItem.title_readonly == true) {
            var readonly = "readonly";
        } else {
            var readonly = "";
        }

        itemHtml =  '<div id="fi_'+item_key+'" class="row ym_form_field"> <div>';
        itemHtml +=     '<input type="hidden" name="items['+item_key+'][ym_type]" value="'+commonItem.ym_type+'" />';
        itemHtml +=     '<input type="hidden" name="items['+item_key+'][html_type]" value="'+commonItem.html_type+'" />';
        itemHtml +=     '<input type="hidden" name="items['+item_key+'][is_info]" value="'+commonItem.is_info+'" />';
        itemHtml +=     '<div class="pull-left checkbox ml12">';
        itemHtml +=         '<label><input type="checkbox" value="1" name="items['+item_key+'][is_required]" /> 必填 </label>';
        itemHtml +=     '</div>';
        itemHtml +=     '<div class="pull-left width110 ml12">';
        itemHtml +=         '<input type="text" class="form_required form-control ym_form_signup_field_required" '+ readonly +' placeholder="'+commonItem.placeholder+'" name="items['+item_key+'][name]" value="'+commonItem.title+'">';
        itemHtml +=     '</div>';
        itemHtml +=     '<div class="pull-left width280 ml12">';
        itemHtml +=         '<input type="text" class="form-control" name="items['+item_key+'][note]" placeholder="提示信息在这儿写！">';
        itemHtml +=     '</div>';
        itemHtml +=     '<div class="pull-left"><button type="button" class="btn btn-delete" data-toggle="tooltip" data-placement="top" title="'+commonItem.description+'"><i class="glyphicon glyphicon-info-sign"></i></button></div>';
        itemHtml +=     '<div class="pull-left"><button type="button" class="btn btn-delete" onclick="javascript:removeFormItem('+item_key+');" title="删除栏位"><i class="glyphicon glyphicon-trash"></i></button></div>';
        itemHtml +=     '</div>';

        if (commonItem.html_type == "radio" || commonItem.html_type == "checkbox" || commonItem.html_type == "select") {
            itemHtml += '<div class="create-options-list">选项列表<div class="options-list" id="fio_' + item_key + '">';
            itemHtml += renderFormItemOptions(item_key, commonItem);
            itemHtml += '</div></div>';
        }
        itemHtml += '<div class="clear"></div><div class="tishinr ml30" style="min-height: 0px;"></div><div class="clear"></div>';
        itemHtml += '</div>';

        item_key++;
    }
    var tpl_item = jQuery.validator.format($.trim(itemHtml));
    $("#other_form_items").append(tpl_item);
    // bootstrap提示行
    $('[data-toggle="tooltip"]').tooltip();
}
// 生成相应栏位选项
function renderFormItemOptions(i, commonItem) {
    itemsHtml = ''
    if (commonItem.subitems != null && commonItem.subitems.length > 0) {
        for (var j = 0; j < commonItem.subitems.length; j++) {
            itemsHtml += '<div id="'+i+'_'+j+'"><input type="text" class="form_required form-control width110 ym_form_signup_field_required" placeholder="选项" name="items[' + i + '][options][' + j + ']" value="' + (commonItem.subitems[j] == null ? "" : commonItem.subitems[j].replace("\"", "\\\"").replace("\n", " ")) + '" />';
            if(j>1) {
                itemsHtml += '<span name="event_form_item_ctrl" class="btn-delete-options" onclick="javascript:removeFormItemOption(' + i + ',' + j + ');"></span>';
            }
            itemsHtml += '</div>';
        }
    }
    if(commonItem.ym_type != 'sex') {
        itemsHtml += '<button type="button" class="btn-add-options" onclick="javascript:addFormItemOption(' + i + ');return false;"><span class="" name="event_form_item_ctrl"></span></button>'
    }
    return itemsHtml;
}
// 删除一个选项
function removeFormItemOption(index, subIndex) {
    if (index >= 0 && subIndex >= 0) {
        if (confirm('您已填写的信息将无法恢复, 确认要删除此选项吗？')) {
            $('#'+index+'_'+subIndex).remove();
        }
    }
}
// 增加一个选顶
function addFormItemOption(index) {
    if (index >= 0) {
        var efis = $('#fio_' + index);
        if (efis != null) {
            j = Math.floor(Math.random() * (10000 - 1000 + 1)) + 1000;

            optionHtml = '<div id="'+index+'_'+j+'"><input type="text" class="form_required form-control width110 ym_form_signup_field_required" placeholder="选项" name="items[' + index + '][options]['+j+']" value="" />';
            optionHtml += '<span name="event_form_item_ctrl" class="btn-delete-options" onclick="javascript:removeFormItemOption(' + index + ', '+j+');"></span></div>';

            var tpl_new_option = jQuery.validator.format($.trim(optionHtml));
            $('#fio_' + index + ' .btn-add-options').before(tpl_new_option);
        }
    }
}
// 增加常用和自定义栏位
function addFormCommonItem(index) {
    if (formCommonItems != null && formCommonItems.length > index && index >= 0) {
        var commonItem = formCommonItems[index];
        if (commonItem != null) {
            renderFormItemTemplate(commonItem);
        }
    }
}
// 删除栏位
function removeFormItem(index) {
    //$(this).parents('.ym_form_item').remove();
    if (confirm('您已填写的信息将无法恢复, 确认要删除此栏位吗？')) {
        $('#fi_'+index).remove();
    }
}

$(document).ready(function () {

    // 删除承办机构及活动流程
    $('body').on('click', '.ym_remove', function(){
        if(confirm('删除后内容将无法恢复，确认删除吗？')) {
            $(this).parents('.ym_form_field').next('.tishinr').remove();
            $(this).parents('.ym_form_field').remove();
        }
        return false;
    });

    // 初始化ueditor
    var ue = UE.getEditor('ym_editor',{
        initialFrameHeight:450,
        serverUrl : ueditor_server_url
    });

    /**
     * 活动流程 操作
     */
    var i_flow = $('.op_flow').size();
    var tpl_flow = jQuery.validator.format($.trim($('#form_field_signup_flow').html()));
    $('body').on('click', '#btn-flow-add', function(){
        $('#flow_list').append(tpl_flow(i_flow++));
        // 活动流程开始时间, 结束时间
        $('#ym_dtbox').DateTimePicker({
            addEventHandlers: function() {
                var dtPickerObj = this;
                $('body').on('click', '.datePicker .pickerInput', function(e){
                    e.stopPropagation();
                    dtPickerObj.showDateTimePicker($(this));
                });
            }
        });
    });
    // 生成活动流程工作流
    function renderFlowTemplate(items) {
        // 活动流程开始时间, 结束时间
        $('#ym_dtbox').DateTimePicker({
            addEventHandlers: function() {
                var dtPickerObj = this;
                $('body').on('click', '.datePicker .pickerInput', function(e){
                    e.stopPropagation();
                    dtPickerObj.showDateTimePicker($(this));
                });
            }
        });
        if(items.length > 0) {
            html = '';
            $.each(items, function(k, v){
                if(v){
                    html += '<div class="row op_flow ym_form_field mb20">';
                    html += '<div class="pull-left mt7 pdlf10">名称：</div>';
                    html += '<div class="pull-left width200 ml12">';
                    html += '<input type="text" class="form-control ym_form_field_required" name="op_flow['+k+'][title]" placeholder="名称限20字" maxlength="20" value="'+ v.title+'">';
                    html += '</div>';

                    html += '<div class="pull-left mt7 pdlf10">时间：</div>';
                    html += '<div class="pull-left width190">';
                    html += '<div class="input-group date form_datetime datePicker">';
                    html += '<input type="text" readonly="" data-field="datetime" id="op_flow['+k+'][start_time]" name="op_flow['+k+'][start_time]" value="'+ v.start_time+'" size="16" class="form-control valid pickerInput ym_form_field_required" aria-required="true" aria-invalid="false">';
                    html += '<label class="input-group-addon radius0" for="op_flow['+k+'][start_time]"><span class="glyphicon glyphicon-th"></span></label>';
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="pull-left mt7 mr10 pdlf10">至</div>';
                    html += '<div class="pull-left width190">';
                    html += '<div class="input-group date form_datetime">';
                    html += '<input type="text" readonly="" data-field="datetime" id="op_flow['+k+'][end_time]" name="op_flow['+k+'][end_time]" value="'+ v.end_time+'" size="16" class="form-control pickerInput ym_form_field_required">';
                    html += '<label class="input-group-addon radius0" for="op_flow['+k+'][end_time]"><span class="glyphicon glyphicon-th"></span></label>';
                    html += '</div>';
                    html += '</div>';

                    html += '<div class="pull-left"><button type="button" class="ym_remove btn btn-delete"><i class="glyphicon glyphicon-trash"></i></button></div>';

                    html += '<div class="pull-left mt16 pdlf10">内容：</div>';
                    html += '<div class="pull-left width500 mt9 ml12">';
                    html += '<input type="text" name="op_flow['+k+'][content]" class="form-control ym_form_field_required" placeholder="内容限50字" maxlength="50" value="'+ v.content+'">';
                    html += '</div>';
                    html += '</div>';
                }
            });
            var tpl_flow_list = jQuery.validator.format($.trim(html));
            $('#flow_list').append(tpl_flow_list);
			i_flow = $('.op_flow').size();
        }
    }
    renderFlowTemplate(flow_items);



    /**
     * 承办机构 操作
     * @type {*|jQuery}
     */

    var i_undertaker = $('.op_undertaker').size();
    var tpl_undertaker = jQuery.validator.format($.trim($('#form_field_signup_undertaker').html()));
    // 生成承办机构项
    $('body').on('click', '#btn-undertaker-add', function(){
        $('#undertaker_list').append(tpl_undertaker(i_undertaker++));
    });

    // 生成承办机构
    function renderUndertakerTemplate(items) {
        if(items.length > 0) {
            html = '';
            $.each(items, function(k, v){
                if(v){
                    html += '<div class="row op_undertaker ym_form_field">';
                    html += '<div class="pull-left btn-group width150 ml12">';
                    html += '<select class="form-control" name="op_undertaker['+k+'][type]">';
                    html += '<option value="1" '+(v.type=='1'?'selected':'')+'>主办方</option>';
                    html += '<option value="2" '+(v.type=='2'?'selected':'')+'>承办方</option>';
                    html += '<option value="3" '+(v.type=='3'?'selected':'')+'>协办方</option>';
                    html += '</select>';
                    html += '</div>';
                    html += '<div class="pull-left width420 ml12">';
                    html += '<textarea type="text" class="form-control op_undertaker ym_form_field_required" rows="3" name="op_undertaker['+k+'][name]" placeholder="">'+ v.name +'</textarea>';
                    html += '</div>';
                    html += '<div class="pull-left"><button type="button" class="btn btn-delete ym_remove"><i class="glyphicon glyphicon-trash"></i></button></div>';
                    html += '</div>';
                    html += '<div class="pull-left mb20 tishinr" style="min-height: 0px;"></div><div class="clear"></div>';
                }
            });
            var tpl_undertaker_list = jQuery.validator.format($.trim(html));
            $('#undertaker_list').append(tpl_undertaker_list);
			i_undertaker = $('.op_undertaker').size();
        }
    }
    renderUndertakerTemplate(undertaker_items);


    // 新增验证插件 - 报名结束时间不得早于开始时间
    $.validator.addMethod("before_signup_start", function(value, element) {
        var signup_start =  new Date($('#start').val().replace(/-/g,"/")).getTime();
        var signup_end =  new Date($('#end').val().replace(/-/g,"/")).getTime();
        if(!signup_end || !signup_start) return true;
        return signup_start < signup_end;
    }, "报名结束时间不得早于开始时间");
    // 新增验证插件 - 报名结束时间不得晚于结束时间
    $.validator.addMethod("before_signup_end", function(value, element) {
        var activity_end =  new Date($('#endTime').val().replace(/-/g,"/")).getTime();
        var signup_end =  new Date($('#end').val().replace(/-/g,"/")).getTime();
        if(!signup_end) return true;
        if(signup_end < activity_end) return true;
        // return activity_end < signup_end;
    }, "报名结束时间不得晚于活动结束时间");
    // 经度玮度必填验证
    $.validator.addMethod("lat_lng_required", function(value, element) {
        return value!='';
    }, "纬度和经度必须通过搜索目的地坐标获取");
    // 各种JS动态添加的选项验证
    $.validator.addMethod("ym_form_field_required", function(value, element) {
        return (value=='') ? false : true;
    }, "此项必填");
    // 报名活动报名表单选项验证
    $.validator.addMethod("ym_form_signup_field_required", function(value, element) {
        return (value=='') ? false : true;
    }, "选项名称和选项均必填");

    ue.addListener('contentChange',function(){
        value = ue.getContentLength(true);
        if(value < 1 || value > 10000){
            $("#editor_error").html('<strong>内容不能为空，且不能多于10000个字</strong>');
        }else{
            $("#editor_error").html('');
        }
    });

    // 报名活动表单提交
    $('form#actForm').submit(function(){

        if($('#ticket_list tr').length < 1) {
            alertModal('票务必须设置。');
            return false;
        }

        if(ue.getContentLength(true) < 1 || ue.getContentLength(true) > 10000){
            $("#editor_error").html('<strong>内容不能为空，且不能多于10000个字</strong>');
            alertModal('内容不能为空，且不能多于10000个字');
            return false;
        }
        UE.getEditor('ym_editor').sync();

    }).validate({
        ignore: '',
        errorPlacement: function(error, element){
            element.parents('.ym_form_field').next('.tishinr').html(error);
            element.parents('.ym_form_field').find('.tishinr').html(error);
        },
        rules: {
            name: {
                required: true,
                rangelength: [2, 50]
            },
            startTime: {
                required: true
            },
            endTime: {
                required: true,
                afterstart: true
            },
            areaid_1: {
                required: true
            },
            areaid_2: {
                required: true
            },
            address: {
                required: true,
                rangelength: [5, 100]
            },
            //content: {
            //    required: true,
            //    rangelength:[2,200000]
            //},
            end : {
                before_signup_start: true,
                before_signup_end: true
            },
            lat : {
                lat_lng_required: true
            },
            lng: {
                lat_lng_required: true
            }
        },
        messages: {
            name: {
                required: "名称不能为空",
                rangelength: "名称不得少于两个字，不得多于五十个字"
            },
            startTime: {
                required: "开始时间不能为空"
            },
            endTime: {
                required: "结束时间不能为空"
            },
            areaid_1: {
                required: "区域/详细地址不能为空"
            },
            areaid_2: {
                required: "区域/详细地址不能为空"
            },
            address: {
                required: "区域/详细地址不能为空",
                rangelength: "详细地址不得少于5个字，不得多于100个字"
            }
            //content: {
            //    required: "活动内容不能为空",
            //    rangelength: "活动内容不能少于两个字或不能多于一万个字"
            //}
        }
    }).focusInvalid = function(){
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

    // 区域触发
    $('#area1').change(function(){
        var id1 = $(this).val();
        if(id1 == '') {
            $('#area2').html('<option value="">市/区</option>');
            return false;
        }
        $('#val').val(id1);
        $.ajax({
            type: 'POST',
            url: ajax_area_url,
            data: {id: id1},
            dataType: "json",
            beforeSend: function(){
                $('#area2').after('<i id="loading" class="fa fa-spinner fa-spin ml12"></i>');
            },
            success: function(data){
                if(data.status=='ok'){
                    $('#loading').remove();
                    //                    var html = '<option value=""></option>';
                    var html = '';
                    $.each(data.data, function(k, v){
                        html += '<option value="'+v.id+'">'+v.name+'</option>';
                    });
                    $('#area2').html(html);

                    $('#area2').on('change', function(){
                        var id2 = $(this).val();
                        $('#val').val(id1+','+id2);
                    });
                }else{
                    $('#loading').remove();
                    alert(data.msg);
                }
            }
        });
    });


});