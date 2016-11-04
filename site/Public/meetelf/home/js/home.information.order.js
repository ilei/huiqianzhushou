define(['jquery'], function ($) {
  function show_wallet(){
    window.location.href=$("#msg_order_url").val();
  }
  if($("#msg_order").val()){
    setTimeout(show_wallet, 3000);
  }
});
