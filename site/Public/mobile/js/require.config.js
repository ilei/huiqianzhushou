require.config({
    paths : {
        'jquery': '../../common/js/jquery.min',
        'bootstrap': '../../common/bootstrap/js/bootstrap.min',
        'lazyload': '../../common/js/jquery.lazyload',
        'validate': '../../common/js/jquery.validate',
        'icheck': '../../icheck/icheck.min'}
    ,shim: {
      'bootstrap':{deps:['jquery']}, 
      'validate': {deps:['jquery']}, 
      'icheck':{deps:['jquery']}, 
      'lazyload':{deps:['jquery']}
    }
});
