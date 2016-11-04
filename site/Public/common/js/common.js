/**
 * 公共js
 *
 * CT: 2014-10-08 18:10 by YLX
 * UT: 2014-10-10 09:52 by YLX
 */


// 重写js alert 方法
function ym_alert($msg)
{
    alert($msg);
}

// 错误信息渐隐, 需传入渐隐element id
function ym_fadeout(id)
{
    var alert  = $('#'+id), time=3;
    var interval = setInterval(function(){
        time--;
        if(time <= 0) {
            alert.fadeOut(1000);
            clearInterval(interval);
        };
    }, 1000);
}

/**
 *
 * @param l
 * @returns {string}
 */
function randomChar(num){

    var x="0123456789qwertyuioplkjhgfdsazxcvbnmABCDEFGHIJKLMNOPQRSTUVWXYZ";
    var tmp="";
    for(var   i=0;i< num;i++)   {
        tmp += x.charAt(Math.ceil(Math.random()*100000000)%x.length);
    }
    return tmp;
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

// 弹出确认对话框
function alertConfirm(msg, url) {
    var obj = $('#confirm-modal');
    obj.modal('show');
    obj.find('.tips-msg').html(msg);
    $('#confirm_yes').click(function(){
        location.href=url;
    });
    $('#confirm_no').click(function(){
        obj.modal('hide');
    });
}

// 弹出modal框
function alertModal(msg, obj)
{
    if(!obj) {
        obj = $('#tips-modal');
    }
	obj.modal('show');
	obj.find('.tips-msg').html(msg);
}





// 回车触发点击
function press_target(event, target_id)
{
    event = event || window.event;
    if(event.keyCode == 13) { //回车
        $('#'+target_id).trigger('click');
    }
    return false;
}

$(document).ready(function(){
    // --------------
    // 复制到剪贴板
    // --------------
    var client = new ZeroClipboard( $(".copy-button") );
    client.on( "ready", function( readyEvent ) {
        // alert( "ZeroClipboard SWF is ready!" );
        client.on( "aftercopy", function( event ) {
            // `this` === `client`
            // `event.target` === the element that was clicked
            //event.target.style.display = "none";
            $('.copy-button').text('已复制');
            setTimeout("$('.copy-button').text('重新复制');", 2000);
            //alert("Copied text to clipboard: " + event.data["text/plain"] );
        } );
    } );
	
    // 删除消息 -- START
    function del_confirm(url, msg){
        if (confirm(msg)) {
            location.href=url;
        }
        return false;
    }
	$('.ym_del').click(function(){
        msg = $(this).attr('msg');
        if(!msg) msg = '确认要删除?';
        del_confirm($(this).attr('url'), msg);
        return false;
    });
    // 删除消息 -- END
	
	// 去消input自动填充
	$('input[autocomplete="off"]').each(function(){
        var input = this;
        var name = $(input).attr('name');
        var id = $(input).attr('id');

        $(input).removeAttr('name');
        $(input).removeAttr('id');      

        setTimeout(function(){ 
            $(input).attr('name', name);
            $(input).attr('id', id);            
        }, 1);
    });	
});

//提示大小写开关
    function  detectCapsLock(event){  
        var e = event||window.event;  
        var o = e.target||e.srcElement;  
        var oTip = o.nextSibling;  
        var keyCode  =  e.keyCode||e.which; // 按键的keyCode   
        var isShift  =  e.shiftKey ||(keyCode  ==   16 ) || false ; // shift键是否按住  
          
        if (  
            ((keyCode >=   65   &&  keyCode  <=   90 )  &&   !isShift)    // Caps Lock 打开，且没有按住shift键   
            || ((keyCode >=   97   &&  keyCode  <=   122 )  &&  isShift)    // Caps Lock 打开，且按住shift键  
         ) {  
            // oTip.style.display = '';  
          $("#tishi").css('display','block');
        } else {  
          $("#tishi").css('display','none');
            // oTip.style.display  =  'none'; 
        }   
    }  
      // $("#inputPassword3").onkeypress = detectCapsLock;
    // document.getElementById('inputPassword3').onkeypress = detectCapsLock;
