define(['jquery','lang','messageControl','validate', 'pagerControlClient'], function($,lang,messager,_,pagerClient){

    //设置Document域
    document.domain = $("#document_domain").val();

    //公用数据———————————————————————————————START
    //初始默认值
    var t = 'all';//电子票的guid  ym_activity_attr_ticket电子票名称
    var keyword = '';//关键字搜索姓名，手机号
    var s = 'all';//签到状态
    var p = '1';//页数
    var i = '10';//当页显示条数
    var ajax_userinfo_url = $("#ajax_userinfo_url_hidden").val();//action地址
    var ck_user_guids = new Array();
    var download_other_data_url = $("#download_other_data").attr('href');//下载筛选后报名信息地址
    var email_not_full = $("#hidden_email").val();//邮箱必填
    var data_page_status = '';//数据分页状态
    var activity_status_hidden = $("#activity_status_hidden").val();//活动状态
    var ticket_guid = '';//电子票guid
    var update_data_url = $("#hidden_update_user_info_url").val();//修改信息url
    //var user_info_list =JSON.parse($('#hidden_user_info_list').val());//报名用户详情列表
    //console.log(eval($('#hidden_user_info_list').val()));

    //公用数据———————————————————————————————END

    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    //发送电子票——————————————————————————————START
    $("#btn_send_other_sms").click(function(){
            //准备数据
            //var server_url = ("#ajax_send_ticket").val(); //后台地址
            var activity_guid = $("#activity_guid_hidden").val();
            var activity_name = $("#activity_name_hidden").val();
            var data = new Array();
            //if(email_not_full == '1'){
            //    data['send_way'] = ['sms','email'];//短信/邮件
            //    //data['send_way'][1] = 'email';//
            //}else{
            data['send_way'] = ['sms'];
            //}
            var data = {target:'other',send_type:'sms',aid:activity_guid,aname:activity_name,send_way:data['send_way']};
            var obj = $(this);

        //alertConfirm('确定此操作？',sendserver(data,obj));
            //调后台
            sendserver(data,obj);

    });
    $("#btn_send_all_sms").click(function(){
            //准备数据
            //var server_url = ("#ajax_send_ticket").val(); //后台地址
            var activity_guid = $("#activity_guid_hidden").val();
            var activity_name = $("#activity_name_hidden").val();
            var data = new Array();
            data['send_way'] = ['sms'];
            var data = {target:'all',send_type:'sms',aid:activity_guid,aname:activity_name,send_way:data['send_way']};
            var obj = $(this);

            //调后台
            sendserver(data,obj);

    });
    $("#btn_send_other_email").click(function(){
        //准备数据
        //var server_url = ("#ajax_send_ticket").val(); //后台地址
        var activity_guid = $("#activity_guid_hidden").val();
        var activity_name = $("#activity_name_hidden").val();
        var data = new Array();
        data['send_way'] = ['email'];//短信/邮件
            //data['send_way'][1] = 'email';//
        var data = {target:'other',send_type:'email',aid:activity_guid,aname:activity_name,send_way:data['send_way']};
        var obj = $(this);

        //调后台
        sendserver(data,obj);

    });
    $("#btn_send_all_email").click(function(){
            //准备数据
            //var server_url = ("#ajax_send_ticket").val(); //后台地址
            var activity_guid = $("#activity_guid_hidden").val();
            var activity_name = $("#activity_name_hidden").val();
            var data = new Array();
            //if(email_not_full == '1'){
            //    data['send_way'] = ['sms','email'];//短信/邮件
            //    //data['send_way'][1] = 'email';//
            //}else{
            data['send_way'] = ['email'];
            //}
            var data = {target:'all',send_type:'email',aid:activity_guid,aname:activity_name,send_way:data['send_way']};
            var obj = $(this);

            //调后台
            sendserver(data,obj);

    });
    //发送短信或邮件
    function sendserver(data,obj){
        //if(confirm('确定此项操作？') == true){
            //alertConfirm('确定此项操作？');
            var url = $("#ajax_send_ticket").val();   //后台地址
            var activity_info_url = $("#activity_info_url").val();

        console.log('到这了');
            //alertConfirm(
        //    '确定此项操作？',
        //    function(){
        //        $.ajax({
        //            url: url,
        //            type: 'POST',
        //            data: {data:data},
        //            dataType: 'json',
        //            beforeSend: function () {
        //                //obj.button('loading');
        //                //obj.parent().append('<i id="loading" class="fa fa-spinner fa-spin fa-2x" style="margin: 2px 5px;"></i>');
        //            },
        //            success: function (data) {
        //                if (data.status == 'ok') {
        //                    //$('form#form_send')[0].reset();
        //                    alertTips($('#tips-modal'), data.msg,activity_info_url);
        //                } else if (data.status == 'ko') {
        //                    alertTips($('#tips-modal'), data.msg);
        //                }
        //            }
        //        })
        //    }
        //);

        messager.show({
            type:"confirm",
            title:"操作确认",
            content:"确定此项操作？",
            //buttonTitle:"认证"
        }, function (type) {
            if(type==="submit"){
                $("#email_build").attr('disabled', true);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {data:data},
                    dataType: 'json',
                    beforeSend: function () {
                        //obj.button('loading');
                        //obj.parent().append('<i id="loading" class="fa fa-spinner fa-spin fa-2x" style="margin: 2px 5px;"></i>');
                            //等待图标
                        $("#loading_css").attr('class','loading_css');
                        $("#spinner").attr('class','spinner');
                    },
                    success: function(data){
                        $("#loading_css").removeAttr('class','loading_css');
                        $("#spinner").removeAttr('class','spinner');
                        if(data != ''){
                            console.log(data);
                            console.log('987654321');
                            if(data.status == 'ok'){
                                messager.show({
                                    type: "alert",
                                    title: "操作确认",
                                    content: data.msg,
                                    autoClose: true
                                },function(){
                                    location.href = activity_info_url;
                                });
                            }else{
                                console.log('123456789');
                                messager.show({
                                    type: "alert",
                                    title: "操作确认",
                                    content: data.msg,
                                    autoClose: true
                                });
                                //alertTips($('#tips-modal'), data.msg);
                            }
                            uid = '';
                        }
                    }
                });
            }
        });
        //}
    }

    //发送短信邮件验证——————————————————————————————————————————START
    $(".myModaltongzhi_button").click(function(){   //#########################待验证
        $("#num_sms").text(ck_user_guids.length);
        $("#num_email").text(ck_user_guids.length);
        $("#checked_user_count").text(ck_user_guids.length);
    });

    $(".myModaltongzhi_button").click(function(){
        //if(confirm('确定此项操作？') == true){
            //准备数据
            //var server_url = ("#ajax_send_ticket").val(); //后台地址
            var activity_guid = $("#activity_guid_hidden").val();
            var activity_name = $("#activity_name_hidden").val();
            var data = new Array();
            data['data_user_guids'] = ck_user_guids;
            //data['send_type'] = $("input[name = 'send_way[]']").val();
            if($(this).attr('v') == 'sms'){
                data['send_way'] = ['sms'];//短信/邮件
                //data['send_way'][1] = 'email';//
            }else{
                data['send_way'] = ['email'];
            }
            data['aid'] = activity_guid;
            data['aname'] = activity_name;
            var obj = $(this);
            //data['ticket_content'] = $("#ticket-content").val();//后台做html处理。。。。。短信或邮件内容
            //data['ticket_signature'] = $("#ticket-signature").val();//后台做html处理。。。。。  短信签名

            //调后台
            var url = $("#ajax_send_ticket").val();   //后台地址
            var activity_info_url = $("#activity_info_url").val();

            var send_data = {
                'aid':activity_guid,
                'aname':activity_name,
                'data_user_guids':data['data_user_guids'],//
                'send_way':data['send_way'],//后台重新赋值
                //'ticket_content':data['ticket_content'],
                //'ticket_signature':data['ticket_signature'],
            }
        messager.show({
            type:"confirm",
            title:"操作确认",
            content:"确定此项操作？",
            //buttonTitle:"认证"
        }, function (type) {
            if(type==="submit"){
                $("#email_build").attr('disabled', true);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {data:send_data},
                    dataType: 'json',
                    beforeSend: function () {
                        obj.button('loading');
                        //obj.parent().append('<i id="loading" class="fa fa-spinner fa-spin fa-2x" style="margin: 2px 5px;"></i>');
                    },
                    //dataType: 'json',
                    success: function(data){
                        if(data != ''){
                            console.log(data);
                            if(data.status == 'ok'){
                                messager.show({
                                    type: "alert",
                                    title: "操作确认",
                                    content: data.msg
                                    //,
                                    //autoClose: true
                                },function(){
                                    //location.href = activity_info_url;
                                    obj.button('reset');
                                    ajax_ticket_list(ajax_userinfo_url);
                                });
                                //alertTips($('#tips-modal'), data.msg,activity_info_url);
                            }else{
                                messager.show({
                                    type: "alert",
                                    title: "操作确认",
                                    content: data.msg
                                    //,
                                    //autoClose: true
                                },function(){
                                    ajax_ticket_list(ajax_userinfo_url);
                                });
                                //alertTips($('#tips-modal'), data.msg);
                            }
                            uid = '';
                        }
                    }
                });
            }
        });
            //alertConfirm(
            //    '确定此项操作？',
            //    function(){
            //        $.ajax({
            //            url: url,
            //            type: 'POST',
            //            data: {data:send_data},
            //            dataType: 'json',
            //            beforeSend: function () {
            //                obj.button('loading');
            //                //obj.parent().append('<i id="loading" class="fa fa-spinner fa-spin fa-2x" style="margin: 2px 5px;"></i>');
            //            },
            //            success: function (data) {
            //                console.log(data);
            //                if (data.status == 'ok') {
            //                    //$('form#form_send')[0].reset();
            //                    alertTips($('#tips-modal'), data.msg,activity_info_url);
            //                } else if (data.status == 'ko') {
            //                    alertTips($('#tips-modal'), data.msg);
            //                }
            //            }
            //        })
            //    }
            //
            //);

        //}
    });
    // 选择性发送电子票/通知 表单验证

    //发送短信邮件验证——————————————————————————————————————————END
    //发送电子票——————————————————————————————END

    //导出数据——————————————————————————————START
    //导出选中
    $("#download_checked_data").click(function(){
        if($("#checked_guids").val() == ''){
            //alert('请选择要导出的数据');
            messager.show({
                type: "alert",
                title: "提示框",
                content: "请选择要导出的数据",
                autoClose: true
            });
            //alertModal('请选择要导出的数据');
        }else{
            $("#checked_export").submit();
        }
    });
    //导出数据——————————————————————————————END

    // 弹出modal框
    function alertModal(msg, obj)
    {
        if(!obj) {
            obj = $('#tips-modal');
        }
        obj.modal('show');
        obj.find('.tips-msg').html(msg);
    }

    // 弹出报名用户信息
    function alertUserViewModal(msg, obj)
    {
        if(!obj) {
            obj = $('#user-view-modal');
        }
        obj.modal('show');
        obj.find('.user-view-modal-body').html(msg);
    }

    // 弹出确认对话框
    function alertConfirm(msg, ajax) {
        var obj = $('#confirm-modal');
        obj.modal('show');
        obj.find('.tips-msg').html(msg);
        //解除绑定事件
        $("#confirm_no").unbind('click');
        $("#confirm_yes").unbind('click');

        $('#confirm_no').click(function(){
            obj.modal('hide');
        });
        $('#confirm_yes').click(function(){
            if(ajax && ajax instanceof Function){
                ajax();
            }
        });
    }
    /**
     * 弹出提示框
     * CT: 2014-12-02 10:50 by QXL
     */
    function alertTips(obj,msg,url){
        obj.modal('show');
        obj.find('.tips-msg').html(msg);
        var t=setTimeout(function(){
            if(url){
                location.href=url;
            }else{
                obj.modal('hide');
            }
            clearTimeout(t);
        },1800);

        $('#tips-modal').on('hidden.bs.modal', function (e) {
            if(url){
                location.href=url;
            }else{
                obj.modal('hide');
            }
        })
    }

    //票务状态筛选————————————————————————————————————————START

    //签到状态筛选
    $("#ticket_status").find("a").click(function(){
        //组装数据
        p = '1';//默认第一页
        s = $(this).attr("ticket_type");
        ////导出筛选条件拼接
        //download_other_data_url = download_other_data_url + '/s/' + s;

        //调用后台
        ajax_ticket_list(ajax_userinfo_url);

    });

    //票务分类筛选
    $("#ticket_fiter").find('a').click(function(){

        //组装数据
        p = '1';
        t = $(this).attr('value');
        ////导出筛选条件拼接
        //download_other_data_url = download_other_data_url + '/t/' + t;

        //调用后台
        ajax_ticket_list(ajax_userinfo_url);
    });

    //设置分页控件监视 只设置一次
    pagerClient.config({
        element: "#pager_container",
        sizer: function (ps) {
            //更新pageSize
            i = ps;
            p = '1';
            data_page_status = '1';
            ajax_ticket_list(ajax_userinfo_url);
        },
        pager: function (page) {
            //更新当前页码
            p = page;
            ajax_ticket_list(ajax_userinfo_url);
        }
    });

    //关键字搜索
    $("#btn_search").click(function(){
        //
        p = '1';
        keyword = $("#search").val();

        //删除链接后缀
        var download_other_url = download_other_data_url.substring(0,download_other_data_url.indexOf("/keyword/"));
        if(download_other_url == ''){
            //导出筛选条件拼接
            $("#download_other_data").attr('href',download_other_data_url + '/keyword/' + keyword);
        }else{
            //导出筛选条件拼接
            $("#download_other_data").attr('href',download_other_url + '/keyword/' + keyword);
        }

        $("#btn_search_reset").show();

        //调用后台
        ajax_ticket_list(ajax_userinfo_url);
    });

    //筛选后提交地址
    function ajax_ticket_list(url){
        var aid = $("#activity_guid_hidden").val();//活动guid

        //组装数据
        var filters = {'aid':aid,'t':t,'keyword':keyword,'s':s,'p':p,'i':i}//t:票类型 keyword:要搜索的关键字 s:签到状态 p:跳转的页数 i:当页显示条数

        $.ajax({
            url: url,
            type: "POST",
            data: {filters:filters},
            beforeSend: function(){
                //等待图标
                $("#loading_css").attr('class','loading_css');
                $("#spinner").attr('class','spinner');
            },
            success: function(data){
                console.log(data);
                //if(data.data != ''){
                    $("#user_list_tbody").html(data.data);
                    $("#surplus_count").text(data.count);
                    $("#user_count").text(data.user_count);
                    $("#pager_container").html(data.pager);
                    $("#ajax_ticket_name").text(data.ajax_ticket_name);//票种
                    $("#ajax_ticket_status").text(data.ajax_ticket_status);//电子票状态
                    $("#ckall").prop('checked',false);
                    console.log(data_page_status);
                    if(data_page_status != '1'){
                        ck_user_guids.splice(0);
                    }

                    $("#loading_css").removeAttr('class','loading_css');
                    $("#spinner").removeAttr('class','spinner');



                    //$("#user_list_tbody").find("input:checked").each(function(){
                    //    ck_user_guids.splice($.inArray($(this).val(),ck_user_guids),1);
                        ck_user_guids.splice(0,ck_user_guids.length);
                    //});

                    if(ck_user_guids.length == 0){
                        $(".myModaltongzhi_button").prop('disabled',true);
                        $("#not_status_myModaltongzhi_button").prop('disabled',true);
                        $("#ckall").prop('checked',false);
                    }
                    $("#ck_user_guid_count").text(ck_user_guids.length);
                //}else{
                //    //alert('没有相关数据');
                //    messager.show({
                //        type: "alert",
                //        title: "提示框",
                //        content: "没有相关数据",
                //        autoClose: true
                //    },function(){
                //        $("#loading_css").removeAttr('class','loading_css');
                //        $("#spinner").removeAttr('class','spinner');
                //
                //    });
                //    //alertModal('没有相关数据');
                //}
            }
        });
    }
    //票务状态筛选————————————————————————————————————————END

    // 回车触发点击———————————————————————————————————————START
    //keyword关键字搜索点击回车事件
    $("#search").keydown(function(e){
        press_target(e,'btn_search');
    });
    function press_target(event, target_id)
    {
        event = event || window.event;
        if(event.keyCode == 13) { //回车
            $('#'+target_id).trigger('click');
        }
        return false;
    }
    // 回车触发点击———————————————————————————————————————END

    //keyword内容重置——————————————————————————————————————START
    $("#btn_search_reset").click(function(){
        keyword = '';
        $("#search").val('');
        $("#btn_search_reset").hide();
        location.href = $("#activity_info_url").val();
    });
    //keyword内容重置——————————————————————————————————————END

    //全选checkbox————————————————————————————————————————START
    $("#ckall").click(function(){
        console.log('多选');
        //var ckall = $("#ckall").attr('checked');
        var ckall = $("#ckall").prop('checked');//返回true  或  false
        if(ckall){
            $(".ck").prop('checked',ckall);
            //$("input[type='checkbox']").prop('checked',ckall);
            //console.log($("input[type='checkbox']"));
            var j = ck_user_guids.length;
            var user_guid = '';
            $("#user_list_tbody").find("input:checked").each(function(){
                user_guid = $(this).val();
                if(ck_user_guids.indexOf(user_guid) == -1){
                    ck_user_guids[j] = user_guid;
                    j++;
                }
            });


            $("#checked_guids").attr('value',ck_user_guids);
            $("#ck_user_guid_count").text(ck_user_guids.length);

            if(ck_user_guids.length != 0){
                $(".myModaltongzhi_button").removeAttr('disabled');
                $("#not_status_myModaltongzhi_button").removeAttr('disabled');
            }
        }else{
            $("#user_list_tbody").find("input:checked").each(function(){
                ck_user_guids.splice($.inArray($(this).val(),ck_user_guids),1);
            });
            if(ck_user_guids.length == 0){
                $(".myModaltongzhi_button").prop('disabled',true);
                $("#not_status_myModaltongzhi_button").prop('disabled',true);
                $("#ckall").prop('checked',false);
            }

            $("#checked_guids").attr('value',ck_user_guids);
            $("#ck_user_guid_count").text(ck_user_guids.length);

            $(".ck").removeAttr('checked');
        }
    });
    //全选checkbox————————————————————————————————————————END

    //单选checkbox————————————————————————————————————————————START
    $("#user_list_tbody").delegate("input[name = 'ck']",'click',function(){
        console.log('单选');
        var ck_user_guid = $(this).prop('checked');
        var j = ck_user_guids.length;
        if(ck_user_guid){
            $(this).prop('checked',ck_user_guid);
            ck_user_guids[j] = $(this).val();

            $("#checked_guids").attr('value',ck_user_guids);
            $("#ck_user_guid_count").text(ck_user_guids.length);

            $(".myModaltongzhi_button").removeAttr('disabled');
            $("#not_status_myModaltongzhi_button").removeAttr('disabled');
        }else{
            ck_user_guids.splice($.inArray($(this).val(),ck_user_guids),1);

            $("#checked_guids").attr('value',ck_user_guids);
            $("#ck_user_guid_count").text(ck_user_guids.length);

            $(this).removeAttr('checked');
            if(ck_user_guids.length == 0){
                $(".myModaltongzhi_button").prop('disabled',true);
                $("#not_status_myModaltongzhi_button").prop('disabled',true);
                $("#ckall").prop('checked',false);
            }
        }
    });
    //单选checkbox————————————————————————————————————————————END

    //添加参会人员————————————————————————————————————————————START
    $("#add_user_btn").click(function(){
        obj = $('#modal_add_signup_user_ajax');
        var iframe = obj.find("iframe");
        iframe.attr('src', iframe.data('src'));
        obj.modal('show');
    });
     //修改参会人员————————————————————————————————————————————START
    $("#user_list_tbody").delegate("i[name = 'update_info']",'click',function(){
        ticket_guid = '';
        if(activity_status_hidden != '1'){
            messager.show({
                type: "alert",
                title: "提示框",
                content: "活动不是进行中，不能操作"
                //,
                //autoClose: true
            },function(){
                //location.href = activity_info_url;
                ajax_ticket_list(ajax_userinfo_url);
            });
            //alertModal("活动不是进行中，不能操作");
        }else{
            ticket_guid = $(this).parent('a').attr('name');
            $("#updatemodal-iframe").attr('data-src',update_data_url + '/user_guid/' + ticket_guid);
            obj = $('#modal_update_signup_user_ajax');
            var iframe = obj.find("iframe");
            iframe.attr('src', update_data_url + '/user_guid/' + ticket_guid);
            obj.modal('show');
        }
    });

    //添加或更新人员信息后刷新——————————————————————————————————————START
    $("#hidden_update_refresh").blur(function(){
        if($("#hidden_update_refresh").val() != ''){
            console.log('hidden_update_refresh');
            $("#hidden_update_refresh").val('');
            ajax_ticket_list(ajax_userinfo_url);
        }
    });
    $.extend({ajax_ticket_list:ajax_ticket_list});
    //添加或更新人员信息后刷新——————————————————————————————————————END

    //查看参会人员————————————————————————————————————————————END
    $("#user_list_tbody").delegate("i[name = 'look_info']", 'click', function(){
        $.ajax({
            url: $("#hidden_user_info_url").val(),
            type: 'post',
            data: {
                aid: $("#activity_guid_hidden").val(),
                uid: $(this).parent('a').attr('name')
            },
            dataType: 'json',
            success: function(data){
                if(data != ''){
                    console.log(data);
                    alertUserViewModal(data);
                }
            }
        });
    });

    //删除报名人员
    $("#user_list_tbody").delegate("i[name='del_info']",'click',function(){
        if(activity_status_hidden != '1'){
            messager.show({
                type: "alert",
                title: "提示框",
                content: "活动不是进行中，不能操作",
                autoClose: true
            });
            //alertModal("活动不是进行中，不能操作");
        }else{
            var uid = $(this).parent('a').attr('name');
        messager.show({
            type:"confirm",
            title:"操作确认",
            content:"确定要删除该报名人员？",
            //buttonTitle:"认证"
        }, function (type) {
            if(type==="submit"){
                    $("#email_build").attr('disabled', true);
                    $.ajax({
                        url: $("#hidden_del_user_info_url").val(),
                        type: 'post',
                        data: {
                            uid: uid
                        },
                        dataType: 'json',
                        success: function(data){
                            if(data != ''){
                                console.log(data);
                                if(data.status == 'ok'){
                                    messager.show({
                                        type: "alert",
                                        title: "操作确认",
                                        content: data.msg,
                                        autoClose: true
                                    },function(){
                                        //location.href = activity_info_url;
                                        ajax_ticket_list(ajax_userinfo_url);
                                    });
                                    //alertTips($('#tips-modal'), data.msg,activity_info_url);
                                }else{
                                    messager.show({
                                        type: "alert",
                                        title: "操作确认",
                                        content: data.msg
                                    },function(){
                                        //location.href = activity_info_url;
                                        ajax_ticket_list(ajax_userinfo_url);
                                    });
                                    //alertTips($('#tips-modal'), data.msg);
                                }
                                uid = '';
                            }
                        }
                    });
                }
        });

    }
    });

    //状态不对添加报名按钮
    $("#not_status_add_user_btn").click(
        function(){
            messager.show({
                content: '活动不是进行中，不能操作',
                autoClose: true
            });
        //alertModal();
    });
    $("#not_status_moreoperation").click(function(){
        messager.show({
            content: '活动不是进行中，不能操作',
            autoClose: true
        });
        //alertModal('活动不是进行中，不能操作');
    });
    $("#not_status_myModaltongzhi_button").click(function(){
        messager.show({
            content: '活动不是进行中，不能操作',
            autoClose: true
        });
        //alertModal('活动不是进行中，不能操作');
    });

//批量导入
$("#muti-import").on('change', function(){
    $("#upload-file").modal('show');
    $("#muti-import-form").submit();
});


    //top 锚点
    $("body").append('<div class="mt_scrollUp"><div class="container mt_wrapper"><div class="bottom_tools">'+
        '<a id="scrollUp" href="javascript:;" title="飞回顶部"></a>'+
        '</div></div></div>')
    var $body = $(document.body);;
    var $bottomTools = $('.bottom_tools');
    var $mtservice = $('#mt_service');
    var mtImg = $('.mt_img');
    $(window).scroll(function () {
        var scrollHeight = $(document).height();
        var scrollTop = $(window).scrollTop();
        var $footerHeight = $('footer').outerHeight(true);
        var $windowHeight = $(window).innerHeight();
        scrollTop > 100 ? $(".bottom_tools").fadeIn(200).css("display","block") : $(".bottom_tools").fadeOut(200);
        $bottomTools.css("bottom", scrollHeight - scrollTop - $footerHeight > $windowHeight ? 40 : $windowHeight + scrollTop + $footerHeight + 40 - scrollHeight);
    });
    $('#scrollUp').click(function (e) {
        e.preventDefault();
        $('html,body').animate({ scrollTop:0});
    });

});

