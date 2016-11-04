/**
 *
 */
$(function () {
    /**
     * 提交登录信息到后台验证
     *
     * CT: 2014-12-02 10:50 by QXL
     */
    $('.js-login').click(function () {
        var obj = $(this);
        var account = $('input[name=account]').val();
        var password = $('input[name=password]').val();
        var data = {account: account, password: password};
        $.ajax({
            url: YM['authLogin'],
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function () {
                obj.button('loading');
            },
            success: function (data) {
                if (data.code == '200') {
                    window.location.href = YM['redirectPath'];
                } else if (data.code == '201') {
                    alertTips($('#tips-modal'), data.Msg);
                }
            },
            complete: function () {
                obj.button('reset');
            }
        });
    });
})