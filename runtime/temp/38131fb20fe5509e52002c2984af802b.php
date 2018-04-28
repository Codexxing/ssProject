<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:36:"../themes/admin/orderlist\index.html";i:1524445238;s:42:"D:\wamp64\www\sushe\themes\admin\base.html";i:1523604429;}*/ ?>
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
    .orderlistFont{font-family: 'ArialMT', 'Arial';font-size: 14px;color: #686868;}
    .tixingfont{color: rgb(51,204,255)}
    .layui-btn+.layui-btn{margin-left: 6px;}
    </style>
<div class="layui-body">
    <!--tab标签-->
    <div class="layui-tab layui-tab-brief">
        <ul class="layui-tab-title">
            <li class="layui-this">订单列表</li>
            <!--<li class=""><a href="<?php echo url('admin/user/add'); ?>">添加用户</a></li>-->
        </ul>
        <div class="layui-tab-content">
            <div class="layui-inline" style="margin-bottom: 8px;">
                <a href="<?php echo url('jdroom/orderlist/index',array('paystatus'=>'1')); ?>" class="layui-btn layui-btn-sm <?php if($paystatus  ==  1): ?> layui-btn-normal  <?php else: ?> layui-btn-primary <?php endif; ?>">已支付<span class="layui-badge "><?php echo $num['payComplete']; ?></span></a>
                <a href="<?php echo url('jdroom/orderlist/index',array('paystatus'=>'2')); ?>" class="layui-btn layui-btn-sm  <?php if($paystatus  ==  2): ?> layui-btn-normal  <?php else: ?> layui-btn-primary <?php endif; ?>">未完成<span class="layui-badge layui-bg-gray"><?php echo $num['nocomplete']; ?></span></a>
                <a href="<?php echo url('jdroom/orderlist/index',array('paystatus'=>'3')); ?>" class="layui-btn layui-btn-sm <?php if($paystatus  ==  3): ?> layui-btn-normal  <?php else: ?> layui-btn-primary <?php endif; ?>">未支付<span class="layui-badge layui-bg-gray"><?php echo $num['payNoComplete']; ?></span></a>
                <a href="<?php echo url('jdroom/orderlist/index',array('paystatus'=>'4')); ?>" class="layui-btn layui-btn-sm <?php if($paystatus  ==  4): ?> layui-btn-normal  <?php else: ?> layui-btn-primary <?php endif; ?>">已取消<span class="layui-badge layui-bg-gray"><?php echo $num['payCancle']; ?></span></a>
                <a href="<?php echo url('jdroom/orderlist/index',array('paystatus'=>'5')); ?>" class="layui-btn layui-btn-sm <?php if($paystatus  ==  5): ?> layui-btn-normal  <?php else: ?> layui-btn-primary <?php endif; ?>">已完成<span class="layui-badge layui-bg-gray"><?php echo $num['complete']; ?></span></a>
                <a href="<?php echo url('jdroom/orderlist/index',array('paystatus'=>'0')); ?>" class="layui-btn layui-btn-sm <?php if($paystatus  ==  0): ?> layui-btn-normal  <?php else: ?> layui-btn-primary <?php endif; ?>">全部订单<span class="layui-badge layui-bg-gray"><?php echo $num['all']; ?></span></a>
                <a href="<?php echo url('jdroom/orderlist/index',array('paystatus'=>'6')); ?>" class="layui-btn layui-btn-sm <?php if($paystatus  ==  6): ?> layui-btn-normal  <?php else: ?> layui-btn-primary <?php endif; ?>">我的接单<span class="layui-badge layui-bg-gray"><?php echo $num['mynum']; ?></span></a>

            </div>
            <div class="layui-tab-item layui-show">

                <form class="layui-form layui-form-pane layui-inline" action="<?php echo url('jdroom/orderlist/index'); ?>" method="get">
                    <div class="layui-inline">
                        <label class="layui-form-label">关键词</label>
                        <div class="layui-input-inline">
                            <input type="text" name="keyword" value="<?php echo $keyword; ?>" required placeholder="请输入订单号或用户姓名" class="layui-input">
                            <input type="hidden" name="paystatus" value="<?php echo $paystatus; ?>" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn  layui-btn-primary">搜索</button>
                    </div>
                </form>
                <?php if(1 == 2): ?>
                <ul class="layui-inline" style="margin-left: 50px;">
                    <a href="/jdroom/orderlist/index?time=1&paystatus=<?php echo $paystatus; ?>">
                        <li class="layui-inline">
                            <button class="layui-btn  layui-btn-sm layui-btn-normal ">今天</button>
                        </li>
                    </a>
                    <a href="/jdroom/orderlist/index?time=-1&paystatus=<?php echo $paystatus; ?>">
                        <li class="layui-inline">
                            <button class="layui-btn layui-btn-primary layui-btn-sm">昨天</button>
                        </li>
                    </a>
                    <a href="/jdroom/orderlist/index?time=7&paystatus=<?php echo $paystatus; ?>">
                        <li class="layui-inline">
                            <button class="layui-btn layui-btn-primary layui-btn-sm">7天</button>
                        </li>
                    </a>
                    <a href="/jdroom/orderlist/index?time=30&paystatus=<?php echo $paystatus; ?>">
                        <li class="layui-inline">
                            <button class="layui-btn layui-btn-primary layui-btn-sm">30天</button>
                        </li>
                    </a>
                </ul>
                <form class="layui-form layui-input-inline" action="/jdroom/orderlist/index" style="    margin-top: 10px;">
                    <div class="layui-form layui-inline" >
                        <div class="layui-form-item">
                                <label class="layui-form-label">时间</label>
                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input" value="" name="startTime" lay-id="orderlist_start" placeholder="开始时间">
                                </div>
                                <div class="layui-input-inline" style="width:12px;margin-top: 10px">到</div>

                                <div class="layui-input-inline">
                                    <input type="text" class="layui-input" value="" name="endTime" id="orderlist_end" placeholder="结束时间">
                                </div>
                            <div class="layui-input-inline" style="margin-top: 5px;">
                                    <button class="layui-btn layui-btn-sm layui-btn-primary" lay-submit lay-filter="formDemo">提交</button>
                                </div>
                        </div>
                    </div>
                </form>
                <?php endif; ?>
                <hr>

                        <table class="layui-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">ID</th>
                            <th>订单号</th>
                            <th>收函对象</th>
                            <th>发函对象</th>
                            <th>需求描述</th>
                            <th>接单人</th>
                            <!--<th>查看详情</th>-->
                            <th>订单状态</th>
                            <th>时间</th>
                            <th>上传下载(发票/律师函)</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($list[0]['data'] as $k=>$vo): ?>
                    <tr>
                        <td><?php echo $vo['id']; ?></td>
                        <td><a class="tixingfont" title="查看详情" target="_blank" href="/jdroom/Orderlist/detail?order=<?php echo $vo['order_number']; ?>"><?php echo $vo['order_number']; ?></a></td>
                        <td><a title="<?php echo $vo['accept_name']; ?>" style="cursor: pointer;"><?php echo $vo['accept_name_s']; ?></a></td>
                        <td><a title="<?php echo $vo['send_name']; ?>" style="cursor: pointer;"><?php echo $vo['send_name_s']; ?></a></td>
                        <td><a  class="tixingfont" style="cursor: pointer;" onclick="lookContent('<?php echo $vo['order_number']; ?>')">需求描述</a></td>
                        <td><?php echo $vo['username']; ?></td>
                        <!--<td><a class="tixingfont" target="_blank" href="/jdroom/Orderlist/detail?order=<?php echo $vo['order_number']; ?>">查看详情</a></td>-->
                        <td><span style="color: red;"><?php echo $vo['orderstatus']; ?></span></td>

                        <td><?php echo $vo['createtime']; ?><br><?php echo $vo['updatetime']; ?></td>
                        <td>
                            <?php if(!(empty($vo['signServiceId']) || (($vo['signServiceId'] instanceof \think\Collection || $vo['signServiceId'] instanceof \think\Paginator ) && $vo['signServiceId']->isEmpty()))): ?>
                                <span id="uploadDown" attr-order="<?php echo $vo['order_number']; ?>" style="cursor: pointer;">上传/下载</span>
                            <?php else: ?>
                                <span attr-order="<?php echo $vo['order_number']; ?>" style="cursor: pointer;">用户签署后上传</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($vo['is_complete'] == 0): if($vo['iscancle'] == 1): ?>
                                    <!--<button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right;;margin-right: 22%;margin-left: 10px;" onclick="cancleOrder(this,'<?php echo $vo['order_number']; ?>',0)">取消订单</button>-->
                                    <button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right;margin-left: 10px;" onclick="cancleOrder(this,'<?php echo $vo['order_number']; ?>',0)" title="取消订单"><i class="layui-icon">&#xe640;</i></button>
                                <?php elseif($vo['iscancle'] == 0): ?>
                                    <!--<button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;;margin-right: 22%;margin-left: 10px;">取消订单</button>-->
                                    <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;margin-left: 10px;" title="取消订单"><i class="layui-icon">&#xe640;</i></button>
                                <?php endif; if(!(empty($vo['signServiceId']) || (($vo['signServiceId'] instanceof \think\Collection || $vo['signServiceId'] instanceof \think\Paginator ) && $vo['signServiceId']->isEmpty()))): ?>
                                    <!--<button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right;" onclick="expressOrder(this,'<?php echo $vo['order_number']; ?>',1)">填写订单号</button>-->
                                    <button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right;" onclick="expressOrder(this,'<?php echo $vo['order_number']; ?>',1)" title="填写订单号"><i class="layui-icon">&#xe6b2;</i></button>

                                <?php else: ?>
                                    <!--<button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;">填写订单号</button>-->
                                    <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;" title="填写订单号"><i class="layui-icon">&#xe6b2;</i></button>

                                <?php endif; switch($vo['isreceive']): case "0": ?>
                                        <!--<button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right" type="add" onclick="orderReceive(this,'<?php echo $vo['order_number']; ?>')">接单</button>-->
                                        <button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right" type="add" onclick="orderReceive(this,'<?php echo $vo['order_number']; ?>')" title="接单"><i class="layui-icon">&#x1005;</i></button>
                                    <?php break; case "1": ?>
                                        <!--<button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right" >已接单</button>-->
                                        <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right"  title="已接单"><i class="layui-icon">&#x1005;</i></button>
                                    <?php break; case "2": ?>
                                        <!--<button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right" type="cancle" onclick="orderReceive(this,'<?php echo $vo['order_number']; ?>')">取消接单</button>-->
                                        <button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right" type="cancle" onclick="orderReceive(this,'<?php echo $vo['order_number']; ?>')" title="取消接单"><i class="layui-icon">&#x1007;</i></button>
                                    <?php break; case "3": ?>
                                        <!--<button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right" >取消接单</button>-->
                                        <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right"  title="取消接单"><i class="layui-icon">&#x1007;</i></button>
                                    <?php break; case "4": ?>
                                        <!--<button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right" type="add">接单</button>-->
                                        <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right" type="add" title="接单"><i class="layui-icon">&#x1005;</i></button>
                                    <?php break; endswitch; endif; ?>

                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <!--分页-->
               <?php echo $list[1]->render();; ?>
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