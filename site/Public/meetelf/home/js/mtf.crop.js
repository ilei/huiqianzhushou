require.config({
  paths : {'cropper': '../../meetelf/upload/js/cropper.min', 'form': '../../meetelf/upload/js/jquery.form'}
  ,shim: {'cropper' : {deps: ['jquery'], exports: 'jQuery.fn.cropper'}, 'form': {deps: ['jquery']}}
});
define(['jquery', 'cropper', 'form'], function($) {
  function imgUpload(bucket, options, callback){
    var $me   = $(this),
    $image    = $("#cropperDefaultImage"),
    $form     = $("#cropperForm"),
    $modal    = $($me.data('target')),
    $url      = window.URL || window.webkitURL,
    $submit   = $("#upload-submit"),
    $postInputImg = $("#postInputImg"),
    $inputImage = document.getElementById("cropperInputImage"),
    $cropperData = $($form.find("#cropData")),
    $blobURL,
    cropBoxData,
    canvasData;
    options = options || {
      autoCropArea: 0.8,
      aspectRatio: 1/1,
      preview: '.img-preview',
      minCropBoxWidth:60,
      minCropBoxHeight:60,
      built: function () {
        $image.cropper('setCropBoxData', cropBoxData);
        $image.cropper('setCanvasData', canvasData);
      }
    };
    if($me.data('original')){
      $image.attr('src', $me.data('original'));
    }

    $modal.on('show.bs.modal', function(){
      $image.cropper(options);
    }).on('hidden.bs.modal', function () {
      cropBoxData = $image.cropper('getCropBoxData');
      canvasData = $image.cropper('getCanvasData');
      $image.cropper('destroy');
    });

    if($url){
      $inputImage.onchange = function () {
        var files = this.files;
        var file;
        if (files && files.length) {
          file = files[0];
          if (/^image\/\w+/.test(file.type)) {
            $blobURL = $url.createObjectURL(file);
            $image.cropper('reset');
            $image.cropper('replace', $blobURL);
            //$inputImage.value = null;
          } else {
            window.alert('请添加图片文件');
          }
        }
      };
    } else {
      $inputImage.disabled = true;
    }

    $submit.on('click', function(e){
      $cropperData.val(JSON.stringify($image.cropper('getData')));
      var opt = {
        target: '#cropperUploadIframe',
        dataType:'json',
        success:function(data){
          if(data.status){
            $form[0].reset();
            $image.cropper('destroy');
            $modal.modal('hide'); 
            $postInputImg.val(data.data.val);
            $me.attr('src', data.data.path);
          }else{
            alert(data.msg);
          }
        }, 
      };
      $form.ajaxSubmit(opt);
    });
  };
  $.fn.extend({mtfImgUpload: imgUpload});
  $("#upload-poster-modal").mtfImgUpload('avatar');
});
