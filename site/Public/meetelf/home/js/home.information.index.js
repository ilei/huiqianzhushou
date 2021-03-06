require(['jquery', 'bootstrap', 'lazyload', 'langs'], function ($) {
    var dataModule = $('body').delegate('a[href^="#"]', 'click', function (e) {e.preventDefault()});
    var a = document.createElement("a");
    var u = window.location.href;
    a.href = u;
    var url = a.protocol + '://' + a.host;
    var curr_path = u.substr(url.length - 1);
    $("#pinned-nav").find(".dropdown-menu").find('a').each(function (i, o) {
        if ($(o).attr('href') == curr_path) {
            $(o).attr('class', 'active');
            $(o).parent().parent(".dropdown-menu").siblings(".dropdown-toggle").attr('class', 'dropdown-toggle active');
        }
    });
    var $body = $(document.body);
    var $bottomTools = $('.bottom_tools');
    var $mtservice = $('#mt_service');
    var mtImg = $('.mt_img');
    $(window).scroll(function () {
        var scrollHeight = $(document).height();
        var scrollTop = $(window).scrollTop();
        var $footerHeight = $('footer').outerHeight(true);
        var $windowHeight = $(window).innerHeight();
        scrollTop > 100 ? $(".bottom_tools").fadeIn(200).css("display", "block") : $(".bottom_tools").fadeOut(200);
        $bottomTools.css("bottom", scrollHeight - scrollTop - $footerHeight > $windowHeight ? 40 : $windowHeight + scrollTop + $footerHeight + 40 - scrollHeight);
    });
    $('#scrollUp').click(function (e) {
        e.preventDefault();
        $('html,body').animate({scrollTop: 0});
    });
    $mtservice.hover(function () {
        mtImg.css("opacity", "1");
    }, function () {
        mtImg.css("opacity", "0");
    });

    $(document).ready(function(){
        img_load();
    });

    function img_load(){
        //统一设置图片的LazyLoad
        $("img[data-original]").lazyload({
            effect: "show",
            placeholder:"/Public/meetelf/home/images/portraitup.png",
            threshold : 200,
            event:"sporty"
        });
        setTimeout(function(){
            $("img").trigger("sporty");
        },1000);
    }
    $.extend({imgReload:img_load});


    // 弹出modal框
    function alertModal(msg, obj) {
        if (!obj) {
            obj = $('#tips-modal');
        }
        obj.modal('show');
        obj.find('.tips-msg').html(msg);
    }
    $.extend({alertModal:alertModal});


});
