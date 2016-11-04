/**
 * Created by ManonLoki1 on 15/9/25.
 * 分页控件的客户端扩展
 */

define(["jquery"], function ($) {

    var manager = {};
    //注册DOM事件
    var registEvents = (function () {

        var sizer = $("#sel_pageSize");
        var pager = $("#nav_pagerControl").find("a");

        //清理遗留事件
        sizer.unbind();
        pager.unbind();

        //注册显示数量变化事件
        sizer.change(function () {
            var ps = $(this).val();
            //执行回调
            manager.sizer_callback.bind(manager.context || this)(ps);
        });

        //注册分页按钮点击事件
        pager.click(function () {

            var p = parseInt($(this).attr("p"));
            //执行回调
            manager.pager_callback.bind(manager.context || this)(p);
        });

        return arguments.callee;

    })();
    //监控DOM变化
    function watch() {
        if (manager.selector) {
            //绑定DOM变化监视事件
            manager.selector.bind("DOMNodeInserted", function (e) {

                //console.log("watch container changed re register event", Math.random() % 100000);
                //重新注册事件
                registEvents();
            });
        }
    }
    /**
     * 设置监听对象
     * @param configer {context 上下文 默认为事件触发者,element 选择器 string [* . element]|$(selector),pager 分页按钮回调 sizer 条目数量回调 }
     */
    function config(configer) {
        //只处理对象类型
        if (typeof configer === 'object') {
            //设置上下文
            manager.context = configer.context;
            //设置选择器
            manager.selector = $(configer.element || {});


            //设置回调
            manager.sizer_callback = configer.sizer instanceof Function ? configer.sizer : function () {
                console.log("unset sizer callback")
            };
            manager.pager_callback = configer.pager instanceof Function ? configer.pager : function () {
                console.log("unset pager callback")
            };

            //设置监视
            watch();
        }
    }

    return {
        "config": config
    };
});

