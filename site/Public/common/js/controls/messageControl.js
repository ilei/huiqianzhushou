/**
 * Created by ManonLoki1 on 15/10/27.
 * 消息控件
 */
//消息模块
define(['jquery'], function () {
    var modal_id = "control-modal-tips";//弹窗ID
    var refereceCount=0;//引用数量 用于分离各自窗体的Dom引用
    /**
     *
     * @param config 配置信息 Object
     * @param callback 回调函数 function
     *
     * 配置信息定义
     * type  alert / confirm
     * title 标题
     * content 内容
     * buttonTitle 按钮内容
     * autoClose 是否自动关闭 仅Alert模式有效
     * timeout 定时关闭时间 默认3秒
     */
    var show = function (config, callback) {
        var isModalShow = true;
        var realModalID=modal_id+(++refereceCount); //计算当前模态窗体的ID

        config = config || {
                type: 'alert'
            };
        callback = callback || function (type) {
                console.log(type + ":no callback");
            };


        //组建新的Dom
        //树根
        var modal_dom_root = $("<div>")
            .addClass("modal fade")
            .attr("id", realModalID)
            .attr("tabindex", "-1")
            .attr("role", "dialog")
            .attr("aria-labelledby", modal_id + "_title");
        var modal_dom_document = $("<div>")
            .addClass("modal-dialog")
            .attr("role", "document");
        var modal_dom_content = $("<div>")
            .addClass("modal-content");

        var modal_dom_header = $("<div>")
            .addClass("modal-header")
            .append($('<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>')
                .click(function () {
                    callback("cancel");
                    modal_dom_root.modal("hide");
                }))
            .append($('<h4 class="modal-title"></h4>').attr("id", modal_id + "_title").text(config.title || "提示消息"));

        var modal_dom_body = $("<div>")
            .addClass("modal-body")
            .append($("<p>").html(config.content || ""));

        var modal_dom_footer = $("<div>")
            .addClass("modal-footer");

        if (config.type === "alert") {
            modal_dom_footer.append($('<button type="button" class="btn btn-primary" data-dismiss="modal"></button>')
                .text("确定")
                .click(function () {
                    isModalShow = false;
                    modal_dom_root.modal("hide");
                    callback("cancel");
                }));

        } else {

            modal_dom_footer.append($('<button type="button" class="btn btn-primary"></button>')
                .text(config.buttonTitle || "确定")
                .click(function () {
                    modal_dom_root.modal("hide");
                    callback("submit");
                }));

            modal_dom_footer.append($('<button type="button" class="btn btn-default"></button>')
                .text("取消")
                .click(function () {
                    modal_dom_root.modal("hide");
                    callback("cancel");
                }));

        }


        //拼DOM到BODY中
        modal_dom_header.appendTo(modal_dom_content);
        modal_dom_body.appendTo(modal_dom_content);
        modal_dom_footer.appendTo(modal_dom_content);
        modal_dom_content.appendTo(modal_dom_document);
        modal_dom_document.appendTo(modal_dom_root);
        modal_dom_root.appendTo($('body'));

        //处理完全隐藏事件
        $(modal_dom_root).on("hidden.bs.modal",function(e){
            refereceCount--;//
            $(this).remove();
        });
        //弹出窗体
        modal_dom_root.modal("show");



        //判断是否自动关闭
        if (config.autoClose && config.type === 'alert') {
            setTimeout(function () {
                //只在Window打开的情况下进行回调
                if (isModalShow) {
                    modal_dom_root.modal("hide");
                    callback("cancel");
                }
            }, (config.timeout || 3) * 1000);
        }
    }

    return {
        show: show
    }

});
