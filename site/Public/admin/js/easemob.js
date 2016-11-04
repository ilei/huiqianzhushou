/**
 * 环信数据修复
 */
$(function () {

    /**
     * 修复单个用户
     * CT: 2015-06-12 10:50 by ylx
     */
    $('.ym_easemob_single_fix').click(function () {
        var obj = $(this);
        var guid = obj.attr('data-guid');
        $.ajax({
            url: YM['url_easemob_single_user'],
            type: 'POST',
            data: { guid: guid },
            dataType: 'json',
            beforeSend: function () {
                obj.button('loading');
            },
            success: function (data) {
                alertTips($('#tips-modal'), data.msg);
                if (data.status == 'ok') {
                    var obj_easemob = obj.parents('.ym_table_tr').find('.ym_easemob_id');
                    obj_easemob.text('是');
                    obj_easemob.attr('title', data.easemob_id);
                    obj_easemob.attr('data-original-title', data.easemob_id);
                    obj.remove();
                }
            },
            complete: function () {
                obj.button('reset');
            }
        });
    });

    /**
     * 修复单个用户到群组
     * CT: 2015-06-15 10:50 by ylx
     */
    $('.ym_easemob_group_user_fix').click(function () {
        var obj = $(this);
        var user_guid = obj.attr('data-user-guid');
        var chat_group_id = $('#chat_group_id').val();
        if(user_guid == '' || chat_group_id == '') {
            alertModal('参错误, 请刷新页面重试.');
            return false;
        }
        $.ajax({
            url: YM['url_easemob_group_user_fix'],
            type: 'POST',
            data: { user_guid: user_guid, chat_group_id: chat_group_id },
            dataType: 'json',
            beforeSend: function () {
                obj.button('loading');
            },
            success: function (data) {
                alertTips($('#tips-modal'), data.msg);
                if (data.status == 'ok') {
                    obj.parents('.ym_chat_fix').remove();
                }
            },
            complete: function () {
                obj.button('reset');
            }
        });
    });

    /**
     * 修复单个群组
     * CT: 2015-06-15 10:50 by ylx
     */
    $('.ym_easemob_group_fix').click(function () {
        var obj = $(this);
        var guid = obj.attr('data-guid');
        $.ajax({
            url: YM['url_easemob_group_fix'],
            type: 'POST',
            data: { guid: guid },
            dataType: 'json',
            beforeSend: function () {
                obj.button('loading');
            },
            success: function (data) {
                alertTips($('#tips-modal'), data.msg);
                if (data.status == 'ok') {
                    if($('#ym_page_type').val()) {
                        location.reload();
                    } else {
                        var obj_easemob = obj.parent().find('.ym_chat_group_id');
                        obj_easemob.text('是');
                        obj_easemob.attr('title', data.chat_group_id);
                        obj_easemob.attr('data-original-title', data.chat_group_id);
                        obj.remove();
                    }
                }
            },
            complete: function () {
                obj.button('reset');
            }
        });
    });
})