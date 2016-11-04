define(['jquery', 'lang', 'validate'], function($,lang){
$.validator.addMethod("isEmail", function(value, element){
		var pattern = /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/;
		return pattern.test(value);
	}, lang.email_format_err);

$("#regorg").validate({
		errorClass: "invalid",
                errorPlacement: function (error, element) {
                    element.parent().parent().parent().find('.tishinr').append(error);
                },
		rules: {
			email: {
				required: true,
				isEmail: true,
			}
		},
		messages: {
			email: {
				required: lang.user_not_empty,
			}
		}
	});
});