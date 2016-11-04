define(['jquery', 'cropper', 'form'], function($) {
  var extend = function(o,n,override){
    for(var p in n){
      if(n.hasOwnProperty(p) && (!o.hasOwnProperty(p) || override)){
        o[p]=n[p];
      }
    }
    return o;
  };
  function imgUpload(bucket, defaultImg, returnSize, options, callback){
    var $me   = $(this),
    $image    = $("#cropperDefaultImage"),
    $form     = $("#cropperForm"),
    $modal    = $($me.data('target')),
    $url      = window.URL || window.webkitURL,
    $submit   = $("#upload-submit"),
    $postInputImg = $("#postInputImg"),
    $inputImage = document.getElementById("cropperInputImage"),
    $cropperData = $($form.find("#cropData")),
    $cropperDataType = $($form.find("#cropDataType")),
    $returnSize  = $("#returnSize"),
    $blobURL,
    cropBoxData,
    canvasData;
    options = extend({
      autoCropArea: 0.8,
      aspectRatio: 1/1,
      preview: '.img-preview',
      minCropBoxWidth:60,
      minCropBoxHeight:60,
      built: function () {
        $image.cropper('setCropBoxData', cropBoxData);
        $image.cropper('setCanvasData', canvasData);
      }
    }, options, true);
    var $src;
    if($src = $me.attr('data-original')){
      $image.attr('src', $src);
    }
    if(!$src && defaultImg && $("#"+defaultImg).attr('src')){
      $image.attr('src', $("#"+defaultImg).attr('src'));
    }
    if(returnSize){
      $returnSize.val(returnSize); 
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
      $cropperDataType.val(bucket);
      var opt = {
        target: '#cropperUploadIframe',
        dataType:'json',
        success:function(data){
          if(data.status){
            //$form[0].reset();
            $image.cropper('destroy');
            $modal.modal('hide'); 
            $postInputImg.val(data.data.val);
            $me.attr('src', data.data.path);
            if(callback){
              callback(data);
            }
          }else{
            alert(data.msg);
          }
        }, 
      };
      $form.ajaxSubmit(opt);
    });
  };
  $.fn.extend({mtfImgUpload: imgUpload});
});
