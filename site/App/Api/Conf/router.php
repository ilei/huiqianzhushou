<?php
return array(
    // 路由配置
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES' => array(

        // 登陆
        array('login', 'Auth/login', '', array('method' => 'post')),
        array('login', 'Auth/login', '', array('method' => 'get')), // debug
        // 登出
        array('logout', 'Member/logout', '', array('method' => 'post')),
        array('logout', 'Member/logout', '', array('method' => 'get')), // debug
        // 重新登陆
        array('relogin', 'Member/relogin', '', array('method' => 'post')),
        array('relogin', 'Member/relogin', '', array('method' => 'get')), // debug
        // 忘记密码
        array('mobile-check', 'Auth/mobile_check', '', array('method' => 'post')), // 检查手机号码是否存在
        array('mobile-check', 'Auth/mobile_check', '', array('method' => 'get')), // 发送验证码
        array('mobile-check', 'Auth/mobile_check', '', array('method' => 'put')), // 检查验证码是否正确及超时
        array('password-forget', 'Auth/change_password', '', array('method' => 'put')), // 修改密码

        // 发送消息
        array('message', 'Message/send', '', array('method' => 'post')),
        array('message', 'Message/send', '', array('method' => 'get')),

        // 二度人脉
        // 社团相关
        array('contacts/two/org/:field/stat', 'Contact/org_two_stat', array('type' => 'org'), array('method' => 'get')),   //社团的人脉 - 统计 field: industry, edu, area, interest
        array('contacts/two/org/:field/list', 'Contact/org_two_list', array('type' => 'org'), array('method' => 'get')),   //社团的人脉 - 列表 field: industry, edu, area, interest
        // 社团相关
        array('contacts/two/activity/:field/stat/:aid', 'Contact/org_two_stat', array('type' => 'activity'), array('method' => 'get')),   //会场的人脉 - 统计 field: industry, edu, area, interest
        array('contacts/two/activity/:field/list/:aid', 'Contact/org_two_list', array('type' => 'activity'), array('method' => 'get')),   //会场的人脉 - 列表 field: industry, edu, area, interest
        // 人脉相关
        array('contacts/two/friend/:field/stat', 'Contact/friend_two_stat', '', array('method' => 'get')),   //云友的人脉 - 统计 field: industry, edu, area, interest
        array('contacts/two/friend/:field/list', 'Contact/friend_two_list', '', array('method' => 'get')),   //云友的人脉 - 列表 field: industry, edu, area, interest
        // 全部二度人脉
        array('contacts/two/org', 'Contact/org_two', array('type' => 'org'), array('method' => 'get')), //获取 全部 社团的人脉
        array('contacts/two/friend', 'Contact/friend_two', '', array('method' => 'get')), //获取 全部 社团的人脉
        array('contacts/two/activity/:aid', 'Contact/org_two', array('type' => 'activity'), array('method' => 'get')), //获取 全部 同活动的人脉
		
		//通讯录
        array('contacts/list', 'Contact/contacts_list', '', array('method' => 'post')), // 通讯录 
        array('contacts/list/two', 'Contact/contacts_two', '', array('method' => 'get')), // 通讯录


		//获得用户的全部报名活动 
		array('contacts/signup/list', 'Contact/activity_signup_list', '', array('method' => 'get')),

        // 一度人脉
        array('contacts/new', 'Contact/list_apply', '', array('method' => 'get')), // 新云友列表
        array('contacts/new', 'Contact/apply_contact', '', array('method' => 'post')), // 申请好友
        array('contacts/new', 'Contact/confirm_contact', '', array('method' => 'put')), // 确认好友申请
        array('contacts/search', 'Contact/search_contact', '', array('method' => 'get')), // 搜索云友
        array('contacts/:guid', 'Contact/detail', '', array('method' => 'get')), // 人脉详情
        array('contacts/:tid', 'Contact/del', '', array('method' => 'delete')), // 删除好友
        array('contacts', 'Contact/one', '', array('method' => 'get')), // 一度人脉列表

        // 任务
        array('tasks/done', 'Task/task_finish_list', '', array('method' => 'get')), // 任务列表 已完成
        array('tasks/:type', 'Task/task_check_user_info', '', array('method' => 'get')), // 检查用户头像、地区、行业是否填写
        array('tasks', 'Task/task_list', '', array('method' => 'get')), // 任务列表 未接受&已接受

        // 我的
        array('me/mobile', 'Member/sms_check_mobile', '', array('method' => 'post')), // 手机验证 - 验证手机并发送手机验证码
        array('me/mobile', 'Member/send_check_mobile', '', array('method' => 'put')), // 手机验证 - 检查手机验证码是否正确并返回

        array('me', 'Member/info', '', array('method' => 'get')), // 我的详情
        array('me', 'Member/edit', '', array('method' => 'put')), // 编辑我的信息
        array('error', 'Member/error_log', '', array('method' => 'post')), // 上传错误日志
        array('error', 'Member/error_log', '', array('method' => 'get')), // 上传错误日志 - debug
        array('portrait', 'Member/portrait', '', array('method' => 'post')), // 修改头像
        array('portrait', 'Member/portrait', '', array('method' => 'get')),  // 修改头像 - debug
        array('industries', 'Industry/index', '', array('method' => 'get')), // 行业列表
        array('password', 'Member/check_passwd', '', array('method' => 'post')), // 检查密码
        array('password', 'Member/change_passwd', '', array('method' => 'put')), // 修改密码
        array('password', 'Member/change_passwd', '', array('method' => 'get')), // 修改密码 - debug
        array('opinions', 'Opinion/add', '', array('method' => 'post')), // 意见反馈
        array('opinions', 'Opinion/add', '', array('method' => 'get')), // 意见反馈 - debug
        array('credentials', 'Member/upload_credentials', '', array('method' => 'post')), // 上传身份证
        array('status', 'Member/user_status', '', array('method' => 'get')), // 用户实名认证状态

        
        // 兴趣
        array('interests/search', 'Interest/search', '', array('method'=>'get')), // 搜索兴趣
        array('interests', 'Interest/add', '', array('method'=>'post')), // 增加兴趣
        array('interests', 'Interest/add', '', array('method'=>'get')), // 增加兴趣 - debug
        // 我的 公司
        array('companies', 'Company/add', '', array('method' => 'post')), // 增加公司
        array('companies', 'Company/add', '', array('method' => 'get')), // 增加公司 - debug
        array('companies/:guid', 'Company/edit', '', array('method' => 'put')), // 修改公司
        array('companies/:guid', 'Company/delete', '', array('method' => 'delete')), // 删除公司

        // 群组
        array('groups/:group_disc_guid/members', 'Group/member_add', '', array('method'=>'post')), // 拉人
        array('groups', 'Group/add', '', array('method'=>'post')), // 创建群组
        array('groups/:group_disc_guid/type/:type/members', 'Group/list_members', '', array('method'=>'get')), // 获取群组成员列表
        array('groups/:group_disc_guid/members/:del_uid', 'Group/member_del', '', array('method'=>'delete')), // 退出群组
        array('groups/:group_disc_guid', 'Group/info', '', array('method'=>'get')), // 获取群组详情
        array('groups/:group_disc_guid', 'Group/rename', '', array('method'=>'put')), // 群组重命名
        array('groups', 'Group/list_by_user', '', array('method'=>'get')), // 获取用户所在群组列表

        // 社团
        array('orgs/me/invite', 'Member/get_org_invite', '', array('method' => 'get')), //社团 - 获取当前用户的社团邀请列表
        array('orgs/me/invite', 'Member/handle_org_invite', '', array('method' => 'put')), //社团 - 处理社团邀请信息
        array('orgs/me/refuse', 'Member/get_refuse_msg', '', array('method' => 'get')), //社团 - 获取社团拒绝理由
        array('orgs/me/blacklist/:oid', 'Member/del_org_black', '', array('method' => 'delete')), //社团 - 从黑名单中删除当前社团
        array('orgs/me/blacklist', 'Member/get_org_blacklist', '', array('method' => 'get')), //社团 - 获取当前用户的社团黑名单列表

        array('orgs/apply/scan', 'Org/scan', '', array('method' => 'post')), // APP扫描社团邀请注册二维码则自动加入该社团
        array('orgs/check/member', 'Org/check_member', '', array('method' => 'get')), // 检查社员是否在社团下
        array('orgs/:org_guid', 'Org/info', '', array('method' => 'get')), // 社团详情
        array('orgs', 'Org/list_by_user', '', array('method' => 'get')), // 社团列表

        // 活动
        array('activities/org/:org_guid', 'Activity/activity_list', '', array('method' => 'get')), // 活动列表
        array('activities/status/:activity_guid', 'Activity/is_over', '', array('method' => 'get')), // 活动是否结束
        array('activities/read/all', 'Activity/all_read', '', array('method' => 'post')), //所有活动设置为已读

//        array('update', 'Download/app_check', '', array('method' => 'get')), // APK更新信息

        array('refuse', 'Refuse/refuse_content', '', array('method' => 'get')), // 实名认证拒绝理由

        array('timeline', 'Timeline/add', '', array('method' => 'post')), // 增加时间轴项


        /**
         * 签到APP and PC端离线签到
         */
        array('signin/login', 'Signin/Auth/login', '', array('method' => 'post')), // 登录
        array('signin/relogin', 'Signin/Index/relogin', '', array('method' => 'post')), // 登录
        array('signin/logout', 'Signin/Index/logout', '', array('method' => 'post')), // 退出登录

        array('signin/user', 'Signin/Index/signin_check_user', '', array('method' => 'post')), // 检查用户是否存在
        array('signin/user', 'Signin/Index/signin', '', array('method' => 'put')), // 进行用户签到
        array('signin/app/activity', 'Signin/Index/activity_list', '', array('method' => 'get')), // 手机端 - 获取活动列表
        array('signin/app/opinion', 'Signin/Index/opinion_app', '', array('method' => 'post')), // 手机端 - 意见反馈
        array('signin/check/token', 'Signin/Index/app_check_token', '', array('method' => 'get')), // 检查token

        array('signin/userinfo', 'Signin/Pc/get_user_info', '', array('method' => 'get')), // 获取用户及签到帐号信息
        array('signin/activity/list', 'Signin/Pc/get_activity_list', '', array('method' => 'get')), // 获取活动信息
        array('signin/activity/ticketinfo', 'Signin/Pc/get_signin_user_ticket_info', '', array('method' => 'post')), // 获取活动票务
        array('signin/pc/pwd', 'Signin/Pc/ckeckPwd', '', array('method' => 'post')), // 验证密码
        array('signin/pc', 'Signin/Pc/down', '', array('method' => 'get')), // 下载活动及票务数据
        array('signin/pc', 'Signin/Pc/up', '', array('method' => 'post')), // 上传活动及票务数据

        /**
         * 会签助手APP
         */
        array('app/login', 'App/Auth/login', '', array('method' => 'post')),// 登陆
        array('app/login', 'App/Auth/login', '', array('method' => 'get')),// 登陆
        array('app/register/get_code', 'App/Register/sendCode', '', array('method' => 'get')),// 发送验证码
        array('app/register/check_code', 'App/Register/checkCode', '', array('method' => 'put')),// 检测验证码
        array('app/register', 'App/Register/reg', '', array('method' => 'post')),// 注册
        array('app/user/info', 'App/User/get_user_info', '', array('method' => 'get')),// 个人详情
        array('app/user/set_pw', 'App/User/set_pw', '', array('method' => 'put')),// 个人详情
        array('app/user/edit', 'App/User/edit', '', array('method' => 'put')),// 个人信息修改
        array('app/account', 'App/Account/get_account', '', array('method' => 'get')),// 我的账户
        array('app/ticket', 'App/Ticket/get_tickect', '', array('method' => 'get')),// 我的票务--未完成
        array('app/ticket/success', 'App/Ticket/get_tickect_success', '', array('method' => 'get')),// 我的票务--未完成
        array('app/activity/put', 'App/Activity/get_activity', '', array('method' => 'get')),// 我发布的活动
        array('app/activity/common', 'App/Activity/get_activity_common', '', array('method' => 'get')),// 公开活动
        array('app/activity/search', 'App/Activity/search_activity', '', array('method' => 'get')),// 搜索活动
        array('app/feedback', 'App/Feedback/feedback', '', array('method' => 'post')),// 我的票务--未完成
//        array('app/version', 'App/Download/app_check', '', array('method' => 'post')),// 版本更新
    )
);