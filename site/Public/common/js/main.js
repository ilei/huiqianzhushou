require.config({
    paths: {
        'jquery': './jquery.min',
        'bootstrap': '../bootstrap/js/bootstrap.min',
        'lazyload': 'jquery.lazyload',
        'validate': 'jquery.validate',
        'cookie': 'jquery.cookie',
        'permanent': 'permanent',
        'lang': 'lang',
        'pin': 'jquery.pin.min',
        'datepicker': 'DateTimePicker',
        'upload': 'jquery.ajaxupload',
        'jcrop': 'jcrop/jquery.Jcrop.min',
        'migrate': 'jcrop/jquery-migrate-1.1.0.min',
        'bootstrap-switch': 'bootstrap-switch.min',
        'zeroClipboard': 'zeroclipboard/ZeroClipboard',
        'ui': 'ui.min',
        'pagerControlClient': "controls/pagerControl.client",//分页控件的客户端扩展
        'ckeditor': '../../meetelf/ckeditor/ckeditor',
        'combobox':'../../meetelf/js/bootstrap-combobox',
        'messageControl':"controls/messageControl", //弹窗控件
        'ueditorall':"../../ueditor/ueditor.all.min",//百度编辑器
        'ueditorconfig':"../../ueditor/ueditor.config",//百度编辑器
        'homeCommon':'./home.common',
        'homelang':'./lang/l.home.act.add',
        'cropper': '../../meetelf/upload/js/cropper.min',
        'MTFCropper':"../../common/js/MTFCropper",
        'form': '../../meetelf/upload/js/jquery.form',
        
    }
    ,
    shim: {
        'validate': {deps: ['jquery']},
        'pin': {deps: ['jquery'], exports: 'jQuery.fn.pin'},
        'combobox': {deps: ['jquery'], exports: 'jQuery.fn.combobox'},
        'datepicker': {deps: ['jquery'], exports: 'jQuery.fn.DateTimePicker'},
        'bootstrap': {deps: ['jquery']},
        'lazyload': {deps: ['jquery'], exports: 'jQuery.fn.lazyload'},
        'cookie': {deps: ['jquery'], exports: 'jQuery.fn.cookie'},
        'permanent': {deps: ['jquery']},
        'upload': {deps: ['jquery']},
        'jcrop': {deps: ['jquery']},
        'migrate': {deps: ['jquery']},
        'bootstrap-switch': {deps: ['jquery']},
        'ui': {deps: ['jquery'], exports: 'jQuery.fn.sortable'},
        'ueditorall': {deps: ['jquery', 'ueditorconfig']},
        'ueditorconfig': {deps: ['jquery']},
        'homeCommon': {deps: ['jquery']},
        'homelang': {deps: ['jquery']},
        'cropper' : {deps: ['jquery'], exports: 'jQuery.fn.cropper'},
        'MTFCropper':{deps: ['jquery','cropper']},
        'form': {deps: ['jquery']},
    },
    urlArgs: "t=" + ((release = document.getElementById('last_release').value) == 1 ? (new Date()).getTime() : release),
    waitSeconds: 0 //超时时间 永不超时
});
require(['jquery', 'bootstrap', 'lazyload', 'validate', 'permanent', 'homeCommon'], function ($) {
});
