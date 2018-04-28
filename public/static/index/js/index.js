/*苹果二维码显示*/
function downbtn_ios(obj){
   $('.banner .downModal').fadeIn();
   $('.banner .close').fadeIn();
   $('.downModal p').eq(0).show().siblings('p').hide();
}
/*关闭二维码*/
function CloseBtn(obj)
{
  $('.banner .downModal').hide();
  $('.banner .close').hide();
}

