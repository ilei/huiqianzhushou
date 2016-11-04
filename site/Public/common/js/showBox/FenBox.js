// JavaScript Document
var GlobalBox=null;

if(window.top.GlobalBox==null || window.top.GlobalBox==undefined)
{
	GlobalBox=new FenBox();
}else{
	GlobalBox=window.top.GlobalBox;
}

function FenBox(option)
{
	var _this=this;
	var setting={
		width: 500,
		height: 370,
		src: "javascript:;",
		callback: function(){},
		success:function(data){
			
		},
		showClose:true,
		showSpeed:0
	};
	
	var objects={
		'mask'	:null,
		'box'	:null,
		'data'	: null
	};
	
	var win=null;
	
	function init()
	{
		//扩展选项
		if(option!=undefined)
			setting=$.extend(setting,option);
		
		win=$(window);
	}
	
	this.show=function(option)
	{
		setting=$.extend(setting,option);
		showLayer();
		showBox();
		
		//box.show(setting.showSpeed);
		if(setting.showSpeed>0)
		{
			objects.box.fadeIn(setting.showSpeed);
		}else{
			objects.box.show();
		}		
	}
	
	function showBox()
	{   
		var html='<div class="ymmodal"><div id="model_iframe" class="myform1 bodyform">'
		+'				<a href="javascript:void(0);" class="close mr4 closer"><i class="fa fa-times"></i></a>'
		+'				<iframe id="sso_login_iframe"name="sso_login_iframe"src="javascript:;"scrolling="no"frameborder="no"width="100%"height="90%"></iframe>'
		+'		 </div></div>';
		
		var box=$(html);
		
		var boxbd=box.find(".boxbd");
		var ifm=box.find("iframe");
		
		var boxwidth=parseInt(setting.width);//+20;
		var boxheight=parseInt(setting.height);//+17;
		

		box.find('#model_iframe').css('height',boxheight + "px");
		box.find('#model_iframe').css('width',boxwidth + "px");
		$(parent.document).find('body').addClass('modal-open');

		// box.css('left',( win.innerWidth()-boxwidth) /2  );
		// box.css('top',(win.innerHeight()-boxheight) /2  );
		
//		ifm.attr('height',setting.height);
//		ifm.attr('width',setting.width);
		
		ifm.ready(onReady).attr('src',setting.src);
		
		box.find(".closer").click(closeBox);
		
		if(setting.showClose==false)
		{
			box.find(".closer").hide();
		}
		
		objects.box=box;
		$("body").prepend(box);
	}
	
	function showLayer()
	{
		var layer=$('<div id="mask" class="mask" style="display: block">');
		// layer.css('width',win.innerWidth());
		layer.css('height',win.innerHeight());
		
		objects.mask=layer;
		$("body").prepend(layer);
	}
	
	
	function onReady()
	{
		//alert('ready');
	}

	this.setData=function(data)
	{
		setting.data=data;
	}
	
	this.getData =function()
	{
		return setting.data;
	}
	
	function closeBox(){
		objects.box.hide();
		objects.mask.hide();
		objects.box.remove();
		objects.mask.remove();
		objects.box=null;
		objects.mask=null;
		$(parent.document).find('body').removeClass('modal-open');
	}
	
	this.success=function(data)
	{
		closeBox();
		if(setting.success!=null)
		{
			setting.success(data);
		}
	}
	
	this.close=closeBox;
	
	init();
}



(function($){
	
	
	$.extend({
		showBox:function(option){
			GlobalBox.show(option);
		},
		closeBox:function(){
			GlobalBox.close();
		},
		FenBox: GlobalBox
	});
	
	jQuery.fn.extend({
		showBox:function(option){
			if(this.length>0) showLink(this.get(0),option);
		}
	});	
	
	$(document).ready(function(e) {
        $("a[rel='FenBox'], button[rel='FenBox']").each(function(index, element) {
			$(this).click(function(){
				showLink(this);	
				return false;
			});
        });
    });
	
	var showLink=function(obj,opt)
	{
		var src=$(obj).attr('href');
		var width=$(obj).attr('width');
		var height=$(obj).attr('height');
		var showSpeed=$(obj).attr('showSpeed');
		
		var option={
			src:src,
			width:width,
			height:height,
			showSpeed:showSpeed
		};
		
		var __success_str_=$(obj).attr('success');
		var __success_func_=null;
		var success=null;
		eval("__success_func_="+__success_str_);
		if(typeof __success_func_=='function')
		{
			option.success=__success_func_;
		}
		
		var __data_str_=$(obj).attr('data');
		var __data_object_=null;
		eval("__data_object_="+__data_str_);
		if(typeof __data_object_=='object'){
			option.data=__data_object_;	
		}
		
		
		if(opt!=undefined) $.extend(option,opt);
		$.FenBox.show(option);		
		return false;
	}	
})(jQuery);
