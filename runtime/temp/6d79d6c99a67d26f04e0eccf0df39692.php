<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:37:"../themes/admin/orderlist\detail.html";i:1524454850;s:42:"D:\wamp64\www\sushe\themes\admin\base.html";i:1523604429;}*/ ?>
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
    .layui-input{border: none;}
</style>
<div class="layui-body">
    <!--tab标签-->
    <div class="layui-tab layui-tab-brief">
        <ul class="layui-tab-title">
            <li class=""><a href="<?php echo url('jdroom/orderlist/index'); ?>">订单列表</a></li>
            <li class="layui-this">订单详情</li>
        </ul>
        <div class="layui-tab-content">
            <!--未支付状态下页面为只读-->
            <?php if($list['paystatus'] == 0): ?>
                <div style="width: 100%;background: transparent;z-index:9999;height: 1900px;position: absolute;"></div>
                <div style="margin-left:500px;top: 45%;;position: fixed;font-size:48px;color:#ccc;z-index: 999999" id="noOperation">用户支付后可操作</div>
            <?php elseif($list['auid'] == 0): ?>
                <div style="width: 100%;background: transparent;z-index:9999;height: 1900px;position: absolute;"></div>
                <div style="margin-left:500px;top: 45%;;position: fixed;font-size:48px;color:#ccc;z-index: 999999" >本人接单后可操作</div>
            <?php elseif($list['is_complete'] == 2): ?>
                <div style="width: 100%;background: transparent;z-index:9999;height: 800px;position: absolute;"></div>
                <div style="margin-left:500px;top: 45%;;position: fixed;font-size:48px;color:#ccc;z-index: 999999" >订单已被取消</div>
            <?php endif; ?>
            <input type="hidden" id="positionFlag" value="<?php echo $flag; ?>">
            <div class="layui-tab-item layui-show">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">订单号：</label>
                            <div class="layui-input-inline">
                                <input type="text"   placeholder="" value="<?php echo $order; ?>" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">订单状态：</label>
                            <div class="layui-input-inline">
                                <input type="text"   placeholder=""  value="<?php echo $list['orderstatus']; ?>" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">创建时间：</label>
                        <div class="layui-input-inline">
                            <input type="text"  value="<?php echo $list['createtime']; ?>" autocomplete="off" class="layui-input">
                        </div>
                        <label class="layui-form-label">更新时间：</label>
                        <div class="layui-input-inline">
                            <input type="text"  value="<?php echo $list['updatetime']; ?>" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">接单人：</label>
                        <div class="layui-input-inline">
                            <input type="text" value="<?php echo $list['username']; ?>" autocomplete="off" class="layui-input">
                        </div>
                        <div class="layui-input-inline">
                            <?php if($list['iscancle'] == 1): ?>
                                <button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right;" onclick="cancleOrder(this,'<?php echo $order; ?>',0)">取消订单</button>
                            <?php elseif($list['iscancle'] == 0): ?>
                                <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;">取消订单</button>
                            <?php elseif($list['iscancle'] == 2): ?>
                                <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;">已取消</button>
                            <?php endif; ?>

                        </div>
                    </div>
                    <!--需求描述-->
                    <div class="layui-collapse" lay-accordion="" style="border: none;" id="flag0">
                        <div class="layui-colla-item">
                            <h2 class="layui-colla-title">
                                <p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 30px;"></p>
                                <label class="layui-form-label layui-inline" style="cursor:pointer;line-height:25px;margin-top: -2px;background:#f1f1f1; text-align:center; margin-left: 20px;"><strong>需求描述</strong></label>
                            </h2>
                            <?php if($list['layerIssue'] == 0): ?>
                                <div class="layui-colla-content layui-show">
                            <?php else: ?>
                                <div class="layui-colla-content">
                            <?php endif; ?>
                                <div class="layui-form-item">
                                    <label class="layui-form-label layui-inline" style="width:80%;background:rgba(255, 255, 204, 1); text-align:center; margin-left: 20px;">
                                        用户的需求简述资料会在订单完成后的24小时内删除，“诉舍”平台承诺保护用户和律师的隐私权益
                                    </label>
                                </div>
                                <div class="layui-form-item layui-form-text">
                                    <div class="layui-input-block" style="width:72%;">
                                        <?php echo $list['content']; ?>
                                    </div>
                                </div>
                                <div class="layui-upload" style="margin-left: 90px;">
                                    <button type="button" name="image" class="layui-btn" id="touxiangimg" style="background-color: transparent;;"></button>
                                    <div class="layui-upload-list">
                                        <?php foreach($files['url'] as $k=>$v): if(!(empty($v) || (($v instanceof \think\Collection || $v instanceof \think\Paginator ) && $v->isEmpty()))): ?>
                                                <img src="<?php echo $v; ?>" class="layui-upload-img" style="cursor:pointer;width: 115px;height: 60px;float: left;margin-top: -55px; margin-left: 20px;" onclick="ShowBigimg(this,1)">
                                            <?php endif; endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--补充需求-->
                    <?php if(!(empty($is_replenish) || (($is_replenish instanceof \think\Collection || $is_replenish instanceof \think\Paginator ) && $is_replenish->isEmpty()))): ?>
                        <div class="layui-collapse" lay-accordion="" style="border: none;">
                        <div class="layui-colla-item">
                            <h2 class="layui-colla-title">
                                <p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 30px;"></p>
                                <label class="layui-form-label layui-inline" style="cursor:pointer;line-height:25px;margin-top: -2px;background:#f1f1f1; text-align:center; margin-left: 20px;"><strong>补充需求</strong></label>
                            </h2>
                                <?php if($list['layerIssue'] == 0): ?>
                                    <div class="layui-colla-content layui-show">
                                 <?php else: ?>
                                    <div class="layui-colla-content">
                                <?php endif; ?>
                                <div class="layui-form-item layui-form-text">
                                    补充需求：
                                    <div class="layui-input-block" style="width:72%;">
                                        <?php echo $list['replenish_content']; ?>
                                    </div>
                                </div>
                                <div class="layui-upload" style="margin-left: 90px;">
                                    <button type="button" name="image" class="layui-btn" id="" style="background-color: transparent;"></button>
                                    <div class="layui-upload-list">
                                        <?php if(!(empty($replenishfiles['url']) || (($replenishfiles['url'] instanceof \think\Collection || $replenishfiles['url'] instanceof \think\Paginator ) && $replenishfiles['url']->isEmpty()))): foreach($replenishfiles['url'] as $k=>$v): if(!(empty($v) || (($v instanceof \think\Collection || $v instanceof \think\Paginator ) && $v->isEmpty()))): ?>
                                                    <img src="<?php echo $v; ?>" class="layui-upload-img"  style="cursor:pointer;width: 115px;height: 60px;float: left;margin-top: -55px; margin-left: 20px;" onclick="ShowBigimg(this,1)">
                                                <?php endif; endforeach; endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                <!--收函对象-->
                <div class="layui-collapse" lay-accordion="" style="border: none;" id="flag1">
                    <div class="layui-colla-item">
                        <h2 class="layui-colla-title">
                            <p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 30px;"></p>
                            <label class="layui-form-label layui-inline" style="cursor:pointer;line-height:25px;margin-top: -2px;background:#f1f1f1; text-align:center; margin-left: 20px;"><strong>收函对象</strong></label>
                        </h2>
                        <?php if($list['layerIssue'] == 0): ?>
                            <div class="layui-colla-content layui-show">
                         <?php else: ?>
                            <div class="layui-colla-content ">
                         <?php endif; ?>
                            <div class="layui-form-item">
                                <label class="layui-form-label">收函对象：</label>
                                <div class="layui-input-inline">
                                    <input type="text"  value="<?php echo $address['accept_name']; ?>" autocomplete="off" class="layui-input">
                                </div>
                                <label class="layui-form-label">收函地址：</label>
                                <div class="layui-input-inline">
                                    <input type="text" value="<?php echo $address['accept_address']; ?>" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">收函电话：</label>
                                <div class="layui-input-inline">
                                    <input type="text"   value="<?php echo $address['accept_phone']; ?>" autocomplete="off" class="layui-input">
                                </div>
                                <label class="layui-form-label">电子邮件：</label>
                                <div class="layui-input-inline">
                                    <input type="text"  value="<?php echo $address['accept_email']; ?>" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!--发函对象-->
                <div class="layui-collapse" lay-accordion="" style="border: none;">
                    <div class="layui-colla-item">
                        <h2 class="layui-colla-title">
                            <p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 60px;"></p>
                            <label class="layui-form-label layui-inline" style="cursor:pointer;line-height:25px;background:#f1f1f1; text-align:center; margin-left: 20px;"><strong>发函对象</strong></label>
                        </h2>
                        <?php if($list['layerIssue'] == 0): ?>
                            <div class="layui-colla-content  layui-show">
                         <?php else: ?>
                            <div class="layui-colla-content">
                         <?php endif; ?>
                            <div class="layui-form-item">
                                <label class="layui-form-label">发函对象：</label>
                                <div class="layui-input-inline" >
                                    <input type="text"  value="<?php echo $address['send_name']; ?>" autocomplete="off" class="layui-input" >
                                </div>
                                <label class="layui-form-label">发函地址：</label>
                                <div class="layui-input-inline" style="    width: 50%;">
                                    <input type="text" value="<?php echo $address['send_address']; ?>" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">发函电话：</label>
                                <div class="layui-input-inline">
                                    <input type="text"  value="<?php echo $address['send_phone']; ?>" autocomplete="off" class="layui-input">
                                </div>
                                <label class="layui-form-label">电子邮件：</label>
                                <div class="layui-input-inline">
                                    <input type="text"  value="<?php echo $address['send_email']; ?>" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                            <?php if($list['send_type'] == 1): ?>
                                <div class="layui-form-item">
                                    <label class="layui-form-label" style="width: 112px;">公司信誉代码：</label>
                                    <div class="layui-input-inline">
                                        <input type="text"  value="<?php echo $address['companyCode']; ?>" autocomplete="off" class="layui-input">
                                    </div>
                                </div>
                            企业信息展示：
                                <div class="layui-upload" style="margin-left: 90px;">
                                <button type="button" name="image" class="layui-btn" style="background-color: transparent;;"></button>
                                <div class="layui-upload-list">
                                    <?php foreach($company['url'] as $k=>$vc): if(!(empty($vc) || (($vc instanceof \think\Collection || $vc instanceof \think\Paginator ) && $vc->isEmpty()))): ?>
                                            <img src="<?php echo $vc; ?>" class="layui-upload-img" style="cursor:pointer;width: 115px;height: 60px;float: left;margin-top: -55px; margin-left: 20px;" onclick="ShowBigimg(this,1)">
                                        <?php endif; endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="layui-form-item layui-form-text" style="margin-top: 40px;width: 80%;">
                                <div class="layui-input-block">
                                    <textarea placeholder="填写对资料的意见" class="layui-textarea" id="fileIssue" style="max-height: 150px;"></textarea>
                                </div>
                            </div>
                            <div class="layui-form-item" style="margin-left: 60px;">
                                <div class="layui-input-inline">
                                    <?php if($list['layerIssue'] == 3): ?>
                                        <button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right;background: rgba(102, 204, 0, 1);color: white;"  onclick="fileVerify(this,'<?php echo $order; ?>',0,'<?php echo $list['uid']; ?>');">资料有误</button>
                                    <?php else: ?>
                                        <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;background: rgba(102, 204, 0, 1);color: white;" >资料有误</button>
                                    <?php endif; ?>
                                </div>
                                <div class="layui-input-inline">
                                    <?php if($list['layerIssue'] == 3): ?>
                                        <button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right;border-color: rgba(102, 204, 0, 1); " onclick="fileVerify(this,'<?php echo $order; ?>',1,'<?php echo $list['uid']; ?>');">资料确认</button>
                                    <?php else: ?>
                                        <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;border-color: rgba(102, 204, 0, 1); " >资料确认</button>
                                    <?php endif; ?>
                                </div>
                                <?php if(!(empty($is_replenish) || (($is_replenish instanceof \think\Collection || $is_replenish instanceof \think\Paginator ) && $is_replenish->isEmpty()))): ?>
                                    <button class="layui-btn layui-btn-radius" style="float: right;border-color: rgba(102, 204, 0, 1); " >查看互动</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!--提交律师函-->
                <div class="layui-collapse" lay-accordion="" style="border: none;" id="flag2">
                    <div class="layui-colla-item">
                    <h2 class="layui-colla-title">
                        <p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 60px;"></p>
                        <label class="layui-form-label layui-inline" style="cursor:pointer;line-height:25px;margin-top: -2px;background:#f1f1f1; text-align:center; margin-left: 20px;"><strong>提交律师函</strong></label>
                    </h2>

                    <?php if(($list['layerIssue'] == 1)): ?>
                        <div class="layui-colla-content layui-show">
                    <?php else: ?>
                        <div class="layui-colla-content">
                    <?php endif; ?>
                        <div class="layui-upload" style="margin-left: 0px;">
                            <button type="button" name="image" class="layui-btn" style="background-color: transparent;;"></button>
                            <div class="layui-upload-list" id="fileShow0">
                                <?php if(!(empty($layerFile) || (($layerFile instanceof \think\Collection || $layerFile instanceof \think\Paginator ) && $layerFile->isEmpty()))): foreach($layerFile[0]['pdf'] as $k=>$v): ?>
                                        <a href="<?php echo $v; ?>" target="_blank" title="<?php echo $layerFile[0]['filename']; ?>"><img src="/static/images/pdf.png" class="layui-upload-img" style="cursor:pointer;height: 60px;float: left;margin-top: -55px; margin-left: 20px;" ></a>
                                    <?php endforeach; endif; ?>
                            </div>
                        </div>
                        <?php if($changeContent['count'] >= 1): ?>
                            <div class="layui-form-item">
                                <p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 60px;"></p>
                                <label class="layui-form-label layui-inline" style="line-height:25px;margin-top: -25px;background:#f1f1f1; text-align:center; margin-left: 20px;width: 145px;"><strong>第1次律师函修改要求</strong></label>
                            </div>
                            <div class="layui-form-item layui-form-text">
                                <div class="layui-input-block" style="width:72%;margin-left: 10px;">
                                    <?php if($changeContent['count'] == 1): ?>
                                        <?php echo $changeContent[0]['content']; endif; ?>
                                </div>
                            </div>
                            <div class="layui-input-block" style="margin-left: 10px;" id="fileShow1">
                                <?php if(!(empty($layerFile) || (($layerFile instanceof \think\Collection || $layerFile instanceof \think\Paginator ) && $layerFile->isEmpty()))): if($layerFile['count'] >= '2'): foreach($layerFile[1]['pdf'] as $k=>$v): ?>
                                            <img src="/static/images/pdf.png" class="layui-upload-img"  title="<?php echo $layerFile[1]['filename']; ?>" style="cursor:pointer;width: 115px;height: 60px;margin-bottom: 30px;" >
                                        <?php endforeach; endif; endif; ?>
                             </div>
                        <?php endif; if($changeContent['count'] == 2): ?>
                            <div class="layui-form-item">
                                <p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 60px;"></p>
                                <label class="layui-form-label layui-inline" style="line-height:25px;margin-top: -25px;background:#f1f1f1; text-align:center; margin-left: 20px;width: 145px;"><strong>第2次律师函修改要求</strong></label>
                            </div>
                            <div class="layui-form-item layui-form-text">
                                <div class="layui-input-block" style="width:72%;margin-left: 10px;">
                                    <?php if($changeContent['count'] == 2): if($changeContent['count'] > '1'): ?>
                                            <?php echo $changeContent[1]['content']; endif; endif; ?>
                                </div>
                            </div>
                            <div class="layui-input-block" style="margin-left: 10px;" id="fileShow2">
                                <?php if(!(empty($layerFile) || (($layerFile instanceof \think\Collection || $layerFile instanceof \think\Paginator ) && $layerFile->isEmpty()))): if($layerFile['count'] >= '3'): foreach($layerFile[2]['pdf'] as $k=>$v): ?>
                                            <img src="/static/images/pdf.png"  title="<?php echo $layerFile[2]['filename']; ?>" class="layui-upload-img" style="cursor:pointer;width: 115px;height: 60px;margin-bottom: 30px;">
                                        <?php endforeach; endif; endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="layui-upload" style="    margin-top: 25px;">
                            <?php if($list['uploadBtn'] == 1): ?>
                                <button type="button" class="layui-btn layui-btn-normal" id="testList">选择文件</button>
                            <?php elseif($list['uploadBtn'] == 0): ?>
                                <button type="button" class="layui-btn layui-btn-normal layui-btn-disabled">选择文件</button>
                            <?php endif; ?>
                            <div class="layui-upload-list">
                                <table class="layui-table">
                                    <thead>
                                    <tr><th>文件名</th>
                                        <th>大小</th>
                                        <th>状态</th>
                                        <th>操作</th>
                                    </tr></thead>
                                    <tbody id="demoList"></tbody>
                                </table>
                            </div>
                            <?php if($list['uploadBtn'] == 1): ?>
                                <button type="button" attr_uid ='<?php echo $list['uid']; ?>' class="layui-btn" id="testListAction">开始上传</button>
                            <?php elseif($list['uploadBtn'] == 0): ?>
                                <button type="button" class="layui-btn layui-btn-disabled">开始上传</button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
                </div>

                    <!--用户授权资料-->
                <div class="layui-collapse" lay-accordion="" style="border: none;" id="flag3">
                    <div class="layui-colla-item">
                        <h2 class="layui-colla-title">
                            <p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 60px;"></p>
                            <label class="layui-form-label layui-inline" style="cursor:pointer;line-height:25px;margin-top: -2px;background:#f1f1f1; text-align:center; margin-left: 20px;    width: 85px;"><strong>用户授权资料</strong></label>
                        </h2>
                        <?php if(!(empty($hash) || (($hash instanceof \think\Collection || $hash instanceof \think\Paginator ) && $hash->isEmpty()))): ?>
                            <div class="layui-colla-content layui_show">
                        <?php else: ?>
                            <div class="layui-colla-content">
                        <?php endif; if(!(empty($list['shouquanUrl']) || (($list['shouquanUrl'] instanceof \think\Collection || $list['shouquanUrl'] instanceof \think\Paginator ) && $list['shouquanUrl']->isEmpty()))): ?>
                            <div class="layui-upload" style="margin-left: 90px;">
                                <button type="button" name="image" class="layui-btn" style="background-color: transparent;;"></button>
                                <div class="layui-upload-list">
                                    <?php if(!(empty($list['shouquanUrl']) || (($list['shouquanUrl'] instanceof \think\Collection || $list['shouquanUrl'] instanceof \think\Paginator ) && $list['shouquanUrl']->isEmpty()))): ?>
                                        <a href="/jdroom/orderlist/downLoadPdf?url=<?php echo $list['shouquanUrl']; ?>" title="点击下载授权书"><img src="/static/images/pdf.png" class="layui-upload-img" style="cursor:pointer;width: 115px;height: 60px;float: left;margin-top: -55px; margin-left: 20px;"></a>
                                    <?php endif; ?>
                                    <input  name="img" type="hidden" value="">
                                </div>
                            </div>
                                <?php endif; ?>
                            <div class="layui-form-item">
                                <label class="layui-form-label" style="width: 122px;    margin-top: 40px;">授权书签署标识：</label>
                                <div class="layui-input-inline" style="    margin-top: 40px;">
                                    <input type="text"  placeholder="" value="<?php echo $list['signServiceId']; ?>" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <!--填写快递单号-->
                <div class="layui-collapse" lay-accordion="" style="border: none;" id="flag4">
                    <div class="layui-colla-item">
                        <h2 class="layui-colla-title">
                            <p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 60px;"></p>
                            <label class="layui-form-label layui-inline" style="cursor:pointer;line-height:25px;margin-top: -2px;background:#f1f1f1; text-align:center; margin-left: 20px;    width: 85px;"><strong>填写快递单号</strong></label>
                        </h2>
                        <?php if(!(empty($list['express_number']) || (($list['express_number'] instanceof \think\Collection || $list['express_number'] instanceof \think\Paginator ) && $list['express_number']->isEmpty()))): ?>
                            <div class="layui-colla-content">
                        <?php else: ?>
                            <div class="layui-colla-content layui_show">
                         <?php endif; ?>
                            <div class="layui-form-item">
                                <label class="layui-form-label"></label>
                                <div class="layui-input-inline">
                                    <input type="text" id="order_number_express"  placeholder="请填写快递单号" value="<?php echo $list['express_number']; ?>" autocomplete="off" class="layui-input express_number_input" style="border: 1px solid #ccc;">
                                </div>
                                <div class="layui-input-inline">
                                    <?php if(!(empty($list['signServiceId']) || (($list['signServiceId'] instanceof \think\Collection || $list['signServiceId'] instanceof \think\Paginator ) && $list['signServiceId']->isEmpty()))): ?>
                                        <button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right" onclick="confirmOrderNumber(this,'<?php echo $order; ?>',1,'<?php echo $list['uid']; ?>')">确认快递单号</button>
                                    <?php else: ?>
                                        <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right" >确认快递单号</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <!--结束-->
                    <div class="layui-form-item"><p style="height:1px;background:#dcdcdc;width:100%;clear: both;margin-top: 60px;"></p></div>
                    <div class="layui-form-item">
                        <div class="layui-input-inline">
                            <?php if($list['iscancle'] == 1): ?>
                                <button class="layui-btn layui-btn-primary layui-btn-radius" style="float: right;" onclick="cancleOrder(this,'<?php echo $order; ?>',0)">取消订单</button>
                            <?php elseif($list['iscancle'] == 0): ?>
                                <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;">取消订单</button>
                            <?php elseif($list['iscancle'] == 2): ?>
                                <button class="layui-btn layui-btn-disabled layui-btn-radius" style="float: right;">已取消</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <!--</form>-->
            </div>
        </div>
    </div>
</div>
                    <div style="width: 100%;background: #f1f1f1;z-index:9999"></div>


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

<script>
    layui.use(['element', 'layer'], function(){
        var element = layui.element;
        var layer = layui.layer;

        //监听折叠
        element.on('collapse(test)', function(data){
            layer.msg('展开状态：'+ data.show);
        });
    });
    $('.layui-input').attr('readonly','readonly');
    $('.express_number_input').attr('readonly',false);

    //图片放大
    function ShowBigimg(obj,sta){
        var url = $(obj).attr("src");
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            shadeClose: true,
//            area: ['800px', '700px'], //宽高
            area: '800px', //宽高
            content: "<img  src=" + url + " />"
        });
    }
    //定位到某位置

    var flag = $('#positionFlag').val();
//    alert(flag);
    window.location.hash='#flag'+flag;
    if('<?php echo $list['paystatus']; ?>'==0 || '<?php echo $list['is_complete']; ?>'==1|| '<?php echo $list['is_complete']; ?>'==2){
        $('.layui-colla-content').addClass(' layui-show');
    }
//    $('.scroll_top').click(function(){$('html,body').animate({scrollTop: '0px'}, 800);});
//  $('html,body').animate({scrollTop:$('#flag'+flag).offset().top}, 800);};
//    $('.scroll_bottom').click(function(){$('html,body').animate({scrollTop:$('.bottom').offset().top}, 800);});
</script>

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