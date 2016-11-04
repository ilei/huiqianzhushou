function fileSelected() {
    var file = document.getElementById('fileToUpload').files[0];
    if (file) {
        var fileSize = 0;
        if (file.size > 1024 * 1024)
            fileSize = (Math.round(file.size * 100 / (1024 * 1024)) / 100).toString() + 'MB';
        else
            fileSize = (Math.round(file.size * 100 / 1024) / 100).toString() + 'KB';
        // <!-- document.getElementById('fileName').innerHTML = 'Name: ' + file.name; -->
        // <!-- document.getElementById('fileSize').innerHTML = 'Size: ' + fileSize; -->
        // <!-- document.getElementById('fileType').innerHTML = 'Type: ' + file.type; -->
    }
}

function uploadFile() {
    //var type = $('select[name=type] :selected').val();
    //if ($('input[name=fileToUpload]').val()) {
    //    var filename = $('input[type=file]').val().split('\\').pop();
    //    var ext = filename.split('.').pop();
    //    if (type == 1 || type == 3 || type == 4 || type == 6) {
    //        if (ext == 'apk') {
    //            $("#tishinr_apkFile").text();
    //        } else {
    //            alert("文件格式必须为.apk");
    //            return;
    //        }
    //        ;
    //    } else if (type == 2 || type == 5) {
    //        if (ext == 'ipa') {
    //            $("#tishinr_apkFile").text();
    //        } else {
    //            alert("文件格式必须为.ipa");
    //            return;
    //        }
    //        ;
    //    }
    //    ;
    //}

    var fd = new FormData();
    fd.append("fileToUpload", document.getElementById('fileToUpload').files[0]);
    var xhr = new XMLHttpRequest();
    xhr.upload.addEventListener("progress", uploadProgress, false);
    xhr.addEventListener("load", uploadComplete, false);
    xhr.addEventListener("error", uploadFailed, false);
    xhr.addEventListener("abort", uploadCanceled, false);
    var url = ajax_url_upload_file; //document.getElementById('url').value;
    var type = $('select[name=type] :selected').val();
    url = url + '/type/' + type;
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('progressNumber').innerHTML = '100%';
            $("#notrepeat").css("width", '100%');
            var mes = xhr.responseText;
            //使用eval函数将mes子串转化成对应的对象
            var mes_obj = eval("(" + mes + ")");
            alertModal(mes_obj.msg);
            document.getElementById("submit").disabled = false;
            $('#MD5_num').html('当前上传文件的MD5值' + mes_obj.md5);
            $('#oldMD5_num').html('旧版本MD5值' + mes_obj.old_md5);
        }
    }
    xhr.open("POST", url, true);//修改成自己的接口
    xhr.send(fd);
}
$(function () {
    // 默认隐藏进度条
    $("#pro").hide();
})

// var percentComplete ='';
function uploadProgress(evt) {
    if (evt.lengthComputable) {
        var percentComplete = Math.round(evt.loaded * 99 / evt.total);
        document.getElementById('progressNumber').innerHTML = percentComplete.toString() + '%';
        $("#pro").show();
        $("#notrepeat").css("width", percentComplete.toString() + '%');
        document.getElementById("submit").disabled = true;
    }
    else {
        document.getElementById('progressNumber').innerHTML = 'unable to compute';
    }
}


function uploadComplete(evt) {
    /* 服务器端返回响应时候触发event事件*/
    alertModal(evt.target.responseText);
}
function uploadFailed(evt) {
    alertModal("上传错误.");
}
function uploadCanceled(evt) {
    alertModal("上传已取消.");
}

function uploadComplete(evt) {
    /* 服务器端返回响应时候触发event事件*/
    // alert(evt.target.responseText);
}