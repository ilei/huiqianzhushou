jQuery(function($) {
var mailArr = new Array("@qq.com","@163.com","@sina.com","@126.com","@tom.com",
	"@yahoo.com", "@139.com", "@outlook.com", "@gmail.com");
	$.fn.mailAutoTip = function(options) {
		var setting = {
			subBox: "#MailAutoTip",    //下拉框div
			subOp: "li",
			id: "#email , #email_address",   //email输入框的id属性,在其他地方调用需要在此添加输入框属性.
			mailArr: mailArr,
			hoverClass: "on",
			_cur: 0 /*index*/
		};
		var opts = $.extend({}, setting, options || {});
		//tipFun
		var tipFun = function(_v, o) {
				opts._cur = -1;
				var _that = o;
				$(opts.subBox).show();
				var str = "<ul>";
				//str += "<li id=\"e_type\">&nbsp;选择邮箱类型:</li>";
				var e = _v.indexOf("@");
				if (e == -1) {
					$.each(opts.mailArr, function(s, m) {
						str += '<li><a href="javascript:void(0)" >' + _v + m + "</a></li>";
					});
				} else {
					var _sh = _v.substring(0, e)
					var _se = _v.substring(e);
					var ind = 0;
					$.each(opts.mailArr, function(s, m) {
						if (m.indexOf(_se) != -1) {
							str += '<li><a href="javascript:void(0)" >' + _sh + m + "</a></li>";
							ind = 1;
						}
					});
					if (ind == 0) {
						str += '<li><a class="cur_val" href="javascript:void(0)" >' + _v + "</a></li>";
					}
				}
				str += "</ul>";
				$(opts.subBox).html(str); /*hover*/
				$(opts.subBox).find(opts.subOp).hover(function() {
					var _tempArr = $(opts.subBox).find(opts.subOp);
					var _size = _tempArr.size();
					for (var i = 0; i < _size; i++) {
						_tempArr.eq(i).removeClass(opts.hoverClass);
					}
					var _that = $(this);
					_that.addClass(opts.hoverClass);
				}, function() {
					var _that = $(this);
					_that.removeClass(opts.hoverClass)
				}); /*click*/
				$(opts.subBox).find(opts.subOp).each(function() {
					$(this).click(function(e) {
						if ($(e.target).attr("id") != "e_type") {
							$(opts.id).val($(e.target).html());
							$(opts.subBox).hide();
							e.stopPropagation();
						}
					});
				})
			}; /*itemFun*/
		var itemFun = function() {
				var _tempArr = $(opts.subBox).find(opts.subOp);
				var _size = _tempArr.size();
				for (var i = 0; i < _size; i++) {
					_tempArr.eq(i).removeClass(opts.hoverClass);
				}
				if (_size > 1) {
					if (opts._cur > _size - 1) {
						opts._cur = 0;
					}
					if (opts._cur < 0) {
						opts._cur = _size - 1;
					}
					_tempArr.eq(opts._cur).addClass(opts.hoverClass);
				} else {
					opts._cur = 0;
				}
			};
		$(opts.id).keyup(function(e) {
			var _that = $(this);
			if (_that.val() != "") {
				if (e.keyCode != 38 && e.keyCode != 40 && e.keyCode != 13 && e.keyCode != 27) {
					var _inputVal = _that.val();
					tipFun(_inputVal, _that);
				}
			} else {
				$(opts.subBox).hide();
			}
		}); 
		$(document).bind("click", function(e) {
			$(opts.subBox).hide();
		});
		$(document).keydown(function(e) {
			switch (e.keyCode) {
			case 40:
				//up
				opts._cur++;
				itemFun()
				break;
			case 38:
				//down
				opts._cur--;
				itemFun()
				break;
			default:
				break;
			}
		})
		$(opts.id).keydown(function(e) {
			var _temp = $(opts.subBox).find(opts.subOp);
			if (e.keyCode == 13) {
				// if (opts._cur != 0) {
					$(this).val(_temp.eq(opts._cur).text());
				// 	opts._cur = 0;
				// }
				$(opts.subBox).hide();
				e.stopPropagation();
				return false;
			}
		});
	}
});