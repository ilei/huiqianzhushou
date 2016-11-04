$(document).ready(function(){

    /**
     * 立即发布
     */
    $('.publish_now').click(function(){
        if(!confirm('确定立即发布该活动吗？')){
            return false;
        }
        var aid = $(this).parents('.act-list-item').find('input[name=aid]').val(); // 活动guid
        if(aid.length != 32){
            alertTips($('#tips-modal'), '参数错误，发布失败，请重试。');
        }
        var data = {'aid': aid};
        var obj = $(this);

        $.ajax({
            url: YM['url_publish_now'],
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function(){
                obj.button('loading');
            },
            success: function(data){
                if(data.status=='ok'){
                    obj.parents('.act-list-item').find('.item-timerel').text(data.activity_status);
                    obj.remove();
                }
                alertTips($('#tips-modal'), data.msg);
            },
            complete:function(){
                obj.button('reset');
            }
        });
    });

    /**
     * 搜索
     */
    $('#btn_search').click(function(e){
        var keyword = $('#keyword').val();
        if(keyword == '') {
            alertTips($('#tips-modal'), '请填入要搜索的关键词。');return false;
        }
        location.href = YM['url_reset'] + '/k/' + keyword;
    });

    /**
     * 重置
     */
    $('#btn_reset').click(function(e){
        location.href = YM['url_reset'];
    });

    /**
     * 过滤
     */
    function activity_filter() {
        var filter_status = $('input#filter_status_val').val();
        var filter_type = $('input#filter_type_val').val();
        var keyword = $('input#keyword').val();
        var url = YM['url_reset'];
        if(keyword != '') {
            url += '/k/' + keyword;
        }
        if(filter_status != '') {
            url += '/s/' + filter_status;
        }
        if(filter_type != '') {
            url += '/t/' + filter_type;
        }
        location.href = url;

    }
    $('#filter_status a').click(function(){
        var filter_status_val = $(this).attr('data-status');
        $('input#filter_status_val').val(filter_status_val);
        activity_filter();
    });
    $('#filter_type a').click(function(){
        var filter_type_val = $(this).attr('data-type');
        $('input#filter_type_val').val(filter_type_val);
        activity_filter();
    });

    /**
     * 从该主题下删除此活动
     */
    $('.delete_from_subject').click(function(){
        if(!confirm('确定从该主题下移出该活动吗？')){
            return false;
        }
        var aid = $(this).parents('.act-list-item').find('input[name=aid]').val(); // 活动guid
        var sid = YM['subject_guid'];
        if(aid.length != 32 || sid.length != 32){
            alertTips($('#tips-modal'), '参数错误，发布失败，请重试。');
        }
        var data = {'aid': aid, 'sid':sid};
        var obj = $(this);

        $.ajax({
            url: YM['url_delete_from_subject'],
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function(){
                obj.button('loading');
            },
            success: function(data){
                if(data.status=='ok'){
                    obj.parents('.act-list-item').remove();
                }
                alertTips($('#tips-modal'), data.msg);
            },
            complete:function(){
                obj.button('reset');
            }
        });
    });
});