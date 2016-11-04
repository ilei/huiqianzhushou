define(['jquery','messageControl', 'pin', 'ui', 'validate', 'langs', 'homeCommon'], function($,messager){

  var act_status = $("#act_status").val();//活动状态
  var is_verify = $("#is_verify").val();//是否审核
  $(".pinned").pin({containerSelector: "#pin-nav", minWidth: 940});
  $(".list_down").delegate(".remove", 'click', function(){
    $(this).parents(".form-group").remove();
    var target = $(this).siblings("button").data('target');
    $(target).remove();
    var href = $("#act-del").val();
    var guid = $(this).data('guid');
    $.ajax({
      type: 'POST',
      url: href,
      dataType: 'json',
      data: {guid:guid},
    }).done(function(res) {
      console.log(res);
    });
  });


  //添加表单项
    function create_text(name, ym_type, html_type, note, options){
      var data = {
        name:name,
        ym_type:ym_type,
        html_type:html_type,
        note:note,
      };
      if(options){
        data.options = options;
      }
      $.ajax({
        type: 'POST',
        url: $("#act-add").val(),
        dataType: 'json',
        data: data,
      }).done(function(res) {
        $(".list_down").append(res.form);
        $("#target-list").before(res.target);
        window.location.reload();
      });
    }


  $("#up_1").click(function (){
    if(act_status == '0') {
      create_text('邮箱', 'email', 'text', '请输入邮箱');
    }
  });
  $("#up_2").click(function (){
    if(act_status == '0') {
      var op = ['男', '女'];
      create_text('性别', 'sex', 'radio', '请输入性别', op);
    }
  });
  $("#up_3").click(function (){
    if(act_status == '0') {
      create_text('年龄', 'age', 'text', '请输入年龄');
    }
  });
  $("#up_4").click(function (){
    if(act_status == '0') {
      create_text('公司', 'company', 'text', '请输入公司');
    }
  });
  $("#up_5").click(function (){
    if(act_status == '0') {
      create_text('职位', 'position', 'text', '请输入职位');
    }
  });
  $("#up_6").click(function (){
    if(act_status == '0') {
      create_text('地址', 'address', 'text', '请输入地址');
    }
  });

  $("#down_1").click(function (){
    if(act_status == '0') {
      create_text('单行文本框', 'default', 'text', '请输入信息');
    }
  });
  $("#down_2").click(function (){
    if(act_status == '0'){
    create_text('多行文本框', 'default', 'textarea', '请输入信息');
    }
  });
  $("#down_3").click(function (){
    if(act_status == '0') {
      var op = ['单选选项1', '单选选项2'];
      create_text('单选文本框', 'default', 'radio', '请输入信息', op);
    }
  });
  $("#down_4").click(function (){
    if(act_status == '0') {
      var op = ['复选选项1', '复选选项2'];
      create_text('复选文本框', 'default', 'checkbox', '请输入信息', op);
    }
  });
  $("#down_5").click(function (){
    if(act_status == '0') {
      create_text('日期', 'date', 'text', '请输入日期');
    }
  });
  $("#down_6").click(function (){
    if(act_status == '0') {
      var op = ['下拉选项1', '下拉选项2'];
      create_text('下拉菜单', 'default', 'select', '请输入信息', op);
    }
  });

  $("body").delegate(".jump_modal", 'click', function(){
    var target = $(this).data('target');
    $(target).find('form')[0].reset();
  });

  $("body").delegate('.button-square', 'click', function(){

  });

  $("body").find(".fade").find('form').each(function(i, o){
    $(o).validate({
      errorPlacement: function(error, element){
        element.next('div .tishinr').html(error);
      },
    //showErrors: function(error, element){
    //  if(error && element.length > 0){
    //    var set_element = $(element[0].element);
    //    $(element[0].element).parents().find('div .alert').html($(element)[0].message);
    //    $(element[0].element).parents().find('div .alert').show();
    //  }else{
    //    console.log(set_element);
    //    $(set_element).parents().find('div .alert').html('');
    //    $(set_element).parents().find('div .alert').hide();
    //  }
    //  },
      success:function(element){
        element.parents().find('div .alert').hide();
        //element.parents(".row").siblings("p").html('');
      },
      submitHandler: function(form){
        $(form).find('.btn-save').attr('disabled', true);
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
          rangelength:[2,6],
          remote: {
            url: $("#ajax_check_item_name").val(),
            type: "post",
            data: {
              name: function() {
                return $(o).find("#item-name").val();
              },
              guid: function(){
                return $(o).find("#item-name").data('guid');
              }
            }
          },
        },
        note:{
          required: true,
        },
      },
      messages: {
        name: {
          required: $.lang.item_name_not_empty,
          rangelength:$.lang.item_name_len_error,
          remote:$.lang.item_name_exist,
        },
        note: {
          required: $.lang.note_not_empty,
        },
      },
    });
  });

  //$("body").on("mouseover mouseout",".form-group", function(event){
  //  var p=$(this).parent();
  //  var num=$(this).index();
  //
  //  if (num>0) {
  //    if (event.type == "mouseover") {
  //      $(".list_down .form-group").eq(num-1).find(".icon_hidde").css("visibility","visible");
  //      $(".list_down .form-group").css("background-color","white");
  //    }
  //    else {
  //      $(".list_down .form-group").eq(num-1).find(".icon_hidde").css("visibility","hidden");
  //    }
  //  }
  //});
  $("body").on("mouseover mouseout",".form-group", function(event){
      var p=$(this).parent();
      var num=$(this).index();
      if (num>0) {
        if (event.type == "mouseover") {
          if(act_status == '0') {//活动未发布时，能作修改
            $(".list_down .form-group").eq(num - 1).find(".icon_hidde").css("visibility", "visible");
          }
          if(act_status != '0'){
            $(".list_down .form-group").eq(num - 1).css("background-color","white");//去除阴影效果
          }
        }
        else {
          $(".list_down .form-group").eq(num-1).find(".icon_hidde").css("visibility","hidden");
          //$(".list_down .form-group").css("background-color","white");
        }
      }
    });

  $("body").on("click",".button-square",function (){
    var value=$(this).parent().parent();
    if($(value).siblings(".sub_option").length <= 1){
      alert('不能全部删除');
      return false;
    }else{
      $(value).remove();
    }
  });

  var index;
  $("body").on("click",".button-square_1, .text_add",function (){
    var pp    = $(this).parents(".sub_option");
    if(!index){
      index = $(pp).siblings(".sub_option").length;
    }else{
      index++;
    }
    var obj   = $($("#target-list").html());
    obj.find("input[name='value']").attr('name', 'options['+index+'][value]');
    $(pp).before(obj);
  });
  $(".list_down").sortable({ revert:true});

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
    if (act_status == '0') {
      alertModal('未发布活动不能查看');
    }
    if (is_verify == 0 && act_status != '0') {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });
        };

  });
  $("#order_verify_li").click(function () {
    if (act_status == '0') {
      alertModal('未发布活动不能查看');
    }
    if (is_verify == 0 && act_status != '0') {
            messager.show({
                type:"alert",
                content:"审核中活动不能查看",
                autoClose:true
            });
        };
  });
  $("#signup_and_verify_ticket").click(function () {
    if (act_status == '0') {
      alertModal('未发布活动不能查看');
    }
    if (is_verify == 0 && act_status != '0') {
            messager.show({
                type:"alert",
                content:"审核中活动不能进行签到",
                autoClose:true
            });
        };
  });
  $("#signup_ticket_li").click(function () {
    if (is_verify == 0 && act_status != '0') {
      messager.show({
        type:"alert",
        content:"审核中活动不能查看",
        autoClose:true
      });
    };
  });  //活动未发布提示框
  $("#signin_form_li").click(function () {
    if (is_verify == 0 && act_status != '0') {
      messager.show({
        type:"alert",
        content:"审核中活动不能查看",
        autoClose:true
      });
    };
  });
  $("#look_ticket_info_id").click(function () {
    if (act_status == '0') {
      alertModal('未发布活动不能查看');
    }
  });
  $("#look_all_order_id").click(function () {
    if (act_status == '0') {
      alertModal('未发布活动不能查看');
    }
  });

});

