
$(document).ready(function(){
    /**
     * 票务相关
     * @type {*|jQuery}
     */
    // 打开新增票务窗口
    $('body').on('click', '#btn-ticket-add', function(){
        $('#modal_ticket_form')[0].reset();
        $('#is_new').val('1'); // 新增
        $('#modal_ticket').modal();
    });

    // 确认票务已填信息
    $('body').on('click', '#modal_ticket_save', function(){
        var i_ticket = $.now();
        var t_name = $('#ticket-name').val(),
            t_price = $('#ticket-price').val(),
            t_num = $('#ticket-num').val(),
            t_verifynum = $('#ticket-verifynum').val(),
            t_forsale =  ($('#ticket-forsale:checked').val() == '1' ? 1 : 0),
            is_new = $('#is_new').val(),
            ticket_key = $('#ticket_key').val();
        var t_forsale_text = (t_forsale==1?'是':'否');

        // 验证
        var is_pass = true;
        if(t_name == '') {
            $('#ticket-name').parents('.ym_form_field').find('.error-pr').html('此项不能为空');
            is_pass = false;
        }
        //else { //检查该活动下是否有同名电子票
        //    $.ajax({
        //        url: ticket_check_url,
        //        type: 'POST',
        //        dataType: 'json',
        //        data: { aid: aid, name: t_name },
        //        success: function(data){
        //            if(data.status == 'ko') {
        //                $('#ticket-name').parents('.ym_form_field').find('.error-pr').html('该名称已存在');
        //                return false;
        //            }
        //        }
        //    });
        //}
        if(t_price == '') {
            $('#ticket-price').parents('.ym_form_field').find('.error-pr').html('此项不能为空');
            is_pass = false;
        }
        if(t_num == '') {
            $('#ticket-num').parents('.ym_form_field').find('.error-pr').html('此项不能为空');
            is_pass = false;
        }
        if(t_verifynum < 1 || t_verifynum > 30) {
            $('#ticket-verifynum').parents('.ym_form_field').find('.error-pr').html('验证次数必须为1-30之间');
            is_pass = false;
        }
        if(is_pass != true) return false;

        if(is_new == '2' && ticket_key != ''){ // 若为编辑，则更换值
            $('span.ticket_'+ticket_key+'_name').text(t_name);
            $('span.ticket_'+ticket_key+'_name').next().val(t_name);
            $('span.ticket_'+ticket_key+'_price').text(t_price);
            $('span.ticket_'+ticket_key+'_price').next().val(t_price);
            $('span.ticket_'+ticket_key+'_num').text(t_num);
            $('span.ticket_'+ticket_key+'_num').next().val(t_num);
            $('span.ticket_'+ticket_key+'_verifynum').text(t_verifynum);
            $('span.ticket_'+ticket_key+'_verifynum').next().val(t_verifynum);
            $('span.ticket_'+ticket_key+'_forsale').text(t_forsale_text);
            $('span.ticket_'+ticket_key+'_forsale').next().val(t_forsale);
            //$('input.ticket_'+ticket_key+'_forsale').prop('checked', t_forsale);
        } else if(is_new == '1') { // 若为新增， 则新添一行
            html = '<tr class="op_ticket">';
            html += '<td><span class="ticket_' + i_ticket + '_name">' + t_name + '</span><input type="hidden" name="op_ticket[new][' + i_ticket + '][name]" class="t_name" value="' + t_name + '"/></td>';
            html += '<td><span class="ticket_' + i_ticket + '_price">' + t_price + '</span><input type="hidden" name="op_ticket[new][' + i_ticket + '][price]" class="t_price" value="' + t_price + '"/></td>';
            html += '<td><span class="num_used">0</span>/<span class="ticket_' + i_ticket + '_num">' + t_num + '</span><input type="hidden" name="op_ticket[new][' + i_ticket + '][num]" class="t_num" value="' + t_num + '"/></td>';
            html += '<td><span class="ticket_' + i_ticket + '_verifynum">' + t_verifynum + '</span><input type="hidden" name="op_ticket[new][' + i_ticket + '][verify_num]" class="t_verifynum" value="' + t_verifynum + '"/></td>';
            //html += '<td><input id="switch-offText" data-on-color="success" type="checkbox" name="op_ticket[new][' + i_ticket + '][is_for_sale]" class="t_forsale ticket_' + i_ticket + '_forsale" value="1" data-size="small" data-on-text="是" data-off-text="否" ' + (t_forsale == 1 ? 'checked' : '') + ' /></td>';
            html += '<td><span class="ticket_' + i_ticket + '_forsale">' + t_forsale_text + '</span><input name="op_ticket[new][' + i_ticket + '][is_for_sale]" class="t_forsale ticket_' + i_ticket + '_forsale" type="hidden" value="' + t_forsale +'"  /></td>';
            html += '<td><input type="hidden" name="op_ticket[new][' + i_ticket + '][guid]" value="" /><button type="button" class="btn bg-white radius0 btn-ticket-edit" ticket_key="' + i_ticket + '" title="设置"><i class="fa fa-cog fa-lg"></i></button><button type="button" class="btn bg-white btn-ticket-del" guid="is_new"><i class="glyphicon glyphicon-trash"></i></button></td>';
            html += '</tr>';
            html += '<div class="pull-left ml80 tishinr"></div>';
            i_ticket++;
            $('#ticket_list').append(html);
        }
        $('#modal_ticket').modal('hide');
        $('#modal_ticket_form')[0].reset();
    });

    // 清除错误提示
    $('form#modal_ticket_form input').blur(function(){
        var text_value=$(this).val();
        if(text_value !='') {
            $(this).parents('.ym_form_field').find('.error-pr').html('');
        }
    });

    // 打开票务编辑窗口
    $('body').on('click', '.btn-ticket-edit', function(){
        parent = $(this).parents('tr');
        var t_name = parent.find('.t_name').val(),
            t_price = parent.find('.t_price').val(),
            t_num = parent.find('.t_num').val(),
            t_verifynum = parent.find('.t_verifynum').val(),
            //t_forsale =  (parent.find('.t_forsale:checked').val() == '1' ? true : false),
            t_forsale = (parent.find('.t_forsale').val() == '1' ? true : false);

        // 为模态窗内容赋值
        $('#ticket-name').val(t_name);
        $('#ticket-price').val(t_price);
        $('#ticket-num').val(t_num);
        $('#ticket-verifynum').val(t_verifynum),
        $('#ticket-forsale').prop('checked', t_forsale);

        $('#is_new').val('2'); // 编辑
        $('#ticket_key').val($(this).attr('ticket_key')); // 编辑

        // open modal
        $('#modal_ticket').modal();
    });

    // 删除票务
    $('body').on('click', '.btn-ticket-del', function () {
        if(!confirm('删除后内容将无法恢复，确认删除吗？')) {
            return false;
        }
        var obj = $(this);
        var guid = obj.attr('guid');
        // 如果是新增的，则直接删除
        if(guid == 'is_new') {
            obj.after('<i id="del_ticket_loading" class="fa fa-spinner fa-spin"></i>');
            obj.parents('tr').remove();
            return false;
        }
        // 如果是已保存的，则ajax访问数据库删除
        $.ajax({
            url: ticket_del_url,
            type: 'POST',
            dataType: 'json',
            data: { tid: obj.attr('guid') },
            beforeSend: function(){
                obj.after('<i id="del_ticket_loading" class="fa fa-spinner fa-spin"></i>');
            },
            success: function(data){
                $('#del_ticket_loading').remove();
                if(data.status == 'ok') {
                    obj.parents('tr').remove();
                } else {
                    alertTips($('#tips-modal'), data.msg);
                }
            }
        });
    });
    // 生成票务列表
    function renderTicketTemplate(items) {
        if(items.length > 0) {
            html = '';
            $.each(items, function(k, v){
                html += '<tr class="op_ticket">';
                html += '<td><span class="ticket_' + k + '_name">' + v['name'] + '</span><input type="hidden" name="op_ticket[old][' + k + '][name]" class="t_name" value="' + v['name'] + '"/></td>';
                html += '<td><span class="ticket_' + k + '_price">' + v['price'] + '</span><input type="hidden" name="op_ticket[old][' + k + '][price]" class="t_price" value="' + v['price'] + '"/></td>';
                html += '<td><span class="num_used">' + v['num_used'] + '</span>/<span class="ticket_' + k + '_num">' + v['num'] + '</span><input type="hidden" name="op_ticket[old][' + k + '][num]" class="t_num" value="' + v['num'] + '"/></td>';
                html += '<td><span class="ticket_' + k + '_verifynum">' + v['verify_num'] + '</span><input type="hidden" name="op_ticket[old][' + k + '][verify_num]" class="t_verifynum" value="' + v['verify_num'] + '"/></td>';
                //html += '<td><input id="switch-offText" data-on-color="success" type="checkbox" name="op_ticket[old][' + k + '][is_for_sale]" class="t_forsale ticket_' + k + '_forsale" value="1" data-size="small" data-on-text="是" data-off-text="否" ' + (v['is_for_sale'] == 1 ? 'checked' : '') + ' readonly /></td>';
                html += '<td><span class="ticket_' + k + '_forsale">' + (v['is_for_sale'] == 1 ? '是' : '否') + '</span><input name="op_ticket[old][' + k + '][is_for_sale]" class="t_forsale ticket_' + k + '_forsale" type="hidden" value="' + (v['is_for_sale'] == 1 ? 1 : 0)+'"  /></td>';
                html += '<td><input type="hidden" name="op_ticket[old][' + k + '][guid]" value="'+v['guid']+'" /><button type="button" class="btn bg-white radius0 btn-ticket-edit" ticket_key="' + k + '" title="设置"><i class="fa fa-cog fa-lg"></i></button><button type="button" class="btn bg-white btn-ticket-del" guid="'+v['guid']+'"><i class="glyphicon glyphicon-trash"></i></button></td>';
                html += '</tr>';
                html += '<div class="pull-left ml80 tishinr"></div>';
            });
            $('#ticket_list').append(html);
        }
    }
    renderTicketTemplate(ticket_items);

});

