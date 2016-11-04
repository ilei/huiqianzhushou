define(['jquery','messageControl', 'bootstrap-switch', 'validate', 'datepicker', 'langs', 'homeCommon'], function($,messager){

  var activity_status = parseInt($("#hid_status").val(), 10);
  var is_verify = $("#is_verify").val();//是否审核
  $('#mt_dtbox').DateTimePicker({
    beforeShow:function(element){
      //$(this).find("input").focus();
    }
  });
  var close = false;
  $("#myswitch_close").bootstrapSwitch({
    state: true
  });
  $(".bootstrap-switch-handle-on").html('<i class="fa fa-check"></i>')
  $(".bootstrap-switch-handle-off").html('<i class="fa fa-times"></i>')
  $("#myswitch_close").on('switchChange.bootstrapSwitch', function (e, state) {
    if(state){
      change_signup(1);
      $(".switch_close_hid").css("visibility","hidden");
    }
    else{
      change_signup(0);
      $(".switch_close_hid").css("visibility","visible");
    }
  });
$("#myswitch_open").bootstrapSwitch({
  state: false
});
$(".bootstrap-switch-handle-on").html('<i class="fa fa-check"></i>')
$(".bootstrap-switch-handle-off").html('<i class="fa fa-times"></i>')
$("#myswitch_open").on('switchChange.bootstrapSwitch', function (e, state) {
  if(state){
    $(".switch_open_hid").css("visibility","hidden");
  }
  else{
    $(".switch_open_hid").css("visibility","visible");
  }
});
function change_signup(s){
  var s = !s ? 0 : 1;
  $.ajax({
    type: 'POST',
    url: $(".switch").data("href"),
    dataType: 'json',
    data: {status:s,guid:$("#act-guid").val()},
  }).done(function(res) {
    console.log(res.msg);
  });
}
var sta = null;
$(".mtelf-table-border").delegate(".stop-sale-ticket", 'click', function(){
  var obj  = $(this);
  if(sta == null){
    sta  = obj.attr('value');
  }
  var href = obj.data('href');
  $.ajax({
    type: 'POST',
    url: $(this).data("href"),
    dataType: 'json',
    data: {status:sta},
  }).done(function(res) {
    if(res.status){
      if(sta){
        obj.parent().siblings(".for-sale").html($.lang.ticket_saling);                          
        sta = 0;
        obj.html($.lang.ticket_stop_sale);
      }else{
        obj.parent().siblings(".for-sale").html($.lang.ticket_stop_sale);                          
        sta = 1;
        obj.html($.lang.ticket_start_sale);
      } 
      window.location.reload();
      //load_ticket(obj);
    }else{
      alert(res.msg);
    }
  });
});

function load_ticket(obj){
  var target = $(obj.siblings("a").data('target'));
  $.ajax({
    type: 'POST',
    url: $(obj.siblings("a")).data('loadhref'),
    dataType: 'json',
  }).done(function(res){
    if(res.status){
      target.html('').html(res.content);
    }else{
      console.log(res);
    }
  });
}

$(".mtelf-table-border").delegate(".edit-ticket", 'click', function(){
  var target = $($(this).data('target'));
  target.find('form')[0].reset();
});
$.validator.addMethod("isNumEmpty", function(value, element) {
  if(parseInt(value) <= 0){
    return false; 
  }
  return true;
}, $.lang.ticket_num_not_empty);
$.validator.addMethod("isSaled", function(value, element) {
  var saled = parseInt($("#saled_ticket_num").val());
  if(saled && parseInt(value) < saled){
    return false; 
  }
  return true;
}, $.lang.ticket_num_not_lt_saled);

//验证 
$(".modal-body").find("form").each(function(i, o){
  var ticketname = $(o).find("#ticket-name").val();
  //表单验证
  $(o).validate({
    errorClass:'class',
    errorPlacement: function(error, element){
      if(error[0].innerText){
        alert(error[0].innerText);
      }
    },
    submitHandler: function(form){
      $(form).find('.btn-save').attr('disabled', true);
      if(!ticket_time(form)){
        return false;
      }
      $.ajax({
        type: 'POST',
        url: $(form).attr('action'),
        dataType: 'json',
        data: $(form).serialize(),
      }).done(function(res) {
        if(res.status){
          window.location.reload();	
        }else{
          $(form).find('.btn-save').attr('disabled', false);
          alert(res.msg);
        }
      });
    },
    rules: {
      name: {
        required: true,
        rangelength:[2,10],
				remote: {
					url: $("#ajax_check_ticket_name").val(),
					type: "post",
					data: {
						name: function() {
							return $(o).find("#ticket-name").val();
						},
            guid:function(){
							return $(o).find("#ticket-name").data('guid');
            }
					}
        },
      },
      num:{
        required: true,
        digits: true,
        isNumEmpty:true,
        isSaled:true,
      },
    },
    messages: {
      name: {
        required: $.lang.ticket_name_not_empty,
        rangelength:$.lang.ticket_name_len_error,
        remote:$.lang.ticket_name_exist,
      },
      num: {
        required: $.lang.ticket_num_not_empty,
        digits: $.lang.ticket_num_format_err,
      },
    },
  });
});

  function ticket_time(o){
    var res = true;
    var start =  $("#act_start_time").val();
    var end   = $("#act_end_time").val();
    var start_obj  = $(o).find("input[name='start_time']");
    var start_time = new Date($(start_obj).val().replace(/-/g,"/")).getTime();
    var start_default = $(start_obj).data('value'); 
    var end_obj    = $(o).find("input[name='end_time']");
    var end_time   = new Date($(end_obj).val().replace(/-/g,"/")).getTime();
    var now = new Date().getTime();
    if(start_time){
      if(start_default){
       now = start_default; 
       $.lang.ticket_start_lt_now = $.lang.ticket_start_lt_default;
      }
      if(start_time < now){
        alert($.lang.ticket_start_lt_now);
        $(start_obj).focus();
        res = false;
      }else if(start_time > end){
        alert($.lang.ticket_start_rt_aend);
        $(start_obj).focus();
        res = false;
      }else if(end_time && start_time > end_time){
        alert($.lang.ticket_start_rt_end);
        $(start_obj).focus();
        res = false;
      }
    }
    if(end_time && end_time > end){
        alert($.lang.ticket_end_rt_end);
        $(end_obj).focus();
        res = false;
    }
    return res;
  }

  //RTH_________________________________________________________________________
  // 弹出modal框
  function alertModal(msg, obj) {
    if (!obj) {
      obj = $('#tips-modal');
    }
    obj.modal('show');
    obj.find('.tips-msg').html(msg);
  }
  //活动未发布提示框
  $("#signup_user_info_li").click(function () {
    if (activity_status == '0') {
      alertModal('未发布活动不能查看');
    }
    if (is_verify == 0 && activity_status != '0') {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });   
        };
  });  //活动未发布提示框
  $("#signup_ticket_li").click(function () {
    if (is_verify == 0 && activity_status != '0') {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });
        };
  });  //活动未发布提示框
  $("#signin_form_li").click(function () {
    if (is_verify == 0 && activity_status != '0') {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });
        };
  });
  $("#order_verify_li").click(function () {
    if (activity_status == '0') {
      alertModal('未发布活动不能查看');
    }
    if (is_verify == 0 && activity_status != '0') {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });   
        };
  });
  //$("#signup_and_verify_ticket").click(function () {
  //  if (activity_status == '0') {
  //    alertModal('未发布活动不能查看');
  //  }
  //  if (is_verify == 0 && activity_status != '0') {
  //          messager.show({
  //              type:"alert",
  //              content:"审核中活动不能签到",
  //              autoClose:true
  //          });
  //      };
  //});
  $("#look_ticket_info_id").click(function () {
    if (activity_status == '0') {
      alertModal('未发布活动不能查看');
    }
  });
  $("#look_all_order_id").click(function () {
    if (activity_status == '0') {
      alertModal('未发布活动不能查看');
    }
  });
});

