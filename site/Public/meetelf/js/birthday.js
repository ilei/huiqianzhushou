define(['jquery', 'lang','messageControl', 'validate', 'MTFCropper'], function($,lang,messager){
$.extend({
    ms_DatePicker: function (options) {
            var defaults = {
                YearSelector: "#sel_year",
                MonthSelector: "#sel_month",
                DaySelector: "#sel_day",
                FirstText: "--",
                FirstValue: 0
            };
            var opts = $.extend({}, defaults, options);
            var $YearSelector = $(opts.YearSelector);
            var $MonthSelector = $(opts.MonthSelector);
            var $DaySelector = $(opts.DaySelector);
            var FirstText = opts.FirstText;
            var FirstValue = opts.FirstValue;

            // 初始化
            var str = "<option value=\"" + FirstValue + "\">" + FirstText + "</option>";
            $YearSelector.html(str);
            $MonthSelector.html(str);
            $DaySelector.html(str);

            // 年份列表
            var yearNow = new Date().getFullYear();
			var yearSel = $YearSelector.attr("rel");
            for (var i = yearNow; i >= 1900; i--) {
				var sed = yearSel==i?"selected":"";
				var yearStr = "<option value=\"" + i + "\" " + sed+">" + i + "</option>";
                $YearSelector.append(yearStr);
            }
            var monthSel = $MonthSelector.attr("rel");
                    for (var i = 1; i <= 12; i++) {
                        var sed = monthSel==i?"selected":"";
                        var monthStr = "<option value=\"" + i + "\" "+sed+">" + i + "</option>";
                        $MonthSelector.append(monthStr);
                    }
            var year = parseInt($YearSelector.val());
            var month = parseInt($MonthSelector.val());
            var dayCount = 0;
                    switch (month) {
                        case 1:
                        case 3:
                        case 5:
                        case 7:
                        case 8:
                        case 10:
                        case 12:
                            dayCount = 31;
                            break;
                        case 4:
                        case 6:
                        case 9:
                        case 11:
                            dayCount = 30;
                            break;
                        case 2:
                            dayCount = 28;
                            if ((year % 4 == 0) && (year % 100 != 0) || (year % 400 == 0)) {
                                dayCount = 29;
                            }
                            break;
                        default:
                            break;
                    }
            var daySel = $DaySelector.attr("rel");
                    for (var i = 1; i <= dayCount; i++) {
                        var sed = daySel==i?"selected":"";
                        var dayStr = "<option value=\"" + i + "\" "+sed+">" + i + "</option>";
                        $DaySelector.append(dayStr);
                    }        
            // BuildMonth();
            // 月份列表
            function BuildMonth(){
                $MonthSelector.html(str);

                if ($YearSelector.val() == 0) {
                    $MonthSelector.html(str);
                }else{
                    
        			// var monthSel = $MonthSelector.attr("rel");
                    var monthSel = '';
                    for (var i = 1; i <= 12; i++) {
        				var sed = monthSel==i?"selected":"";
                        var monthStr = "<option value=\"" + i + "\" "+sed+">" + i + "</option>";
                        $MonthSelector.append(monthStr);
                    }
                
                }
            }
            // 日列表(仅当选择了年月)
            function BuildDay() {
                if ($YearSelector.val() == 0 || $MonthSelector.val() == 0) {
                    // 未选择年份或者月份
                    $DaySelector.html(str);
                } else {
                    // $DaySelector.html(str);
                    var year = parseInt($YearSelector.val());
                    var month = parseInt($MonthSelector.val());
                    var dayCount = 0;
                    switch (month) {
                        case 1:
                        case 3:
                        case 5:
                        case 7:
                        case 8:
                        case 10:
                        case 12:
                            dayCount = 31;
                            break;
                        case 4:
                        case 6:
                        case 9:
                        case 11:
                            dayCount = 30;
                            break;
                        case 2:
                            dayCount = 28;
                            if ((year % 4 == 0) && (year % 100 != 0) || (year % 400 == 0)) {
                                dayCount = 29;
                            }
                            break;
                        default:
                            break;
                    }
					
					// var daySel = $DaySelector.attr("rel");
                    var daySel ='';
                    for (var i = 1; i <= dayCount; i++) {
						var sed = daySel==i?"selected":"";
						var dayStr = "<option value=\"" + i + "\" "+sed+">" + i + "</option>";
                        $DaySelector.append(dayStr);
                    }
                }
            }
            $MonthSelector.change(function () {
                BuildDay();
            });
            $YearSelector.change(function () {
                BuildMonth();
                BuildDay();
                                
            });
			if($DaySelector.attr("rel")!=""){
				BuildDay();
			}
        } // End ms_DatePicker
});
//联动菜单
    $(".form-horizontal").delegate("select[name='other_info[area1]']", 'change', function(){
        linkage('other_info[area1]', 'other_info[area2]');    
    });     

    //联动函数
    function linkage(s, d){
        var sobj = $("select[name='" + s + "']");
        var dobj = $("select[name='" + d + "']");
        $.ajax({
            type: 'POST',
            url: '/act/ajax_get_area',
            dataType: 'json',
            data: {id:sobj.val()},
        }).done(function(res) {
            if(res.status){
              dobj.html('').html(res.msg);
            }else{
              dobj.html('<option value>市/区</option>');
              console.log(res.msg);
            }

        });
    }
$.ms_DatePicker();

$.validator.addMethod("isTel", function(value, element) {
// var tel = /^(^0\d{2}-?\d{8}$)|(^0\d{3}-?\d{7}$)|(^0\d2-?\d{8}$)|(^0\d3-?\d{7}$)$/;    //电话号码格式022-12345678
   var tel = /^1\d{10}$|^(0\d{2,3}-?|\(0\d{2,3}\))?[1-9]\d{4,7}(-\d{1,8})?$/;
return this.optional(element) || (tel.test(value));
}, $.lang.mobile_format_err);

$.validator.addMethod("isName", function(value, element) {
var tel = /^[\u4e00-\u9fa5a-zA-Z]{2,10}$/;    //汉字跟英文
return this.optional(element) || (tel.test(value));
}, $.lang.nickname_type);

//validate自定义验证邮箱格式
        $.validator.addMethod("isEmail", function (value, element) {
            var pattern = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
            return pattern.test(value);
        }, $.lang.email_format_err);
//表单验证

    $("#information").validate({
        errorPlacement: function(error, element){
            if(error){
                element.parent().next('.tishinr').html(error);
            }
        },
        rules: {
            'info[email]': {
                isEmail:true,
            },
            'other_info[work_phone]': {
                isTel:true,
            },
            'other_info[nickname]': {
                rangelength: [2, 8],
                isName:true
            },
            'other_info[company]':{
               rangelength: [2, 20],
            },
            'other_info[position]':{
               rangelength: [2, 10],
            },
            'other_info[address]':{
                rangelength: [2, 30],
            }
        },
        messages: {
            'other_info[work_phone]': {
            },
            'other_info[nickname]': {
                rangelength: $.lang.nickname_num, 
            },
            'other_info[company]':{
               rangelength: $.lang.company,
            },
            'other_info[position]':{
               rangelength: $.lang.position,
            },
            'other_info[address]':{
                rangelength: $.lang.address,
            }

        }
    });
    
    function isemail(str){ 
        var reg = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/; 
        return reg.test(str); 
        }     

    $("#user_photoV").mtfImgUpload('avatar', 'user_photoV', '120,120', {aspectRatio:1/1,minContainerHeight:360,minContainerWidth:360}, upload_callback);
    
    function upload_callback(data){
    
        $("#user_photoV").attr('data-original', data.data.path);
        //$("#user_photoV").lazyload();
        $.imgReload();
    }
    //绑定邮箱
    $(".mt-information").delegate("#email_build", 'click', function(){

        messager.show({
            type:"confirm",
            title:"操作确认",
            content:"确认认证邮箱?",
            buttonTitle:"认证"
        }, function (type) {
            if(type==="submit"){
                var eh = isemail($("#email").val());
                if (eh == true) {
                    $("#email_build").attr('disabled', true);
                    $.ajax({
                        type: "post",
                        url: $("#send_button_url").val(),
                        data: {email:$("#email").val()},
                        dataType: "json",
                        success: function (data) {
                            if(data.status==1){
                                time($(this));
                                // $("#email").removeAttr("readonly");
                                // alert($.lang.remove_email);
                                // window.location.reload();

                            }else{
                                // $(".text-dismissible").css("display", "none");
                                $("#email_build").removeAttr('disabled');
                                $(".text-dismissible").css('display','none');
                                messager.show({
                                    type:"alert",
                                    content:data.msg,
                                    autoClose:true
                                });

                            }
                        }
                    });
                }else{
                    messager.show({
                        type:"alert",
                        content:lang.email_format_err,
                        autoClose:true
                    });
                    return;
                };
            }
        });
    });
    //解绑邮箱
    $("#email_remove").click(function(){

        messager.show({
            type:"confirm",
            title:"操作确认",
            content:"确认取消认证?",
            buttonTitle:"确认"
        }, function (type) {
            if(type==="submit"){
                $.ajax({
                    type: "post",
                    url: $("#re_button_url").val(),
                    dataType: "json",
                    success: function (data) {
                        if(data.status==1){
                            // $("#email").removeAttr("readonly");
                            $.alertModal($.lang.remove_email);
                            window.location.reload();

                        }else{
                            messager.show({
                                type:"alert",
                                content:data.msg,
                                autoClose:true
                            });
                        }
                    }
                });
            }
        });
    });
    //解绑邮箱
    $("#email_remove").click(function(){
        alertConfirm(
                '确定取消认证？',
        function(){
            $.ajax({
                type: "post",
                url: $("#re_button_url").val(),
                dataType: "json",
                success: function (data) {
                    if(data.status==1){
                        // $("#email").removeAttr("readonly");
                        $.alertModal($.lang.remove_email);
                        window.location.reload();
                        
                    }else{
                        $.alertModal(data.msg);
                    }
                }
            });
         });  
    });
    //按钮倒计时
    var wait = 120;
    function time(o) {
        if (wait == 0) {
            o.removeAttr('disabled');
            $("#email_build").removeAttr('disabled');
            // o.next(".click_text").html($.lang.click_send_code);
            $(".text-dismissible").hide();
            wait = 120;
        } else {
            $(".text-dismissible").css("display", "block");  
            o.attr("disabled", true);
            // $("#email_build").attr('disabled', true);
                $(".click_text").html(wait + $.lang.next_time_send + $.lang.next_time_send_look);
            wait--;
            setTimeout(function() {
                    time(o)
                },
                1000)
        }
    }

});
