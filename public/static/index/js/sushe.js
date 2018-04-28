/*==网站公共文件==*/
/* *移动端适配尺寸大小* */
var host="/";
var urlPhone =GetQueryString('mobile');
var urlPhone2 = urlPhone.substr(0,3)+"****"+urlPhone.substr(7);
$('#showTel').text(urlPhone2);
function GetQueryString(e) {
    var t = new RegExp("(^|&)" + e + "=([^&]*)(&|$)");
    var a = window.location.search.substr(1).match(t);
    if (a != null) return a[2];
    return ""
}
/*--01. 顶部关闭去下载提示--*/
function appStoreClose(obj)
{
  $('.appStore').fadeOut();
}


/*弹出层显示*/
function acceptBtn(obj)
{
	$('.zhuce').fadeIn();
}

/*关闭弹出层*/
function zhuceClose(obj)
{
	$('.zhuce').fadeOut();
	$('.downloadApp').fadeOut();
}


var successConts='';
successConts+='<section class="downloadApp">'+
   	 '<div class="downloadMain">'+
   	   '<p onclick="zhuceClose(this)" class="zhuceClose"></p>'+
   	   '<p class="download_text download_text1">优惠券已放入你的账户</p>'+
   	   '<p class="download_text">请下载APP查看</p>'+
   	   '<a class="downloadNow" href="https://itunes.apple.com/cn/app/诉舍/id1364772462?mt=8">马上下载</a>'+
   	 '</div>'+
   '</section>';


/*领取优惠券判断*/
/*发送验证码*/
var InterValObjA; //timer变量，控制时间  
var count1 = 60; //间隔函数，1秒执行
var curCount1;//当前剩余秒数
function sendMessage(obj) {  
    var phoneval=$("#phone").val();
    var _self=$(obj);
    var code=$(_self).val();
    if(!checkMobile(phoneval)){
    	$(obj).removeClass('active');
        alert('请输入手机号码');
        return false;
    }else{
     curCount1 = count1;
    //设置button效果，开始计时  
     $(_self).attr("disabled", "true");  
     $(_self).val(curCount1 + "秒后重发");
     InterValObjA = window.setInterval(SetRemainTimeA, 1000); //启动计时器，1秒执行一次
          $.ajax({
           type: "POST",
           url:"/Tool/sendSms",
           data:{mobile:phoneval},
           error: function(XMLHttpRequest, textStatus, errorThrown){
            alert('系统出错');
          },
          success: function(data) {
              console.log(data);
              alert('验证码发送成功,请注意查收');
          }  
        });
    }
//请求后台发送验证码   
}  
//timer处理函数  
function SetRemainTimeA() {  
    if (curCount1 == 0) {
    	$("#codeBtn").addClass('active');
        window.clearInterval(InterValObjA);//停止计时器  
        $("#codeBtn").removeAttr("disabled");//启用按钮  
        $("#codeBtn").val("获取验证码");
    }  
    else {  
        curCount1--;
        $("#codeBtn").removeClass('active');
        $("#codeBtn").val(curCount1 + "秒后重发");
    }  
}  
/*防止没填手机号就点击验证码*/
function checkMobile(tel) {
    var reg=/^(((13[0-9])|(14[0-9]{1})|(17[0-9])|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]))+\d{8})$/;
    if (reg.test(tel)) {
        return true;
    }else{
    	  //alert('请输入正确的手机号');
        return false;
    };
}

/*点击领取按钮*/
function receiveBtn(obj){
  var phone=$('#phone').val();
  var code=$('#code').val();
  if(phone==''  ||  code==''){
  	alert('请填写完整信息');
  	return false;
  }else{
      $.post('/login/userRegister',{mobile:phone,password:'','is_agree':1,'invite_phone':urlPhone,'code':code},function(result){
          var res = eval("("+result+")");//转为json
          if(res.code ==0){
              alert(res.message);return false;
          }
          $('.zhuce').fadeOut();
          setTimeout(function(){
              $('body').append(successConts);
          },200)
      })
  }
}

