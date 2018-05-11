
/**
 * 后台JS主入口
 */

var layer = layui.layer,
    element = layui.element,
    laydate = layui.laydate,
    form = layui.form;
//判断左侧菜单是否收缩
//if(window.sessionStorage.getItem("menu") == "true"){
//    $('.layui-body,.layui-footer').css('margin-left','-10%');
//    $('.layui-side').animate({width: 'hide'});
//}
//左侧菜单栏收缩展开
$('#slidedes').click(function() {
    var type=$(this).attr('attr-type');//1 默认值  下一步为收缩  2为下一步为展开
    $('.layui-side').animate({width: 'toggle'});
    var width='-10%';
    if (type==1 ) {
        width = '-10%';
        $(this).attr('attr-type', 2);
        window.sessionStorage.setItem("menu",true);
    }else if(type==2 ){
         width='0';
        $(this).attr('attr-type',1);
        window.sessionStorage.setItem("menu",false);
    }
    //选择出所有的span，并判断是不是hidden
    $('.layui-body,.layui-footer').css('margin-left',width);
})


/**
 * AJAX全局设置
 */
$.ajaxSetup({
    type: "post",
    dataType: "json"
});
//if(GV.current_controller == 'jdroom/Index/') {
//    $('.fa-shopping-cart').parents('.layui-nav-item').addClass('layui-nav-itemed');
//}
/**
 * 后台侧边菜单选中状态
 */
$('.layui-nav-item').find('a').removeClass('layui-this');
$('.layui-nav-tree').find('a[href*="' + GV.current_controller + '"]').parent().addClass('layui-this').parents('.layui-nav-item').addClass('layui-nav-itemed');

/**
 * 通用单图上传
 */
layui.use('upload', function() {
    var upload = layui.upload;
    //执行实例
    var uploadInst = upload.render({
        elem: '.layui-upload-image', //绑定元素
        url: "/index.php/api/upload/uploadWinImg",
        accept: 'image',
        ext: 'jpg|png|gif|bmp|xls',
        done: function(data){
            //上传完毕回调
            if (data.error === 0) {

                document.getElementById('thumb').value = data.url;
                document.getElementById('thumbShow').src = data.url;
            } else {
                layer.msg(data.message);
            }

        }
        ,error: function(){
            //请求异常回调
            layer.msg('请求服务器失败，请稍后再试');
        }
    });
    //后台资料上传
	var demoListView = $('#demoList'),
        uploadListIns = upload.render({
        elem: '#testList'
        ,url: '/index/Tool/fileUpload/'
        ,accept: 'file'
        ,multiple: true
        ,auto: false
        ,bindAction: '#testListAction'
        ,choose: function(obj){
                var trLen = $('#demoList').find('tr').length;
                if(trLen==1){return false;}

            var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
            //读取本地文件
            obj.preview(function(index, file, result){
                var tr = $(['<tr id="upload-'+ index +'">'
                    ,'<td>'+ file.name +'</td>'
                    ,'<td>'+ (file.size/1014).toFixed(1) +'kb</td>'
                    ,'<td>等待上传</td>'
                    ,'<td>'
                    ,'<button class="layui-btn layui-btn-mini demo-reload layui-hide">重传</button>'
                    ,'<button class="layui-btn layui-btn-mini layui-btn-danger demo-delete">删除</button>'
                    ,'</td>'
                    ,'</tr>'].join(''));

                //单个重传
                tr.find('.demo-reload').on('click', function(){
                    obj.upload(index, file);
                });

                //删除
                tr.find('.demo-delete').on('click', function(){
                    delete files[index]; //删除对应的文件
                    tr.remove();
                    uploadListIns.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                });

                demoListView.append(tr);
            });
        }
        ,done: function(res, index, upload){
			console.log(res);
            if(res.code == 1){ //上传成功
				$('#fileShow'+res.data.times).append('	<a href="'+res.data.url+'" target="_blank" title="'+res.data.filename+'"><img src="/static/images/pdf.png" class="layui-upload-img" style="cursor:pointer;height: 60px;float: left;margin-top: -55px; margin-left: 20px;" onclick="ShowBigimg(this,1)"></a>');
                var tr = demoListView.find('tr#upload-'+ index)
                    ,tds = tr.children();
                tds.eq(2).html('<span style="color: #5FB878;">上传成功</span>');
                tds.eq(3).html(''); //清空操作
			
                return delete this.files[index]; //删除文件队列已经上传成功的文件
            }else if(res.code ==0){
				layer.msg(res.message);return false;
			}
            this.error(index, upload);
        }
        ,error: function(index, upload){
			console.log(index,upload);
            var tr = demoListView.find('tr#upload-'+ index)
                ,tds = tr.children();
            tds.eq(2).html('<span style="color: #FF5722;">上传失败</span>');
            tds.eq(3).find('.demo-reload').removeClass('layui-hide'); //显示重传
        }
    });
    var Opok=$('#hiddenOrder').val();
    console.log(Opok);
    //发票上传
    upload.render({
        elem: '#fapiaoUpload',
        url: '/index.php/api/upload/filesUpload?type=f&order='+$('#hiddenOrder').val(),
        accept:'file',
        //auto: false,
        //ext:'pdf',
        done: function(res){
            console.log(res);
            console.log(res.data.url);
            layer.msg(res.message);
            if(res.error == 0){
                return false;
            }
            $('#faRe').remove();
            $('#fapiaoOpk').append('<a href="/jdroom/orderlist/downLoadPdf?url='+res.data.url+'">'+
                '<i class="layui-icon">&#xe618;</i>'+
            '<p>已存在电子发票，点击下载</p>'+
            '</a>');
        }
    });
    //律师函上传
    upload.render({
        elem: '#lvshihanUpload'
        ,url: '/index.php/api/upload/filesUpload?type=l&order='+$('#hiddenOrder').val(),
        accept:'file',
        done: function(res){
            console.log(res);
            console.log(res.data.url);
            layer.msg(res.message);
            if(res.error == 0){
                return false;
            }
            $('#lvshiRe').remove();
            $('#lvshiOpk').append('<a href="/jdroom/orderlist/downLoadPdf?url='+res.data.url+'">'+
            '<i class="layui-icon">&#xe618;</i>'+
            '<p>已存在律师函，点击下载</p>'+
            '</a>');
        }
    });
});

/**
 * 通用表单提交(AJAX方式)
 */
layui.use('form',function () {
    var form = layui.form;

    form.on('submit(*)', function (data) {
        var indexx = layer.load(2);
        console.log(data.form.action);
        $.ajax({
            url: data.form.action,
            type: data.form.method,
            //dataType: 'json',
            data: $(data.form).serialize(),
            success: function (info) {
                console.log(info);
                layer.close(indexx);
                if (info.code === 1) {
                    setTimeout(function () {
                        location.href = info.url;
                    }, 1000);
                }
                layer.msg(info.msg);
            }
        });

        return false;
    });

    //监听指定开关
    form.on('switch(switchTestTrue)', function(data){
        var id = $(this).attr('data_id');
        var status;
        this.checked == true ? status=1 :status =0;
        $.post('/jdroom/user/updateStatus',{'id':id,'status':status},function(rsd){
            console.log(rsd);
            if(rsd.code == 1){
                layer.msg(rsd.message);return false;
            }else{
                layer.msg('操作失败');return false;
            }
        },'json')
    });
    //监听指定开关
    form.on('switch(switchTestFalse)', function(data){
        var id = $(this).attr('data_id');
        var status;
        this.checked == true ? status=1 :status =0;
        //var co =update_user(id,status);
        $.post('/jdroom/user/updateStatus',{'id':id,'status':status},function(rsd){
            console.log(rsd);
            if(rsd.code == 1){
                layer.msg(rsd.message);return false;
            }else{
                layer.msg('操作失败');return false;
            }
        },'json')
    });

    //监听白名单开关
    form.on('switch(switchCompanyTrue)', function(data){
        var id = $(this).attr('data_id');
        var status;
        this.checked == true ? status=1 :status =0;
        $.post('/jdroom/whitecompany/updateStatus',{'id':id,'status':status},function(rsd){
            console.log(rsd);
            if(rsd.code == 1){
                layer.msg(rsd.message);return false;
            }else{
                layer.msg('操作失败');return false;
            }
        },'json')
    });
    //监听白名单开关
    form.on('switch(switchCompanyFalse)', function(data){
        var id = $(this).attr('data_id');
        var status;
        this.checked == true ? status=1 :status =0;
        //var co =update_user(id,status);
        $.post('/jdroom/whitecompany/updateStatus',{'id':id,'status':status},function(rsd){
            console.log(rsd);
            if(rsd.code == 1){
                layer.msg(rsd.message);return false;
            }else{
                layer.msg('操作失败');return false;
            }
        },'json')
    });
})


//$('.datetime').on('click', function () {
//    laydate.render({
//        elem: this,
//        format: 'YYYY-MM-DD hh:mm:ss'
//    })
//});

//订单列表的时间选择
laydate.render({
    elem: '#orderlist_start',
    type: 'datetime',
    trigger: 'click',
    change: function(value, date){ //监听日期被切换
        lay('#orderlist_start').html(value);
    }
})
laydate.render({
    elem: '#orderlist_end',
    type: 'datetime',
    trigger: 'click',
    change: function(value, date){ //监听日期被切换
        lay('#orderlist_start').html(value);
    }
})

//用户分析的时间选择  开始时间
laydate.render({
    elem: '#usertime_start',
    type: 'datetime',
    trigger: 'click',
    change: function(value, date){ //监听日期被切换
        lay('#usertime').html(value);
    }
})
//用户分析的时间选择  结束时间
laydate.render({
    elem: '#usertime_end',
    type: 'datetime',
    trigger: 'click',
    change: function(value, date){ //监听日期被切换
        lay('#usertime').html(value);
    },
    done:function(value,date,endDate){

    }
})


/**
 * 通用批量处理（审核、取消审核、删除）
 */
$('.ajax-action').on('click', function () {
    var _action = $(this).data('action');
    layer.open({
        shade: false,
        content: '确定执行此操作？',
        btn: ['确定', '取消'],
        yes: function (index) {
            $.ajax({
                url: _action,
                data: $('.ajax-form').serialize(),
                success: function (info) {
                    if (info.code === 1) {
                        setTimeout(function () {
                            location.href = info.url;
                        }, 1000);
                    }
                    layer.msg(info.msg);
                }
            });
            layer.close(index);
        }
    });

    return false;
});

/**
 * 通用全选
 */
$('.check-all').on('click', function () {
    $(this).parents('table').find('input[type="checkbox"]').prop('checked', $(this).prop('checked'));
});

/**
 * 通用删除
 */
$('.ajax-delete').on('click', function () {
    var _href = $(this).attr('href');
    layer.open({
        shade: false,
        content: '确定删除？',
        btn: ['确定', '取消'],
        yes: function (index) {
            $.ajax({
                url: _href,
                type: "get",
                success: function (info) {
                    if (info.code === 1) {
                        setTimeout(function () {
                            location.href = info.url;
                        }, 1000);
                    }
                    layer.msg(info.msg);
                }
            });
            layer.close(index);
        }
    });

    return false;
});

/**
 * 清除缓存
 */
$('#clear-cache').on('click', function () {
    var _url = $(this).data('url');
    if (_url !== 'undefined') {
        $.ajax({
            url: _url,
            success: function (data) {
                if (data.code === 1) {
                    setTimeout(function () {
                        location.href = location.pathname;
                    }, 1000);
                }
                layer.msg(data.msg);
            }
        });
    }

    return false;
});
layui.use('laydate', function() {
    var laydate = layui.laydate;
    laydate.render({
        elem: '#orderlist_start'
        ,type: 'time'
        ,position: 'static'
    });
})

/**
 *接单和取消接单
 * add为接单  cancle是取消订单
 * **/
function orderReceive(obj,order){
    var type = $(obj).attr('type');
    var mess;
    type == 'add' ? mess='确认要接单吗？' : mess='确认要取消接单吗？';
    layer.alert(mess, {
         //skin: 'layui-layer-molv' //样式类名  自定义样式
        title:'接单提示',
         closeBtn: 1    // 是否显示关闭按钮
         ,anim: 1 //动画类型
         ,btn: ['确定','取消'] //按钮
         ,icon: 3   // icon
         ,yes:function(){
            var load = layer.load(2);
            $.post('/jdroom/Tool/orderReceiving',{'order':order,type:type},function(res){
                if(res.code == 1){
                    layer.msg(res.msg);
                    $(obj).html('');
                    if(type == 'add'){
                        $(obj).attr('type','cancle');
                        $(obj).append('<i class="layui-icon">&#x1007;</i>');
                    } else if(type == 'cancle'){
                        $(obj).attr('type','add');
                        $(obj).append('<i class="layui-icon">&#x1005;</i>');
                    }
                    layer.close(load);
                }else{
                    layer.close(load);
                    layer.msg(res.msg);return false;
                }

            },'json')
          }
         ,btn2:function(){
                 //layer.msg('取消了')
             }});

}

/**
 * 取消订单
 * status  0是取消订单   1完成订单
 * **/
function cancleOrder(obj,order,status){
    var mess;
    status==1 ? mess='确定要完成订单吗？' : mess='确认要取消该订单吗？';
    layer.alert(mess, {
        //skin: 'layui-layer-molv' //样式类名  自定义样式
        title:'提示',
        closeBtn: 1    // 是否显示关闭按钮
        ,anim: 1 //动画类型
        ,btn: ['确定','取消'] //按钮
        ,icon: 3   // icon
        ,yes:function(){

            if(status == 0){
                layer.closeAll('dialog');
                layer.prompt({title:'请输入取消订单理由'},function(value, index, elem){
                    var data = {'order':order,status:status,reason:value};
                    orderRequest(obj,order,status,data);
                });
            }else{
                var data = {'order':order,status:status};
                orderRequest(obj,order,status,data);
            }

        }
        ,btn2:function(){
            //layer.msg('取消了')
        }});
}

// status  0是取消订单   1完成订单
function orderRequest(obj,order,status,data){
    var load = layer.load(2);
    //console.log(status);return false;
    $.post('/jdroom/Tool/cancleOrder',data,function(res){
        layer.close(load);
        layer.closeAll();
        if(res.code == 1){
            layer.msg(res.msg);
            status==1 ?   $(obj).text('已完成') :   $(obj).text('已取消');
            $(obj).attr('class','layui-btn layui-btn-disabled layui-btn-radius');
            $(obj).removeAttr('onclick');
            $(obj).siblings('button').attr('class','layui-btn layui-btn-disabled layui-btn-radius').removeAttr('onclick');
        }else{
            layer.msg(res.msg);return false;
        }
    },'json')
}

/**
 * 填写快递订单号
 * status  0是取消订单   1完成订单
 * **/
function expressOrder(obj,order){
    layer.prompt({title:'请输入快递单号'},function(value, index, elem){
        var load = layer.load(2);
        $.post('/jdroom/Tool/expressOrder',{order:order,value:value},function(res){
            layer.close(load);
            layer.msg(res.msg);
            if(res.code ==1){
                $(obj).prev().bind("click",{obj:this,order:order,status:1},cancleOrder);
                $(obj).prev().attr("class",'layui-btn layui-btn-primary layui-btn-radius');
            }else{
                return false;
            }
        },'json');
        layer.close(index);
    });
}

/**
 * 需求描述点击确定
 * status  1无需补充  0需求需要补充
 * **/
function contentSend(obj,order,status,auid){
    switch (status){
        case 0://需要补充
            var content = $('#content_add').val();
            if(content == ''){
                layer.msg('请填写需求描述');return false;
            }
            var data={content:content,status:status,order:order,auid:auid};
            break;
        case 1://无需补充
            var data={status:status,order:order,auid:auid};
            break;
    }
    var load = layer.load(2);
    $.post('/jdroom/Tool/requirement',data,function(res){
        layer.msg(res.msg);
        layer.close(load);
        if(res.code==1){
            setTimeout(function(){window.location.reload();},2000);
        }else{
            return false;
        }
    },'json');
}
/**
 * 律师对资料的确认
 * status  0资料有误  1资料确认
 * **/
function fileVerify(obj,order,status,uid){
    switch (status){
        case 0://资料有误
            var content = $('#fileIssue').val();
            if(content == ''){
                layer.msg('请填写对资料的描述');return false;
            }
            var data={content:content,status:status,order:order,uid:uid};
            break;
        case 1://资料确认
            var data={status:status,order:order,uid:uid};
            break;
    }
    var load = layer.load(2);
    $.post('/jdroom/Tool/fileVerify',data,function(res){
        console.log(res);
        layer.msg(res.msg);
        layer.close(load);
        if(res.code==1){
            setTimeout(function(){window.location.reload();},2000);
        }else{
            return false;
        }
    },'json');
}
//确认快递单号
function confirmOrderNumber(obj,order,uid){
    var number = $('#order_number_express').val();
    if(number == ''){
        layer.msg('请填写快递单号');
        return false;
    }
    var load = layer.load(2);
    $.post('/jdroom/Tool/expressOrder',{order:order,value:number,uid:uid},function(res){
        layer.close(load);
        layer.msg(res.msg);
        if(res.code == 1){
            $('#completeForm').attr('class','layui-btn layui-btn-primary layui-btn-radius');
            $(obj).attr('class','layui-btn layui-btn-disabled layui-btn-radius');
            $(obj).removeAttr('onclick');
            $('#completeForm').bind("click",{obj:this,order:order,status:1},cancleOrder);
        }else{return false;}
    })
}

//兑换优惠券
function couponChange(obj,id){
    layer.alert('确定要兑换该优惠券吗？', {
        //skin: 'layui-layer-molv' //样式类名  自定义样式
        title:'提示',
        closeBtn: 1    // 是否显示关闭按钮
        ,anim: 1 //动画类型
        ,btn: ['确定','取消'] //按钮
        ,icon: 3   // icon
        ,yes:function(){
            var load = layer.load(2);
            $.post('/jdroom/Couponlist/changeCoupon',{'id':id},function(res){
                layer.close(load);
                if(res.code == 1){
                    layer.msg(res.msg);
                    $(obj).attr('class','layui-btn layui-btn-disabled layui-btn-sm');
                    $(obj).removeAttr('onclick');
                }else{
                    layer.msg(res.msg);return false;
                }
            },'json')
        }
        ,btn2:function(){
            //layer.msg('取消了')
        }});
}
/**
 *用户列表当为企业的时候点击查看企业的信息
 **/
function showCompanyInfo(id,type){
    $.post('/jdroom/User/searchCompany',{'id':id},function(rsd){
        if(rsd.code == 1){
            var comVar ;var payVar ;var content ; var kname='未认证';var kidno ='未认证';
            var res = rsd.data;
            if(type ==1) {
                res.is_verify_company==1 ?  comVar = '是' :  comVar = '否';
                res.is_verify_pay==1 ?  payVar = '是' :  payVar = '否';
                content = '<div style="padding: 50px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">公司名称：' + res.com_name + '<br><br>企业对公银行账号：' + res.cardno + '<br><br>开户行支行：' + res.subbranch + '<br><br>开户行名称：' + res.bank + '<br><br>开户省市：' + res.provice + '-' + res.city + '<br><br>打款金额：' + res.money + '元' +
                    '<br><br>是否进行了企业信息认证：' + comVar + '<br><br>是否进行了打款认证：' + payVar + '</div>';
            }else{
                if( res){kname =res.name;kidno =res.idno;}
                content ='<div style="padding: 50px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">姓名：' + kname + '<br><br>身份证号：' + kidno + '<br><br></div>';
            }
            layer.open({
                type: 1
                ,title: '认证信息展示' //不显示标题栏
                ,closeBtn: false
                ,area: '450px;'
                ,shade: 0.8
                ,id: 'LAY_layuipro' //设定一个id，防止重复弹出
                ,btn: ['关闭']
                ,btnAlign: 'c'
                ,content: content
                ,success: function(layero){
                }
            });
        }else{
            layer.msg(res.msg);return false;
        }
    },'json')
}

/**
 *订单列表中查看需求描述
 **/
function lookContent(order){
    $.post('/jdroom/Orderlist/contentSearch',{'order':order},function(rsd){
        var com;
        if(rsd.code == 0){
            layer.msg(rsd.msg);return false;
        }
        rsd.data == null ? com ='暂无内容' : com =rsd.data;
        layer.open({
            type: 1
            ,title: '单号：'+order+'&nbsp;&nbsp;的需求描述' //不显示标题栏
            ,closeBtn: false
            ,area: '450px;'
            ,shade: 0.5
            ,id: 'LAY_layuipro' //设定一个id，防止重复弹出
            ,btn: ['关闭']
            ,btnAlign: 'c'
            ,content: '<div style="padding: 50px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">'+com+'</div>'
            ,success: function(layero){
            }
        });
    },'json')
}

/**
 *查看消息内容
 **/
function lookMessage(id){
    $.post('/jdroom/message/contentSearch',{'id':id},function(rsd){
        var com;
        if(rsd.code == 0){
            layer.msg(rsd.msg);return false;
        }
        rsd.data == null ? com ='暂无内容' : com =rsd.data;
        layer.open({
            type: 1
            ,title: '消息内容' //不显示标题栏
            ,closeBtn: false
            ,area: '450px;'
            ,shade: 0.5
            ,id: 'LAY_layuipro' //设定一个id，防止重复弹出
            ,btn: ['关闭']
            ,btnAlign: 'c'
            ,content: '<div style="padding: 50px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">'+com+'</div>'
            ,success: function(layero){
            }
        });
    },'json')
}
/**
 * 赠送优惠券给用户
 * **/
function sendCoupon(id){
    var money;
    layer.prompt({title:'请输入赠送的钱数，保留两位小数'},function(value, index, elem){
        money =parseInt(value*100)/100;
        var load = layer.load(2);
        $.post('/jdroom/User/sendCoupon',{id:id,value:money},function(res){
            layer.close(load);
            layer.msg(res.msg);
            if(res.code ==0){
                return false;
            }
        },'json');
        layer.close(index);
    });

}

/**
 * 消息发送
 * **/
function sendMessages(){
    layer.open({
        title:'消息发送',
        area: ['700px', '500px'],//宽高
        type: 2,
        anim: 4,
        //cancle:function(index, layero){
        //    alert(index);
        //    window.location.reload();
        //},
        content: ['/jdroom/Message/add','no']
    });
}

/**
 * 退出
 * **/
function logout(){
    $.post('/jdroom/login/logout','',function(res){
        if(res.code ==1){
            layer.msg(res.msg);
            window.location.href=res.url;
        }
    })
}
//设置律师函价格
function setPrice(){
    layer.prompt({title:'请输入律师函价格',anim :3},function(value, index, elem){
        $.post('/jdroom/Tool/setPrice',{value:value},function(res){
            layer.msg(res.msg);
            if(res.code==1)
                window.location.reload();
            else
                return false;
        })
    })
}
//搜索查询用户
function findphone(){
    var phone =$('#searchPhone').val();
    if(phone==''){layer.msg('请输入要搜索的手机号');return false;}
    var reg=/^(((13[0-9])|(14[0-9]{1})|(17[0-9])|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]))+\d{8})$/;
    if(!reg.test(phone)){
        layer.msg('请输入正确的手机号'); return false;
    }
    $.post('/jdroom/message/searchUser',{phone:phone},function(res){
        if(res.code == 0){layer.msg(res.msg);return false;}
        $('#showPhone').find('p').remove();
        $('#showPhone').append(' <input class="layui-btn layui-btn-radius layui-btn-primary" name="attr_phone" attr-id="'+res.data+'" value="'+phone+'"><input name="userid" type="hidden" value="'+res.data+'">');
    })
}

//锁屏
function lockPage(){
    layer.open({
        title : '锁屏中......',
        area: '450px;',
        type : 1,
        content : '<div class="admin-header-lock" id="lock-box">'+
        '<div class="admin-header-lock-img"><img src="/static/admin/images/timg.jpg" style="height: 70px;width: 70px;border-radius: 100%;  box-shadow: 0 0 30px #44576b;margin-left: 140px;margin-bottom: 15px;margin-top: 10px;" class="userAvatar"/></div>'+
        //'<div class="admin-header-lock-name" id="lockUserName">驊驊龔頾</div>'+
        '<div class="input_btn" class="layui-inline">'+
        '<input type="password" style="width: 75%;" class="admin-header-lock-input layui-input" autocomplete="off" placeholder="请输入密码解锁.." name="lockPwd" id="lockPwd" />'+
        '<button class="layui-btn" id="unlock" style="float: left;margin-top: -37px;       margin-bottom: 10px; margin-left: 80%;">解锁</button>'+
        '</div>'+
        //'<p>请输入“123456”，否则不会解锁成功哦！！！</p>'+
        '</div>',
        closeBtn : 0,
        shade : 0.9,
        success : function(){
            //判断是否设置过头像，如果设置过则修改顶部、左侧和个人资料中的头像，否则使用默认头像
            if(window.sessionStorage.getItem('userFace') &&  $(".userAvatar").length > 0){
                $(".userAvatar").attr("src",$(".userAvatar").attr("src").split("images/")[0] + "images/" + window.sessionStorage.getItem('userFace').split("images/")[1]);
            }
        }
    })
    $(".admin-header-lock-input").focus();
}
$(".lockcms").on("click",function(){
    window.sessionStorage.setItem("lockcms",true);
    lockPage();
})
// 判断是否显示锁屏
if(window.sessionStorage.getItem("lockcms") == "true"){
    lockPage();
}
// 解锁
$("body").on("click","#unlock",function(){
    if($(this).siblings(".admin-header-lock-input").val() == ''){
        layer.msg("请输入解锁密码！");
        $(this).siblings(".admin-header-lock-input").focus();
    }else{
        $.post('/jdroom/Login/getLightPassword',{pass:$(this).siblings(".admin-header-lock-input").val()},function(res){
            if(res.code == 1){
                window.sessionStorage.setItem("lockcms",false);
                $(this).siblings(".admin-header-lock-input").val('');
                layer.closeAll("page");
            }else{
                layer.msg("密码错误，请重新输入！");
                $(this).siblings(".admin-header-lock-input").val('').focus();
            }
        })
    }
});
//解锁锁屏
$(document).on('keydown', function() {
    if(event.keyCode == 13) {
        $("#unlock").click();
    }
});

//点击上传下载
$('#uploadDown').click(function(){
    var order=$(this).attr('attr-order');
    layer.open({
        title:'电子发票/律师函-上传/下载',
        area: ['700px', '500px'],//宽高
        type: 2,
        anim: 4,
        content: ['/jdroom/Orderlist/getShowUpload?order='+order,'no']
    });
})
//用户分析的时间搜索提交
function timeSearchUser(){
    var startTime = $('#usertime_start').val();
    var endTime = $('#usertime_end').val();
    if(endTime < startTime){
        layer.msg('开始时间不能大于结束时间');return false;
    }
    $.post('jdroom/Chart/user',{start:startTime,end:endTime},function(res){

    })
}