<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:32:"../themes/admin/index\index.html";i:1523585533;s:42:"D:\wamp64\www\sushe\themes\admin\base.html";i:1523604429;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>诉舍后台系统</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <link rel="stylesheet" href="/static/js/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/static/css/font-awesome.min.css">
    <!--CSS引用-->
    
    <link rel="stylesheet" href="/static/css/admin.css">
    <!--[if lt IE 9]>
    <script src="/static/css/html5shiv.min.js"></script>
    <script src="/static/css/respond.min.js"></script>
    <![endif]-->
    <style>
        .titleImg{
            display: block;    width: 80px;    height: 80px;    margin: 0 19px 0;-webkit-border-radius: 50%;
        }
        .toptitle{ color: #ccc;    font-size: 22px;    float: left;    margin-left: 10px;    margin-top: 15px;}
    </style>
</head>
<body>
<div class="layui-layout layui-layout-admin">
    <!--头部-->
    <div class="layui-header header">
        <a href="" class="toptitle">诉舍后台管理系统</a>
        <a class="layui-nav-item toptitle" title="点击收缩菜单栏" id="slidedes" attr-type=1 style="margin-left: 26px;margin-top: 15px;cursor: pointer;">
            <img style="width: 35px;" src="/static/images/yingyong.png">
        </a>
        <ul class="layui-nav" style="position: absolute;top: 0;right: 20px;background: none;">

            <li class="layui-nav-item"><a style="cursor: pointer;" title="点击设置价格" onclick="setPrice();">当前律师函价格：<span style="color: red;"><?php echo $money; ?></span>&nbsp;元</a></li>
            <li class="layui-nav-item"><a href="" data-url="<?php echo url('jdroom/system/clear'); ?>" id="clear-cache"></a></li>
            <li class="layui-nav-item"><a href="/jdroom/Orderlist/index">待接单数：<span class="layui-badge"><?php echo $counts; ?></span></a></li>
            <li class="layui-nav-item"><a></a></li>
            <li class="layui-nav-item lockcms" pc>
                <a href="javascript:;"><i class="layui-icon seraph icon-lock" >&#xe638;</i>  <cite>锁屏</cite></a>
            </li>
            <li class="layui-nav-item">
                <a href="javascript:;"><?php echo session('admin_name'); ?></a>
                <dl class="layui-nav-child"> <!-- 二级菜单 -->
                    <dd><a href="<?php echo url('jdroom/change_password/index'); ?>">修改密码</a></dd>
                    <!--<dd><a href="<?php echo url('jdroom/login/logout'); ?>">退出登录</a></dd>-->
                    <dd><a style="cursor: pointer;" onclick="logout();">退出登录</a></dd>
                </dl>
            </li>
        </ul>
    </div>

    <!--侧边栏-->
    <div class="layui-side layui-bg-black">
        <div style="height: 150px;">
            <a style="margin-left: 20px;margin-top: 10px;margin-bottom: 10px;">
                <a><img class="titleImg"  src="/static/admin/images/timg.jpg"></a><br>
                <span style="margin-left: 10px;" id="showMe">你好！<?php echo session('admin_name'); ?>， 欢迎登录</span>
            </a>
        </div>
        <div class="layui-side-scroll" style="height: 500px;">
            <ul class="layui-nav layui-nav-tree">
                <!--<li class="layui-nav-item layui-nav-title"><a>管理菜单</a></li>-->
                <li class="layui-nav-item">
                    <a href="<?php echo url('jdroom/index/index'); ?>"><i class="fa fa-home"></i> 网站信息</a>
                </li>
                <?php if(is_array($menu) || $menu instanceof \think\Collection || $menu instanceof \think\Paginator): if( count($menu)==0 ) : echo "" ;else: foreach($menu as $key=>$vo): if(isset($vo['children'])): ?>
                        <li class="layui-nav-item ">
                            <a href="javascript:;"><i class="<?php echo $vo['icon']; ?>"></i> <?php echo $vo['title']; ?></a>
                            <dl class="layui-nav-child">
                                <?php if(is_array($vo['children']) || $vo['children'] instanceof \think\Collection || $vo['children'] instanceof \think\Paginator): if( count($vo['children'])==0 ) : echo "" ;else: foreach($vo['children'] as $key=>$v): ?>
                                    <dd><a href="<?php echo url($v['name']); ?>"><?php echo $v['title']; ?></a></dd>
                                <?php endforeach; endif; else: echo "" ;endif; ?>
                            </dl>
                        </li>
                    <?php else: ?>
                        <li class="layui-nav-item ">
                            <a href="<?php echo url($vo['name']); ?>"><i class="<?php echo $vo['icon']; ?>"></i> <?php echo $vo['title']; ?></a>
                        </li>
                    <?php endif; endforeach; endif; else: echo "" ;endif; ?>

                <li class="layui-nav-item" style="height: 30px; text-align: center"></li>
            </ul>
        </div>
    </div>

    <!--主体-->
    
<style>
    body{overflow-x:hidden; background:#f2f0f5; padding:15px 0px 10px 5px;}
    #main{ font-size:12px;}
    #main span.time{ font-size:14px; color:#528dc5; width:100%; padding-bottom:10px; float:left}
    #main div.top{ width:100%; background:url(/static/images/main_r2_c2.jpg) no-repeat 0 10px; padding:0 0 0 15px; line-height:35px; float:left}
    #main div.sec{ width:100%; background:url(/static/images/main_r2_c2.jpg) no-repeat 0 15px; padding:0 0 0 15px; line-height:35px; float:left}
    .left{ float:left}
    #main div a{ float:left}
    #main span.num{  font-size:30px; color:#538ec6; font-family:"Georgia","Tahoma","Arial";}
    .left{ float:left}
    div.main-tit{ font-size:14px; font-weight:bold; color:#4e4e4e; background:url(/static/images/main_r4_c2.jpg) no-repeat 0 33px; width:100%; padding:30px 0 0 20px; float:left}
    div.main-con{ width:100%; float:left; padding:10px 0 0 20px; line-height:36px;}
    div.main-corpy{ font-size:14px; font-weight:bold; color:#4e4e4e; background:url(/static/images/main_r6_c2.jpg) no-repeat 0 33px; width:100%; padding:30px 0 0 20px; float:left}
    div.main-order{ line-height:30px; padding:10px 0 0 0;}
</style>
<div class="layui-body">
    <!--tab标签-->
    <div class="layui-tab layui-tab-brief">
        <ul class="layui-tab-title">
            <li class="layui-this">网站概览</li>
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <table class="layui-table" width="99%" border="0" cellspacing="0" cellpadding="0" id="main">
                    <tr>
                        <td colspan="2" align="left" valign="top">
                            <span class="time"><strong>上午好！<?php echo session('admin_name'); ?></strong>，所属组别：<u>[<?php echo session('group_name'); ?>]</u></span>
                            <div class="top"><span class="left">您上次的登灵时间：<?php echo $user['last_login_time']; ?>   登录IP：<?php echo $user['last_login_ip']; ?> &nbsp;&nbsp;&nbsp;&nbsp;如非您本人操作，请及时</span><a href="index.html" target="mainFrame" onFocus="this.blur()">更改密码</a></div>
                            <div class="sec">这是您第<span class="num"><?php echo $user['times']; ?></span>次,登录！</div>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top" width="50%">
                            <div class="main-tit">网站信息</div>
                            <div class="main-con">

                                管理员个数：<font color="#538ec6"><strong><?php echo $user['count']; ?></strong></font> 人<br/>
                                登陆者IP：<?php echo $user['last_login_ip']; ?><br/>
                                程序编码：UTF-8<br/>
                            </div>
                        </td>
                        <td align="left" valign="top" width="49%">
                            <div class="main-tit">服务器信息</div>
                            <div class="main-con">
                                网站域名：<?php echo $config['url']; ?><br/>
                                服务器操作系统：<?php echo $config['server_os']; ?><br/>
                                最大上传限制：<?php echo $config['max_upload_size']; ?><br/>
                                MySQL版本：<?php echo $config['mysql_version']; ?><br/>
                                服务器环境：<?php echo $config['server_soft']; ?><br/>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="left" valign="top">
                            <div class="main-corpy">系统提示</div>
                            <div class="main-order">1=>如您在使用过程有发现出错请及时与我们取得联系<br/>
                                2=>建议IE浏览器使用高版本</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>


    <!--底部-->
    <div class="layui-footer footer">
        <div class="layui-main">
            <p>2016-2017 &copy; 诉舍后台系统</p>
        </div>
    </div>
</div>
<script>
    // 定义全局JS变量
    var GV = {
        current_controller: "jdroom/<?php echo (isset($controller) && ($controller !== '')?$controller:''); ?>/",
        base_url: "/static"
    };
</script>
<!--JS引用-->
<script src="/static/js/jquery.min.js"></script>
<!--<script src="/static/js/layui/lay/dest/layui.all.js"></script>-->
<script src="/static/js/layui/layui.all.js"></script>
<script src="/static/js/admin.js"></script>

<!--页面JS脚本-->

    <script>
        console.log( window.WebSocket);
//        var ws = new WebSocket("ws://47.104.105.159"); //连接服务器
//        var ws = new WebSocket("ws://127.0.0.1:80/index/Socket/index");
//
//        ws.onopen = function (event) { alert("已经与服务器建立了连接\r\n当前连接状态：" + this.readyState); };
//        ws.onmessage = function (event) { alert("接收到服务器发送的数据：\r\n" + event.data); };
//        ws.onclose = function (event) { console.log("已经与服务器断开连接\r\n当前连接状态：" + this.readyState); };
//        ws.onerror = function (event) { console.log("WebSocket异常！"); };
    </script>

</body>
</html>