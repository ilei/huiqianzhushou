define(['jquery', 'lang', 'validate'], function($,lang){
    $('#weiboimg').popover({
        content: '<div class="weixinsm">'
        +'<div class="text-center"><img src="../images/weiboimg.jpg" alt="会签助手官方微博"></div>'
        +'<div class="text-center"><h5>会签助手官方微博</h5><h5>【扫一扫】立即关注</h5></div>'
        +'</div>',
        html: true,
        placement: 'top',
        trigger: 'hover'
    });
    $('#weixinimg').popover({
        content: '<div class="weixinsm">'
        +'<div class="text-center"><img src="../images/weixinimg.jpg" alt="会签助手官方微信"></div>'
        +'<div class="text-center"><h5>会签助手官方微信</h5><h5>【扫一扫】立即关注</h5></div>'
        +'</div>',
        html: true,
        placement: 'top',
        trigger: 'hover'
    });

    // index
    $('#downloadios').popover({
        content: '<div class="elf-poplayer">'
        +'<div class="text-center"><img src="__PUBLIC__/meetelf/images/downloadios.jpg" alt="在线签到iOS版"></div>'
        +'<div class="text-center"><h5>在线签到iOS版</h5><h5>【扫一扫】立即下载</h5></div>'
        +'</div>',
        html: true,
        placement: 'top',
        trigger: 'hover'
    });
    $('#downloadandr').popover({
        content: '<div class="elf-poplayer">'
        +'<div class="text-center"><img src="__PUBLIC__/meetelf/images/downloadandr.jpg" alt="在线签到Android版"></div>'
        +'<div class="text-center"><h5>在线签到Android版</h5><h5>【扫一扫】立即下载</h5></div>'
        +'</div>',
        html: true,
        placement: 'top',
        trigger: 'hover'
    });
});
