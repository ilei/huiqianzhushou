require(['jquery', 'bootstrap', 'lazyload'], function ($){
	var dataModule = $('body').delegate('a[href^="#"]', 'click', function(e){e.preventDefault()}).data('module');
	$("img[data-original]").lazyload({
		effect: "fadeIn",
		placeholder:"/Public/meetelf/home/images/portraitup.png",
		threshold: 200
	});
});
