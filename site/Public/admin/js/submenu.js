/**
 * yunmai365
 * 20150525 by Hank
**/
$(document).ready(function(){
	$('.submenu > a').click(function(e)
	{
		e.preventDefault();
		var submenu = $(this).siblings('ul');
		var li = $(this).parents('li');
		var submenus = $('#leftbar li.submenu ul');
		var submenus_parents = $('#leftbar li.submenu');
		if(li.hasClass('open'))
		{
			if($(window).width() > 768) {
				submenu.slideUp();
			} else {
				submenu.fadeOut(250);
			}
			li.removeClass('open');
		} else 
		{
			if($(window).width() > 768) {
				submenus.slideUp();			
				submenu.slideDown();
			} else {
				submenus.fadeOut(250);			
				submenu.fadeIn(250);
			}
			submenus_parents.removeClass('open');		
			li.addClass('open');	
		}
	});
	
	var ul = $('#leftbar > ul');
	
	$('#leftbar > a').click(function(e)
	{
		e.preventDefault();
		var sidebar = $('#leftbar');
		if(sidebar.hasClass('open'))
		{
			sidebar.removeClass('open');
			ul.slideUp(250);
		} else 
		{
			sidebar.addClass('open');
			ul.slideDown(250);
		}
	});
});
