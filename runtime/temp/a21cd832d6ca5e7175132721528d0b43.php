<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:32:"../themes/admin/login\index.html";i:1523252490;}*/ ?>
<!DOCTYPE html>
<html class="loginHtml">
<head>
	<meta charset="utf-8">
	<title>登录--诉舍后台系统</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="icon" href="./favicon.ico">
	<link rel="stylesheet" href="/static/admin/new/css/layui.css" media="all" />
	<link rel="stylesheet" href="/static/admin/new/css/public.css" media="all" />
</head>
<body class="loginBody">
	<form class="layui-form">
		<div class="login_face" style="background: white;"><img  style="width: 80px;height: 90px;    margin: 3px 7px 0px;" src="/static/admin/new/images/face.png" class="userAvatar"></div>
		<div class="layui-form-item input-item">
			<label for="userName">用户名</label>
			<input type="text" placeholder="请输入用户名" id="userName" class="layui-input" lay-verify="required">
		</div>
		<div class="layui-form-item input-item">
			<label for="password">密码</label>
			<input type="password" placeholder="请输入密码" id="password" class="layui-input" lay-verify="required" >
		</div>
		<div class="layui-form-item input-item" id="imgCode" style="    width: 140px;">
			<label for="code">验证码</label>
			<input type="text" placeholder="请输入验证码" id="code" class="layui-input"  lay-verify="required" maxlength="4">
			<!--<img src="/static/admin/new/images/code.jpg">-->
		</div>
		<img src="<?php echo captcha_src(); ?>" alt="点击更换" title="点击更换" onclick="this.src='<?php echo captcha_src(); ?>?time='+Math.random()" style="float:right;cursor: pointer;    margin-top: -51px;" class="captcha">
		<div class="layui-form-item">
			<button class="layui-btn layui-block" lay-filter="login" lay-submit>登录</button>
		</div>
		<div class="layui-form-item layui-row">
			<a href="javascript:;" class="seraph icon-qq layui-col-xs4 layui-col-sm4 layui-col-md4 layui-col-lg4"></a>
			<a href="javascript:;" class="seraph icon-wechat layui-col-xs4 layui-col-sm4 layui-col-md4 layui-col-lg4"></a>
			<a href="javascript:;" class="seraph icon-sina layui-col-xs4 layui-col-sm4 layui-col-md4 layui-col-lg4"></a>
		</div>
	</form>
	<script type="text/javascript" src="/static/admin/new/layui.js"></script>
	<script type="text/javascript" src="/static/admin/new/js/login.js"></script>
	<!--<script type="text/javascript" src="/static/admin/new/js/cache.js"></script>-->
</body>
</html>