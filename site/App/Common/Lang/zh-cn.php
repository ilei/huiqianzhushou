<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return array(
    //活动表
    'k__activity' => array(
        'status'=> array(
            '0' => '未发布',
            '1' => '活动中',
            '2' => '已结束',
            '3' => '已关闭',
            '4' => '未审核',
            '5' => '已通过',
            '6' => '已拒绝'
        ),
        'is_public'=> array(
            '0'=> '未公开',
            '1'=> '公开'
        ),
        'is_verify'=>array(
            '0'=>'未审核',
            '1'=>'已审核',
            '2'=>'已提交',
            '3'=>'已拒绝'
        )
    ),
    'k__order'=>array(
        'status'=>array(
            '0'=>'新订单',
            '1'=>'支付成功',
            '2'=>'支付失败',
            '3'=>'已取消',
            '4'=>'已发货',
            '5'=>'交易成功',
            '6'=>'待审核',
            '7'=>'审核通过',
            '8'=>'审核被拒',
            '9'=>'被删除'
        )
    ),
    'k__activity_userinfo'=>array(
        'type'=>array(
            '0'=>'未知',
            '1'=>'本社团用户报名',
            '2'=>'平台内非本社团用户报名',
            '3'=>'平台外用户报名',
            '4'=>'社团自行添加'
        )
    ),
    'k__activity_user_ticket'=>array(
        'status'=>array(
            '0'=>'未发送',
            '1'=>'发送失败',
            '2'=>'已发送',
            '3'=>'已查看',
            '4'=>'已签到'
        ),
    ),
    'k_model_guid_not_empty' => 'GUID不能为空',
    'k_model_guid_len_error' => 'GUID长度必须是32位',
    'k_model_mobile_not_empty' => '电话号码不能为空',
    'k_model_mobile_format_error' => '电话号码格式不正确',
    'k_model_actform_act_guid_not_empty' => '活动GUID不能为空',
    'k_model_actform_act_guid_len_error' => '活动GUID长度必须是32位',
    'k_model_actform_name_not_empty' => '表单项名称不能为空',
    'k_model_actform_name_len_error' => '表单项名称长度必须是2至10位',
    'k_model_act_userguid_not_empty' => '用户GUID不能为空',
    'k_model_act_userguid_len_error' => '用户GUID长度必须是32位',
    'k_model_act_name_not_empty' => '活动名称不能为空',
    'k_model_act_name_len_error' => '活动名称长度必须是2至50个字符',
    'k_model_act_content_not_empty' => '活动内容不能为空',
    'k_model_act_content_len_error' => '活动内容长度必须是2至10000个字符',
    'k_model_act_starttime_not_empty' => '活动开始时间不能为空',
    'k_model_act_endtime_not_empty' => '活动结束时间不能为空',
    'k_model_act_starttime_lt_now' => '活动开始时间不能小于当前时间',
    'k_model_act_starttime_lt_end' => '活动开始时间不能小于结束时间',
    'k_model_act_province_not_empty' => '活动省份信息不能为空',
    'k_model_act_city_not_empty' => '活动城市信息不能为空',
    'k_model_act_address_not_empty' => '活动具体地址信息不能为空',
    'k_model_act_lat_not_empty' => '活动经度信息不能为空',
    'k_model_act_lng_not_empty' => '活动维度信息不能为空',

    'k_model_org_name_not_empty' => '主办方名称不能为空',
    'k_model_org_name_len_error' => '主办方名称长度必须是2至30个字符',

    'k_model_signin_username_not_empty' => '签到账户用户名不能为空',
    'k_model_signin_username_len_error' => '签到账户用户名长度为2至10个字符',

    'k_model_actformoption_build_guid_not_empty' => '表单GUID不能为空',
    'k_model_actformoption_build_guid_len_error' => '表单GUID长度必须是32位',
    'k_model_actformoption_value_not_empty' => '表单项名称不能为空',
    'k_model_actformoption_value_len_error' => '表单项名称长度长度必须是2至10个字符',

    'k_model_option_email_not_empty' => '邮箱不能为空',
    'k_model_option_email_format_error' => '邮箱格式不正确',
    'k_model_option_content_not_empty' => '内容不能为空',
    'k_model_option_content_error' => '内容格式不正确',

    'k_model_actsubject_name_not_empty' => '标签名称不能为空',
    'k_model_actsubject_name_len_error' => '便签名称长度为2至10个字符',

    'k_model_goods_cate_name_not_empty' => '商品类别名称不能为空',
    'k_model_goods_cate_name_len_error' => '商品类别名称长度为2至30个字符',
    'k_model_goods_cate_creater_not_empty' => '商品类别创建者不能为空',
    'k_model_goods_cate_creater_len_error' => '商品类别创建者长度为32个字符',

    'k_model_orderid_not_empty' => '订单ID不能为空',
    'k_model_orderid_len_error' => '订单ID长度为25个字符',
);
