$(function(){
  document.domain = $("#document_domain").val();
  var unreload = window.parent.$("#unreload").val();
  var unload = window.parent.$("#unload").val();
  var checked_1 = {} ;
  var  checked_2 = {};
  var localDate = '';
  $("body").delegate("input", 'click', function(){
    var o = this;
    if($(o).data("type") == "default" && $(o).attr('type') == 'radio'){
      if($(o).prop('checked')){checked_1.o = true;}else{
        delete checked_1.o; 
      }
    }
    if($(o).data("type") == "default" && $(o).attr('type') == 'checkbox'){
      if($(o).prop('checked')){checked_2.o = true;}else{
        delete checked_2.o; 
      }
    }
  });
  function obj_length(obj){
    var size = 0;
    for(key in obj){
      if(key) size++;
    }
    return size;
  }
  function check(o){
    if($(o).data("type") == "mobile" && $(o).attr('type') == 'text' && !is_mobile($(o).val())){
      $(o).focus();
      $(o).parents(".form-group").find(".tishinr").html('手机格式不正确');
      return false;
    }
    if($(o).data("type") == "email" && $(o).attr('type') == 'text' && !is_email($(o).val())){
      $(o).focus();
      $(o).parents(".form-group").find(".tishinr").html('邮箱格式不正确');
      return false;
    }
    if($(o).data("type") == "default" && $(o).attr('type') == 'radio' && !$(o).prop('checked') && !obj_length(checked_1)){
      $(o).focus();
      $(o).parents(".form-group").find(".tishinr").html('请选择' + $(o).parent().parent().parent('.radio').siblings("input").val());
      return false;
    }
    if($(o).data("type") == "default" && $(o).attr('type') == 'checkbox' && !$(o).prop('checked') && !obj_length(checked_2)){
      $(o).focus();
      $(o).parents(".form-group").find(".tishinr").html('请选择' + $(o).parent().parent().parent('.checkbox').siblings("input").val());
      return false;
    }
    $(o).parents(".form-group").find(".tishinr").html('');
    return true;
  }
  function is_mobile(val){
    return /^1[34578]{1}\d{9}$/.test(val);
  }
  function is_email(val){
    var pattern = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
    return pattern.test(val);
  }

  /*$("body").delegate("input, select, textarea, radio, checkbox", 'blur', function(){
    var o = this;
    var require = $(o).data('require'); 
    if(require && !$(o).val()){
      $(o).parents(".form-group").find(".tishinr").html($(o).attr('placeholder'));
      $(o).focus();
      return false;
    }else if(require && $(o).val()){
      if(!check(o)){
        $(o).focus();
        return false;
      };
    }else{
      $(o).parents(".form-group").find(".tishinr").html('');
    }
  });
  */
  $("body").delegate("input, select, textarea", 'keyup', function(){
    var o = this;
    var require = $(o).data('require'); 
    if(require && !$(o).val()){
      $(o).parents(".form-group").find(".tishinr").html($(o).attr('placeholder'));
      $(o).focus();
      return false;
    }else if(require && $(o).val()){
      if(!check(o)){
      $(o).focus();
        return false;
      };
    }else{
      $(o).parents(".form-group").find(".tishinr").html('');
    }
  });
  $("#submit_form").on('click', function(){
    var objs   = $(this).parents("form").find("input,select, textarea");
    var length = objs.length;
    for(var i = 0; i < length; i++){
      var o = $(objs[i]);
      if($(o).attr('type') == 'hidden'){
        continue; 
      }
      var require = $(o).data('require'); 
      if(require && !$(o).val()){
        $(o).parents(".form-group").find(".tishinr").html($(o).attr('placeholder'));
        $(o).focus();
        return false;
      }else if(require && $(o).val()){
        if(!check(o)){
          $(o).focus();
          return false; 
        }
      }else{
        $(o).parents(".form-group").find(".tishinr").html('');
      }
    }
    var form = $("#signup_add_user_form"); 
    var href = form.data('action'); 
    $.ajax({
      type: 'POST',
      url: href,
      dataType: 'json',
      data: form.serialize(),
    }).done(function(res) {
      if(res.status){
        if(unreload){
          window.parent.$('#signin_type').val(2);
          window.parent.$.ajax_find_user(window.parent.$('#btn_check_mobile'), $("input[name='info[mobile]']").val());
          window.parent.$("#modal_add_signup_user_ajax").modal('hide');
        }else if(unload){
          window.parent.window.console.log('unload');
          window.parent.$("#modal_add_signup_user_ajax").modal('hide');
          window.parent.$("#modal_update_signup_user_ajax").modal('hide');
          window.parent.$.ajax_ticket_list(window.parent.$("#ajax_userinfo_url_hidden").val());
        }else{
          window.parent.location.reload();
        }
      }else{
        alert(res.msg);
      }
    });
  });
  $("body").delegate("#btn-cancel, #close-window", 'click', function(){
    window.parent.$("#modal_add_signup_user_ajax").modal('hide');
    window.parent.$("#modal_add_signup_user_ajax").find('iframe').attr('src', '');
    window.parent.$("#modal_update_signup_user_ajax").modal('hide');
    window.parent.$("#modal_update_signup_user_ajax").find('iframe').attr('src', '');
    // $('#modal_add_signup_user_ajax', window.parent.document).modal('hide');
  });
  var date_opt = {
      preset: 'date', //日期
      theme: 'android-ics light', //皮肤其他参数【android-ics light】【android-ics】【ios】【jqm】【sense-ui】【sense-ui】【sense-ui】
      display: 'modal', //显示方式
      mode: 'scroller', //日期选择模式
      dateFormat: 'yy-mm-dd', // 日期格式
      lang: 'zh',
      dateOrder: 'yymmdd', //面板中日期排列格式
      startYear: 1900,
      endYear: (new Date()).getFullYear()+5
    };

    // jQuery(function($){
    //     $("#inputEmail3").mailAutoTip();
    // });

    $(".ym_date").mobiscroll(date_opt);
});
