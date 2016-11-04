define(['jquery'], function($){
  var value = 0;
  var money = new Array(0, 0, 0);
  var guid  = {};
  $(".item-wallet-buy").delegate('li', 'click', function(){
    var a = $(this).find('a');
    if($(a).attr('class') == 'active'){
      $(a).attr('class', ''); 
      money[$(this).data('type')] = 0;
      guid[$(this).data('type')] = 0;
    }else{
      $(this).parent().find('a').attr('class', '');
      $(a).attr('class', 'active'); 
      money[$(this).data('type')] = parseInt($(a).data('money'));
      guid[$(this).data('type')] = $(a).data('guid');
    } 
    value = parseInt(money[1]) + parseInt(money[2]);
    $(".amount").find("span").html('¥'+value);
  });

  $("#submit").on('click', function(){
    if(value && guid){
      $.ajax({
        type: 'POST',
        url: $("#post-url").val(),
        dataType: 'json',
        data: {guid:guid},
      }).done(function(res) {
        if(res.status){
          window.location.href = res.url;
        }else{
          alert(res.msg);
        }
      });
    }
  });
});
