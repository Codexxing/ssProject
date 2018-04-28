layui.use(['form','layer','jquery'],function(){
    var form = layui.form,
        layer = parent.layer === undefined ? layui.layer : top.layer
        $ = layui.jquery;

    $(".loginBody .seraph").click(function(){
        layer.msg("惊不惊喜？开不开心？还是老老实实的找管理员去注册吧",{
            time:5000
        });
    })

    //登录按钮
    form.on("submit(login)",function(data){
        $(this).text("登录中...");
        var _self = $(this);
            //$(this).attr("disabled","disabled").addClass("layui-disabled");
        var name = $('#userName').val();
        var pass = $('#password').val();
        var code = $('#code').val();
        $.post('/jdroom/login/login',{username:name,password:pass,verify:code,},function(res){
            console.log(res);
            layer.msg(res.msg);
            if(res.code == 0) {
                _self.text("登录");
                return false;
            } else{
                window.location.href = res.url;
            }
        })
        return false;
    })

    //表单输入效果
    $(".loginBody .input-item").click(function(e){
        e.stopPropagation();
        $(this).addClass("layui-input-focus").find(".layui-input").focus();
    })
    $(".loginBody .layui-form-item .layui-input").focus(function(){
        $(this).parent().addClass("layui-input-focus");
    })
    $(".loginBody .layui-form-item .layui-input").blur(function(){
        $(this).parent().removeClass("layui-input-focus");
        if($(this).val() != ''){
            $(this).parent().addClass("layui-input-active");
        }else{
            $(this).parent().removeClass("layui-input-active");
        }
    })
})
