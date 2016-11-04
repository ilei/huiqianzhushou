define(['jquery', 'validate', 'homeCommon'], function($){
    $(document).ready(function(){
        $('#edit_pwd').validate({
            errorClass: "error",
            errorPlacement: function(error, element){
                $(element).parent().siblings(".tishinr").html(error);
            },
            rules: {
                old_password: {
                    required: true,
                    rangelength: [6, 18],
                    remote: {
                        url: "/Information/check?type=old_pass",
                        type: "post",
                        data: {
                            field: function() {
                                return $( "#old_password" ).val();
                            }
                        }
                    }
                },
                password: {
                    required: true,
                    rangelength: [6, 18]
                },
                repassword: {
                    required: true,
                    equalTo: "#password"
                }
            },
            messages: {
                old_password: {
                    required: "原密码不能为空",
                    rangelength: "密码必须为6到18个字符",
                    remote: "原密码不正确"
                },
                password: {
                    required: "新密码不能为空",
                    rangelength: "新密码必须为6到18个字符"
                },
                repassword: {
                    required: "确认密码不能为空",
                    equalTo: "两次填写的密码不一致"
                },
            }
        });
    });
});
