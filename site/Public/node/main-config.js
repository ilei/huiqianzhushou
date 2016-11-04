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
        'migrate': 'jcrop/jquery-migrate-1.1.0.min',
        'bootstrap-switch': 'bootstrap-switch.min',
        'zeroClipboard': 'zeroclipboard/ZeroClipboard',
        'ui': 'ui.min',
        'pagerControlClient': "controls/pagerControl.client",//分页控件的客户端扩展
        'ckeditor': '../../meetelf/ckeditor/ckeditor',
        'cropper': '../../meetelf/upload/js/cropper.min', 
        'form': '../../meetelf/upload/js/jquery.form',
        'combobox':'../../meetelf/js/bootstrap-combobox',
        'messageControl':"controls/messageControl" //弹窗控件
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
        'migrate': {deps: ['jquery']},
        'cropper' : {deps: ['jquery'], exports: 'jQuery.fn.cropper'}, 
        'form': {deps: ['jquery']},
        'bootstrap-switch': {deps: ['jquery']},
        'ui': {deps: ['jquery'], exports: 'jQuery.fn.sortable'}
    },
});
