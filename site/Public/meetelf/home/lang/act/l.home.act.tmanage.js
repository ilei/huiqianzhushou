define(['jquery'],function ($){
	var lang = {
		ticket_name_not_empty:'票务名称不能为空',
		ticket_name_len_error:'票务名称字符为2到10个字',
		ticket_num_not_empty:'票务数量不能为空',
		ticket_num_format_err:'票务数量必须为数字',
		ticket_verify_num_format_err:'票务验证次数必须为数字',
    ticket_saling:'售票中',
    ticket_stop_sale:'暂停售票',
    ticket_start_sale:'开始售票',
    ticket_name_exist:'票务名称已存在',
    ticket_num_not_lt_saled:'票务数量不能小于已出售的数量',
    ticket_start_lt_now:'售票时间不能小于当前时间',
    ticket_start_lt_default:'售票时间不能小于初始售票时间',
    ticket_start_rt_aend:'售票时间不能大于活动结束时间',
    ticket_start_rt_end:'售票开始时间不能大于售票结束时间',
    ticket_end_rt_end:'售票结束时间不能大于活动结束时间',
	};
  $.extend({lang:lang});
});
