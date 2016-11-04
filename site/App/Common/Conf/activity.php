<?php

/**
 * 活动相关配置
 * CT: 2014-09-12 15:00 by YLX
 * UT: 2015-05-14 14:51 by YLX
 */

return array(

    // 活动相关配置
    'ACTIVITY_UNDERTAKER' => array('1' => '主办方', '2' => '承办方', '3' => '协办方', '4' => '特别鸣谢', '5' => '其他'), // 承办机构类型
    'ACTIVITY_TICKET_DEFAULT_VERIFY_TIME' => 10, // 票务默认可验证次数
    'ACTIVITY_TICKET_STATUS' => array(0 => '未发送', 1 => '发送失败', 2 => '已发送', 3 => '已查看', 4 => '已签到', 5 => '正在发送'), // 电子票状态
    'ACTIVITY_TICKET_STATUS_TAG' => array(0 => 'nameo', 1 => 'nameo', 2 => 'nameg', 3 => 'name0', 4 => 'nameb', 5 => 'nameb'), // 电子票状态对应HTML颜色标签
    'ACTIVITY_TICKET_SIGNIN_STATUS' => array(1 => '扫码签到', 2 => '手动签到', 3 => '现场报名'), // 签到状态
);
