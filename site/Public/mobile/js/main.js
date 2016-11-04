require.config({
  paths : {'jquery' : 'http://cdn.bootcss.com/jquery/1.11.1/jquery.min', 'bootstrap' : '//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min', 'validate': '../../common/js/jquery.validate', 'icheck': '../../icheck/icheck.min', 'lazyload': '../../common/js/jquery.lazyload'}
		, shim: {'bootstrap':{deps:['jquery']}, 'validate': {deps:['jquery']}, 'icheck':{deps:['jquery']}, 'lazyload':{deps:['jquery']}}
});


require(['jquery', 'bootstrap', 'lazyload','icheck'], function ($){

	var dataModule = $('body').delegate('a[href^="#"]', 'click', function(e){e.preventDefault()}).data('module');
	if(dataModule) {require(dataModule.split(' '))}
	$("img[data-original]").lazyload({
		effect: "fadeIn",
		placeholder:"/Public/meetelf/home/images/portraitup.png",
		threshold: 200
		//event:"sporty"
	});
});
