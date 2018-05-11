<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
Route::rule('/login/userLogin','index/login/userLogin');
Route::rule('/login/userRegister','index/login/userRegister');
Route::rule('/login/thirdAccountDeal','index/login/thirdAccountDeal');
Route::rule('/login/companyValid','index/login/companyValid');
Route::rule('/login/valiCompanyPay','index/login/valiCompanyPay');
Route::rule('/Tool/sendSms','index/Tool/sendSms');
Route::rule('/Tool/searchOpenidIsHave','index/Tool/searchOpenidIsHave');
Route::rule('/Tool/phoneCodeValidate','index/Tool/phoneCodeValidate');
Route::rule('/Tool/userIsRegister','index/Tool/userIsRegister');
Route::rule('/Tool/agreeProtocol','index/Tool/agreeProtocol');
Route::rule('/Tool/getLayerLetter','index/Tool/getLayerLetter');
Route::rule('/Tool/cancleOrder','index/Tool/cancleOrder');
Route::rule('/Tool/getUserInfo','index/Tool/getUserInfo');
Route::rule('/Tool/getCouponList','index/Tool/getCouponList');
Route::rule('/Tool/conversionCoupon','index/Tool/conversionCoupon');
Route::rule('/Tool/getPolicyClause','index/Tool/getPolicyClause');
Route::rule('/Tool/expressSearch','index/Tool/expressSearch');
Route::rule('/Tool/changePhone','index/Tool/changePhone');
Route::rule('/Tool/changePassword','index/Tool/changePassword');
Route::rule('/Tool/forgetPassword','index/Tool/forgetPassword');
Route::rule('/Tool/imageUploadT','index/Tool/imageUploadT');
Route::rule('/Tool/optionFeedBack','index/Tool/optionFeedBack');
Route::rule('/Tool/eSignApi','index/Tool/eSignApi');
Route::rule('/Tool/getFiles','index/Tool/getFiles');
Route::rule('/Tool/zmxyCallBack','index/Tool/zmxyCallBack');
Route::rule('/Tool/zmxyResultSearch','index/Tool/zmxyResultSearch');
Route::rule('/Tool/zmxyInit','index/Tool/zmxyInit');
Route::rule('/Tool/zmrzCallBack','index/Tool/zmrzCallBack');
Route::rule('/Tool/getOrderDetail','index/Tool/getOrderDetail');
Route::rule('/Tool/userFileIsDel','index/Tool/userFileIsDel');
Route::rule('/Tool/getPrice','index/Tool/getPrice');
Route::rule('/Tool/countMoney','index/Tool/countMoney');
Route::rule('/Alipay/zfbCallBack','index/Alipay/zfbCallBack');//支付宝的授权回调
Route::rule('/Alipay/zfbPayCallBack','index/Alipay/zfbPayCallBack');//支付宝的回调
Route::rule('/Alipay/getNotifyData','index/Alipay/getNotifyData');//微信支付的通知
Route::rule('/Alipay/getWxCallBack','index/Alipay/getWxCallBack');//微信支付的回调
Route::rule('/Alipay/unifiedOrder','index/Alipay/unifiedOrder');//微信的开始下单
Route::rule('/Alipay/zfbPay','index/Alipay/zfbPay');//支付宝的开始下单
Route::rule('/Alipay/wxrefundapi','index/Alipay/wxrefundapi');//微信退款
Route::rule('/Writeletter/getLetterList','index/Writeletter/getLetterList');
Route::rule('/Writeletter/getMarkMessage','index/Writeletter/getMarkMessage');
Route::rule('/Writeletter/getPayInfo','index/Writeletter/getPayInfo');
Route::rule('/Writeletter/submitFile','index/Writeletter/submitFile');
Route::rule('/Writeletter/postMarkIdea','index/Writeletter/postMarkIdea');
Route::rule('/Writeletter/writeAddress','index/Writeletter/writeAddress');
Route::rule('/Writeletter/writeLetter','index/Writeletter/writeLetter');
Route::rule('/Writeletter/getUserAllOrderList','index/Writeletter/getUserAllOrderList');
Route::rule('/about','index/index/about');
Route::rule('/helpcenter','index/index/helpcenter');

