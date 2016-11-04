define(['jquery','validate', 'homeCommon'], function($){


    $("#form_id").validate({
        errorPlacement: function(error, element){
            if(error){
                element.siblings().html(error);
            }
        },
        rules: {

            //银行账号添加编辑
            bank_num: {
                required: true
            },
            open_bank: {
                required: true
            },

            //快递地址添加编辑
            express_name: {
                required: true
            },
            express_mobile: {
                required: true
            },
            express_address: {
                required: true
            },

            //发票信息添加编辑
            invoice_name: {
                required: true
            }
        },
        messages: {

            bank_num: {
                required: '账号不能为空'
            },
            open_bank: {
                required: '开户行地址不能为空'
            },


            express_name: {
                required: '收件人姓名不能为空'
            },
            express_mobile: {
                required: '收件人手机不能为空'
            },
            express_address: {
                required: '收件人地址不能为空'
            },


            invoice_name: {
                required: '名称不能为空'
            }
        }
    });

    $("#is_default").click(function(){
        var ck_status = $("input[type='checkbox']").prop('checked');
        if(ck_status){
            $("#is_default").attr('value','1');
        }else{
            $("#is_default").attr('value','0');
        }
    });

    $("#submit_button").click(function(){
        $("#form_id").submit();
    });
});

