/**
 * Created by ManonLoki1 on 15/10/27.
 */

//弹窗控件
define(['jquery'], function () {
    var modal_id="control-modal-tips";//弹窗ID



    var show= function (obj, callback) {
        var modal_view=document.getElementById(modal_id);

        if(!modal_view){
            //创建Modal
            modal_view=$('<div>').addClass('modal').addClass('fade').attr('tabindex',-1).data('backdrop','static');

        }
    } 




});