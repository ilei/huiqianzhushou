<div class="error">
<h1><?php echo strip_tags($e['message']);?></h1>
<div class="content">
<?php if(isset($e['file'])) {?>
<div class="info">
<div class="title">
<h3>错误位置</h3>
</div>
<div class="text">
<p>FILE: <?php echo $e['file'] ;?> &#12288;LINE: <?php echo $e['line'];?></p>
</div>
</div>
<?php }?>
<?php if(isset($e['trace'])) {?>
<div class="info">
<div class="title">
<h3>TRACE</h3>
</div>
<div class="text">
<p><?php echo nl2br($e['trace']);?></p>
</div>
</div>
<?php }?>
</div>
</div>
<?php
/**
 * 错误跳转对应的模板文件
 *
 * CT: 2014-09-18 10:00 by YLX
 * UT: 2014-11-03 17:00 by YLX
 */
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>404</title>
	<!-- Bootstrap -->
	<link rel="stylesheet" type="text/css" href="<?php echo C('PAGE_404_BOOTSTRAP'); ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo C('PAGE_404_FONT-AWESOME'); ?>">
	<link rel="stylesheet" type="text/css" href="<?php echo C('PAGE_404_TPL'); ?>">


	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="http://cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="http://cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body class="bodybg">
<?php echo C('MEDIA_JS.JQUERY'); ?>
<?php echo C('MEDIA_JS.BOOTSTRAP'); ?>
<import type='js' file="common.js.common" />
<!-- Main Starting -->
<div class="container">
	<div class="row bg404">
		<div class="col-xs-6 col-xs-offset-5">
			<h1 class="text404">404！</h1>
			<ul class="jumph">
				<li>What？被绑票了！</li>
				<li>网页君去火星出差了</li>
				<li>雾霾太大啥都看不到了。</li>
				<li>你输入了错误的网址？</li>
			</ul>
			<h3>火星太危险，赶紧回地球吧.</h3>
			<a type="button" class="btn btn-primary btn-lg mt20" href="http://www.meetelf.com">返回首页</a>
		</div>
	</div>
	<!--    <div class="row text-center mt30">-->
	<!--        Copyright © 2014-2016 版权所有 天津云脉三六五科技有限公司-->
	<!--    </div>-->
	<div class="row text-center mt30">
		© 会签助手
	</div>
</div>
</body>
</html>
<style>
	@CHARSET "UTF-8";
	a,
	a:hover,
	a:focus { outline: none;}
	.hiden{display:none;}
	.container {
		width: 1000px;
		max-width: none !important;
	}
	body { word-wrap:break-word;}
	.clear{clear:both;}
	.pointer{cursor:pointer;}
	.mt0 { margin-top: 0;}
	.mt2 { margin-top: 2px;}
	.mt3 { margin-top: 3px;}
	.mt4 { margin-top: 4px;}
	.mt5 { margin-top: 5px;}
	.mt6 { margin-top: 6px;}
	.mt7 { margin-top: 7px;}
	.mt8 { margin-top: 8px;}
	.mt9 { margin-top: 9px;}
	.mt-10 { margin-top: -10px;}
	.mt-15 { margin-top: -15px;}
	.mt10 { margin-top: 10px;}
	.mt13 { margin-top: 13px;}
	.mt16 { margin-top: 16px;}
	.mt20 { margin-top: 20px;}
	.mt23 { margin-top: 23px;}
	.mt30 { margin-top: 30px;}
	.m0 { margin:auto;}
	.pd0 { padding: 0;}
	.mb0 { margin-bottom: 0;}
	.mb10 { margin-bottom: 10px;}
	.mb20 { margin-bottom: 20px;}
	.mb30 { margin-bottom: 30px;}
	.mb40 { margin-bottom: 40px;}
	.ml5 { margin-left: 5px;}
	.ml-1 { margin-left: -1px;}
	.ml-5 { margin-left: -5px;}
	.ml-12 { margin-left: -12px;}
	.ml12 { margin-left: 12px;}
	.ml18 { margin-left: 18px;}
	.ml30 { margin-left: 30px;}
	.ml40 { margin-left: 40px;}
	.ml75 { margin-left: 75px;}
	.ml80 { margin-left: 80px;}
	.mr4 { margin-right: 4px;}
	.mr5 { margin-right: 5px;}
	.mr10 { margin-right: 10px;}
	.mr12 { margin-right: 12px;}
	.mr25 { margin-right: 25px;}

	.pdlf0 { padding-left: 0;}
	.pdlf10 { padding-left: 10px;}
	.pdlf30 { padding-left: 30px;}
	.alert-pading { padding: 10px 15px;}
	.width30 { width: 30px;}
	.width40 { width: 40px;}
	.width60 { width: 60px;}
	.width75 { width: 75px;}
	.width80 { width: 80px;}
	.width92 { width: 92px;}
	.width104 { width: 104px;}
	.width150 { width: 150px;}
	.width190 { width: 190px;}
	.width200 { width: 200px;}
	.width420 { width: 420px;}
	.width500 { width: 500px;}
	.width600 { width: 600px;}
	.width675 { width: 675px;}
	.width798 { width: 778px;}

	.height90 { height: 90px;}
	.b1 { border: 1px solid #ddd;}
	.heightlist { height: 680px; border: 1px solid #ddd;}
	.heightlistsm { height: 680px;}

	.word-wrap {
		word-wrap: break-word;
		white-space: pre-wrap;
		width:100%;
		table-layout:fixed;
	}

	li > a > label { cursor: pointer;}
	/*提示*/
	.tishinr {
		min-height:30px;
		color: #d9534f;
	}
	.tishihidden { visibility: hidden !important;}
	.tswidth190 {
		width: 190px;
		min-width: 190px;
		margin-left: 15px;
	}
	.tswidth200 {
		width: 200px;
		min-width: 200px;
		margin-left: 15px;
	}
	.pding6 { padding: 6px;}

	.color0 { color: #000000;}
	.colorr { color: #ff0000;}
	/*跳转页面*/
	.pagemain { background-color: #FFF; margin-top: 100px;}
	.jpfont1 { font-weight: 900; margin-top: 120px;  font-size: 48px;}
	.jumph {  min-height: 60px;}
	/*404*/
	.bg404 {
		background-image:url(http://www.meetelf.com/Public/common/images/404.png);
		background-repeat:no-repeat;
		height: 465px;
		margin-top: 50px;
	}
	.text404 { font-weight: 900; margin-top: 150px;  font-size: 56px; color: #f15e4c;}
	/*按钮*/
	.mybtn {
		-moz-user-select: none;
		background-image: none;
		border: 1px solid #d3d5d9;
		cursor: pointer;
		height: 34px;
		border-radius: 0;
		color: #666;
		background-color: #f5f5f5;
		margin-right: 10px;
		padding: 0 10px 0 10px;
		line-height: 30px;
	}
	.mybtn.active:hover,
	.mybtn.active:focus {
		background-color: #19abd6;
	}
	.mybtn.active {
		color: #FFF;
		background-color: #43badd;
		border: 1px solid transparent;
		box-shadow: 0 0 0 rgba(0, 0, 0, 0.125) inset;
	}
	.mybtn:hover,
	.mybtn:focus {
		color: #FFF;
		background-color: #43badd;
		border: 1px solid transparent;
	}
	.mybtn-93 { width: 93px;}

	/*删除*/
	.deletebtn {
		-moz-user-select: none;
		background-image: none;
		border: 1px solid #d3d5d9;
		cursor: pointer;
		display: block;
		width: 115px;
		height: 32px;
		border-radius: 0;
		color: #666;
		background-color: #f5f5f5;
		margin-right: 10px;
		padding-top: 4px;
	}
	.deletebtn.active:hover,
	.deletebtn.active:focus {
		background-color: #e93f29;
	}
	.deletebtn.active {
		color: #fff;
		background-color: #f16250;
		border: 1px solid transparent;
		box-shadow: 0 0 0 rgba(0, 0, 0, 0.125) inset;
	}
	.deletebtn:hover,
	.deletebtn:focus {
		color: #fff;
		background-color: #f16250;
		border: 1px solid transparent;
	}

	/*下拉按钮*/
	.dropdown-mybtn {
		display: block;
		height: 32px;
		border-radius: 0;
		padding-top: 4px;
	}
	/*去圆角*/
	.radius0 { border-radius: 0;}
	/*去row左右*/
	.row0 { margin-left: 0; margin-right: 0;}

	/*认证信息图片大小*/
	.cardimg { max-width:200px;}
	.voteimg { max-width:300px;}
	/*头像图片大小*/
	.img45 { width: 45px; height: 45px;}
	.img56 { width: 56px; height: 56px;}
	.img70 { width: 70px; height: 70px;}
	.homeimg { width: 110px; height: 110px;}
	/*
     * 分页
     */
	.pagination > .active > a,
	.pagination > .active > span,
	.pagination > .active > a:hover,
	.pagination > .active > span:hover,
	.pagination > .active > a:focus,
	.pagination > .active > span:focus {
		background-color: #43badd;
		border-color: #43badd;
		color: #fff;
	}
	.pagination > li > a, .pagination > li > span {
		color: #666666;
		margin-left: 5px;
	}
	.pagination > li > a:hover,
	.pagination > li > span:hover,
	.pagination > li > a:focus,
	.pagination > li > span:focus {
		background-color: #eee;
		border-color: #ddd;
		color: #000;
	}
	.pageform {
		padding: 5px 12px;
		width: 50px;
		height: 29px;
		font-size: 14px;
		border: 1px solid #ddd;
	}
	.pageform:focus {
		border-color: #ccc;
		box-shadow: 0 0px 0px rgba(0, 0, 0, 0.075) inset, 0 0 0px rgba(102, 175, 233, 0.6);
	}

	.determinebtn {
		padding: 5px 12px;
		font-size: 12px;
		color: #666;
		border-radius: 0;
	}


	/*
     * 背景
     */
	.bodybg {
		background-color: #f4f4f4;
		background-image:url(http://www.meetelf.com/Public/common/images/bg.gif);
		font-family: "Microsoft Yahei";
		margin: 0;
		font-size: 14px;
		line-height: 1.42857143;
		color: #666;
	}

	.bodyform {
		font-family: "Microsoft Yahei";
		margin: 0;
		font-size: 14px;
		line-height: 1.42857143;
		color: #666;
	}
	/*
     * 头部
     */
	.header {
		padding-top: 20px;
		margin-bottom: 26px;
		height: 77px;
		background-color: #fff;
		border-top: 4px solid #43badd;
		border-bottom: 1px solid #ccc;
	}
	.topwd {
		display: block;
		padding-left: 40px;
		height: 34px;
		background-image:url(http://www.meetelf.com/Public/common/images/logo_top.jpg);
		background-repeat: no-repeat;
		font-size: 18px;
		line-height: 34px;
	}
	.topwd:hover,
	.topwd:focus {color: #0079dd; text-decoration: none;}
	.ulgeticon { list-style: none outside none;}

	a { color:#666;}
	a:hover { color: #43badd; text-decoration: none;}
	.user_logout { color: #666;}
	.user_logout:hover,
	.user_logout:focus { color: #e93f29; text-decoration: none;}

	.headicon { float: left; width: 30px; height: 30px;}
	.ligeticon { float: left; margin-left: 30px; width: 24px;}
	.ligeticon > a { width: 18px; font-size: 24px; line-height: 24px;}

	.mybadge {
		position: absolute;
		float: right;
		width: 14px;
		height: 14px;
		display: inline-block;
		font-size: 1px;
		line-height: 1;
		color: #f15e4c;
		text-align: center;
		white-space: nowrap;
		vertical-align: baseline;
		background-color: #f15e4c;
		border-radius: 10px;
		border: 2px solid #fff;
	}
	.mybadge:empty { display: none;}

	/*
     * 底部
     */
	.footer {
		margin-top: 26px;
		width: 100%;
		height: 70px;
		background-color: #b8b9b9;
		font-size: 12px;
		line-height: 35px;
		color: #fff;
	}
	/*
     * 主体
     */
	.main {
		min-height: 800px;
		background-color: #fff;
		border: 1px solid #ccc;
		overflow: hidden;
	}
	/*
     * 左边栏
     */
	.main-left {
		width: 14%;
		padding-bottom: 100000px;
		margin-bottom: -100000px;
		background-color: #f5f5f5;
	}
	ul.left-menu {
		padding-left: 0;
		list-style: none;
	}
	ul.left-menu li {
		margin-top: 0;
	}
	ul.left-menu li.left-menu-home-active a,
	ul.left-menu li.left-menu-home a {
		padding: 25px 10px;
	}
	ul.left-menu li, ul.left-menu label{
		font-weight: normal;
	}
	.left-menu > li > a:focus,
	.left-menu > li.active > a,
	.left-menu > li.active > a:hover,
	.left-menu > li.active > a:focus {
		background-color: #fff;
		color: #43badd;
		border-left: 2px solid #43badd;
		border-right: 1px solid #fff;
	}
	.left-menu > li > a {
		padding: 10px;
		display: block;
		text-align: center;
		border-top: 1px solid #fff;
		border-right: 1px solid #ebebeb;
		border-bottom: 1px solid #ebebeb;
		border-left: 2px solid #f5f5f5;
		border-radius: 0;
	}
	.leftbarlast {
		position: absolute;
		left: 0;
		width: 100%;
		height: 100%;
		border-top: 1px solid #fff;
		border-right: 1px solid #ebebeb;
	}
	/*
     * home
     */
	.left-menu > li.left-menu-home > a {
		border-top: 0;
	}
	.left-menu > li.left-menu-home-active > a,
	.left-menu > li.left-menu-home > a:hover,
	.left-menu > li.left-menu-home > a:focus {
		border-top: 0;
		background-color: #f5f5f5;
		border-left: 0;
		color: #43badd;
	}
	/*
     * 右侧栏导航
     */
	.main-right {
		padding:  0 30px;
		width: 86%;
	}
	.ymtitle {
		padding-top: 30px;
		font-size: 16px;
		color: #000;
	}
	.h5title {
		font-size: 14px;
		color: #666;
	}
	.ymnaw { margin-top: 10px;}
	.ymbtn { padding-left: 20px;}
	.ymbtn > li { margin-bottom: 0;}
	.ymbtn > li.active > a,
	.ymbtn > li.active > a:hover,
	.ymbtn > li.active > a:focus {
		background-color: #43badd;
		color: #fff;
		border-color: #43badd #43badd transparent;
	}
	.ymbtn > li > a {
		background-color: #f5f5f5;
		padding: 5px 30px;
		margin-right: 5px;
		border-width: 1px;
		border-color: #ddd #ddd transparent;
	}
	.ymbtn > li > a:hover,
	.ymbtn > li > a:focus {
		background-color: #e6e6e6;
		color: #666;
		border-width: 1px;
		border-color: #ddd #ddd transparent;
	}
	.rightmain { padding: 20px 10px;}
	/*
     * 右侧栏主体
     */
	/*字体颜色*/
	.signature { color: #b2b2b2;}
	.tableme59 .signature {
		display: block;
		width: 330px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}
	.blacknm { color: #000000;}
	nameo { color: #f15e4c;}
	nameb { color: #43badd;}
	namebl { color: #1290ce;}
	name0 { color: #000;}
	nameh { color: #666;}
	nameh1 { color: #c0c0c0;}
	nameg { color: #5cb85c;}
	.no-message { margin: 30px 0 15px; color: #999;}
	/*主页消息表格*/
	.homemessage {
		padding: 13px 18px;
		margin: auto;
		margin-bottom: 14px;
		width: 100%;
		background-color: #fff;
		font-size: 12px;
	}
	/*消息表格*/
	.themessage {
		padding: 13px 18px;
		margin: auto;
		margin-bottom: 14px;
		width: 100%;
		border: 1px solid #ddd;
		background-color: #FFF;
		font-size: 12px;
	}
	.margin0 { margin: auto;}
	.themessage:hover,
	.themessage:focus {
		background-color: #e5f5fa;
		border: 1px solid #43badd;
	}
	.themessageold { background-color: #f5f5f5;}
	.themessageold:hover,
	.themessageold:focus {
		background-color: #ededed;
		border: 1px solid #d3d3d7;
	}
	.left4 { width: 4%;}
	.left10 { width: 10%;}
	.left15 { width: 15%;}
	.right85 { width: 85%;}
	.right90 { width: 90%;}
	.right88 { width: 88%;}
	.right96 { width: 96%;}
	.mainheight { width: 600px; height: 34px; margin-top: 5px; overflow: hidden;}
	.skip_url,
	.skip_url:hover,
	.skip_url:focus { cursor: pointer;}
	/*回复*/
	.message-reply > a { color: #88c92d; font-size: 14px;}
	.message-reply > a:hover,
	.message-reply > a:focus { color: #66af00;}
	/*删除*/
	.message-delete > a { color: #f15e4c; font-size: 14px;}
	.message-delete > a:hover,
	.message-delete > a:focus { color: #df301b;}
	/*已回复*/
	.message-old > a { color: #666; font-size: 14px;}

	.msyinhao { padding-left: 20px; vertical-align: top; }
	.mswenzi { padding-left: 20px;}
	.msmain { padding-right: 25px;}
	.form-control { border-radius: 0;}
	.noresize { resize: none;}

	.form-control[disabled], .form-control[readonly], fieldset[disabled] .form-control { background-color: #FFF;}
	/*提示字体颜色*/
	.prompt-success {
		padding: 6px 20px;
		background-color: #dff0d8;
		color: #3c763d;
		border: 1px solid #d6e9c6;
	}
	.custom-error{
		color: #ca0000;
		font-size: 14px;
		font-weight: normal;
		line-height: 28px;
	}
	/* 页面内容顶部tab所有a标签为小手 */
	.ymnaw .nav-tabs>li.active>a, .nav-tabs>li.active>a:hover, .nav-tabs>li.active>a:focus { cursor: pointer; }
	/*页面输入框默认颜色*/
	.form-control::-moz-placeholder {
		color: #ccc;
		opacity: 1;
	}
	.form-control:-ms-input-placeholder {
		color: #ccc;
	}
	.form-control::-webkit-input-placeholder {
		color: #ccc;
	}
	/*首次登陆*/
	.login-fmod { margin-bottom: 0; padding: 10px 30px;}
	.login-fmod-content { color: #bbb;}


</style>
