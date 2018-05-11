<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/22
 * Time: 17:39
 */

namespace app\jdroom\controller;


use app\common\controller\AdminBase;
use app\common\model\Orderlist as OrderlistModel;
use app\index\controller\Tool as Tools;
use app\index\controller\Alipay as Alipay;
use think\Config;
use think\Request;
use think\Db;
use think\Session;
use PHPMailer\PHPMailer\PHPMailer;

class Tool extends AdminBase
{
    private $userPhone;//注册用户的手机号
    protected function _initialize()
    {
        parent::_initialize();
        $this->order_list = new OrderlistModel();
       $uid = Request::instance()->param('uid');
       $this->userPhone = getOneUserVal(['id'=>$uid],'mobile');
    }
    //接单与取消接单
    public function orderReceiving(){
        if(Request::instance()->isPost()){
            $order=Request::instance()->param('order');//订单号
            $type=Request::instance()->param('type');//类型
            $cpmplete = getOrderCancle($order);
            if($cpmplete == 1){ $this->error('该订单已经完成,请刷新页面');exit;}else if($cpmplete == 2){ $this->error('该订单已经被取消,请刷新页面');exit;}
            if($type=='add') {
                $receive = Db::table('os_order_list')->where(['order_number' => $order])->value('receive_order');
                if($receive == 1){  $this->error('此订单已经接单');  exit; }
                $uid = Session::get('admin_id');
                $arr = [
                    'auid' => $uid,
                    'receive_order' => 1,
                    'receivetime' => getFormatTime()
                ];
                Db::name('order_list')->where(['order_number' => $order])->update($arr);
                createLog('成功接单，单号：'.$order);
                $this->success('接单成功');
            }else if($type=='cancle') {
                $arr = [
                    'auid' => 0,
                    'receive_order' => 0,
                    'canclereceivetime' => getFormatTime()
                ];
                Db::name('order_list')->where(['order_number' => $order])->update($arr);
                createLog('取消接单，单号：'.$order);
                $this->success('取消成功');
            }

        }else{
            $this->error('请求类型错误');
        }
    }

    /**
     * 取消订单与完成订单
    **/
    public function cancleOrder(){
//        if(Request::instance()->isPost()){
            $order=Request::instance()->param('order');//订单号
            $status=Request::instance()->param('status');//状态   1完成订单  0请求取消订单
           $list =  Db::name('order_list')->where(['order_number'=>$order])->find();
//            $cpmplete = getOrderCancle($order);
            if($list['is_complete'] == 2){
                $this->error('此订单已经被取消');
                exit;
            }else   if($list['is_complete'] == 1){
                $this->error('此订单已经完成，无法操作');
                exit;
            }
            switch($status){
                case 0://请求取消订单
                    $reason=Request::instance()->param('reason');
                    $arr = [
                        'cancletime' => config::get('dateTime'),
                        'is_complete' => 2,
                        'cancle_reason' => $reason
                    ];
                    $message = '取消订单成功';
                    createLog('点击取消了订单，单号：'.$order.'，取消理由：'.$reason);
                    $rewards =Db::name('rewards')->where(['order_number'=>$list['order_number'],'pay_status'=>1])->find();
                    if($rewards) {
                        if ($rewards['client_type'] == 1) {//支付宝
                            $this->refundMoneyBack($order);
                        } else {//微信
                            $pay = new Alipay();
                            $pay->wxrefundapi($order);
                        }
                    }
                    break;
                case 1://请求完成订单
                    $number = Db::table('os_order_list')->where(['order_number' => $order])->value('express_number');
                    if(!$number){   $this->error('请先填写快递单号');exit;}
                    $arr = [
                        'completetime' => config::get('dateTime'),
                        'is_complete' => 1
                    ];
                    createLog('点击完成了订单，单号：'.$order);
                    $message = '成功完成订单';
                    break;
            }
            Db::name('order_list')->where(['order_number' => $order])->update($arr);
            $this->success($message);
//        }else{
//            $this->error('请求类型错误');
//        }
    }
    /**
     * 填写快递单号
    **/
    public function expressOrder(){
        if(Request::instance()->isPost()){
            $order = Request::instance()->param('order');
            $cpmplete = getOrderCancle($order);
            if($cpmplete == 2){
                $this->error('此订单已经被取消');
                exit;
            }else if($cpmplete == 1){
                $this->error('此订单已经完成，无法操作');
                exit;
            }
           $signServiceId = Db::table('os_order_list')->where(['order_number' => $order])->value('signServiceId');
            if(!$signServiceId){ $this->error('请等待用户签名盖章之后在填写');        exit;}
            $file =  Db::name('file_stamp')->where(['order_number'=>$order])->find();
            if(!$file['file_stamp']){$this->error('请先上传盖好章的律师函');        exit;}
            $express_order = Request::instance()->param('value');//快递单号
            //验证订单号是否正确
//            $is_number = searchExpressAli($express_order);
//            if($is_number->status == 0) {

                //然后更新成新的时间
                $arr = [
                    'expresstime' => getFormatTime(),
                    'step' => 6,
                    'is_complete' => 1,
                    'express_complete' => 1,
                    'express_number' => $express_order
                ];
                Db::name('order_list')->where(['order_number' => $order])->update($arr);
                createLog('填写了快递单号，单号：' . $order . '，快递单号：' . $express_order);
                //同时自动发送邮件给用户
                //查询律师函
//                $file =  Db::name('layer_file')->where(['order_number'=>$order])->order('times desc')->find();
                $address = Db::name('address')->where(['order_number' => $order])->find();
                $list = Db::name('order_list')->where(['order_number' => $order])->field('shouquanUrl,solid_url_after,express_number')->find();
                if(!$list['express_number']) {
                    if ($address['accept_type'] != 0) {
                        //给对方发邮件
                        $r = $this->sendMaild($address['accept_email'], $file['file_stamp'], $address, 1, '');
                        if ($r)
                            Db::table('os_order_list')->where(['order_number' => $order])->setField('email_complete', 1);
                    }
                    //给发件人发邮件
                    $this->sendMaild($address['send_email'], $file['file_stamp'], $address, 0, $list);
                    $sms = new Tools();
                    $sms->sendSms(['order' => $order, 'mobile' => $this->userPhone, 'content' => '你的函件已投递']);
                }else{
                    Db::table('os_order_list')->where(['order_number' => $order])->setField('express_number', $express_order);
                }
                $this->success('填写完成');
//            }else{
//                $this->error($is_number->msg);
//            }
        }else{
            $this->error('请求类型错误');
        }
    }
    /**
     * 确认填写的需求是不是有问题
     * $status  0需要补充   1不需要补充的
     **/
    public function requirement(){
        if(Request::instance()->isPost()){
            $order = Request::instance()->param('order');
            $status = Request::instance()->param('status');//状态
            $auid = Request::instance()->param('auid');//状态
            Db::name('order_list')->where(['order_number'=>$order])->update(['content_issue'=>$status,'file_status'=>1]);
            if($status==0) {
                $time=config::get('dateTime');
                $content = Request::instance()->param('content');//内容
                $arr = [
                    'order_number' => $order,
                    'content' => $content,
                    'status' => 1,
                    'send_type' => 1,
                    'createtime' => $time,
                    'updatetime' => $time,
                ];
                $auid == Session::get('admin_id') ? $arr['aid'] = $auid : $arr['aid'] = Session::get('admin_id');
                Db::name('message')->insert($arr);
                //然后生成资料补充
                Db::name('letters_file')->insert(['order_number'=>$order,'status'=>1 ,'createtime' => $time,'updatetime' => $time]);
            }
            $status==0 ? createLog('提示需求描述有问题，单号：'.$order) : createLog('确认需求描述没有问题，单号：'.$order);
            $this->success('保存成功，请等待用户修改');
        }else{
            $this->error('请求类型错误');
        }
    }
    /**
     * 律师对资料的描述
     * $status  0资料有误   1资料确认
     **/
    public function fileVerify(){
        if(Request::instance()->isPost()){
            $order = Request::instance()->param('order');
            $status = Request::instance()->param('status');//状态
            $auid =Db::name('order_list')->where(['order_number'=>$order])->field('auid,uid')->find();
            if($status==0) {
                $content = Request::instance()->param('content');//内容
                $arr = [
                    'order_number' => $order,
                    'content' => $content,
                    'status' => 2,
                    'aid' => $auid['auid'],
                    'uid' => $auid['uid'],
                    'send_type' => 1,
                    'createtime' => getFormatTime(),
                    'updatetime' => getFormatTime(),
                ];
                Db::name('message')->insert($arr);
                $me = '你的资料描述存在问题，请及时补充需求';
            }else{
                Db::name('order_list')->where(['order_number'=>$order])->setField('layerletter_have',0);
                $me = '你的资料描述已确认，律师正在出涵';
            }
            Db::name('order_list')->where(['order_number'=>$order])->setField('layerIssue',$status);
            $status==0 ? createLog('提示确认资料有问题，单号：'.$order) : createLog('确认了资料没有问题，单号：'.$order);
            $sms = new Tools();
            $sms->sendSms(['order'=>$order,'mobile'=>$this->userPhone,'content'=>$me]);
            $this->success('提交成功');
        }else{
            $this->error('请求类型错误');
        }
    }
    /**
     * 这是好的 测试通过的
     * $type  0是给自己的  1给对方的
     **/
    private function sendMaild($to='',$file='',$data=[],$type=0,$list=''){
        if($type == 0){
            $title='律师函以及其他资料';
            $body='';
        }else if($type ==1){
            $title='律师函';
            $body='';
        }
        //实例化PHPMailer核心类
        $mail = new PHPMailer();
        //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        $mail->SMTPDebug = 0;
        //使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        //smtp需要鉴权 这个必须是true
        $mail->SMTPAuth=true;
        //链接qq域名邮箱的服务器地址
//        $mail->Host = 'smtp.163.com';
        $mail->Host = 'smtp.exmail.qq.com';
        //设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
        $mail->Port = 465;
        //设置smtp的helo消息头 这个可有可无 内容任意
        $mail->Helo = '汉卓律师事务所邮箱发送';
        //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
        $mail->Hostname = '';
        //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
        $mail->CharSet = 'UTF-8';
        //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName = '汉卓律师事务所';
        //smtp登录的账号 这里填入字符串格式的qq号即可
//        $mail->Username ='ly671205@163.com';
        $mail->Username ='shuqin@hanzhuo.cn';
        //smtp登录的密码 使用生成的授权码 你的最新的授权码
//        $mail->Password = 'ly67120521';
        $mail->Password = 'cp5o6bJmUGziPGZu';
        //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
//        $mail->From = 'ly671205@163.com';
        $mail->From = 'shuqin@hanzhuo.cn';
        //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->isHTML(true);
        //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
        $mail->addAddress($to,'律师函');
        if($type ==1) {
            $mail->addBCC('shuqin@hanzhuo.cn');                     // 添加密送者，Mail Header不会显示密送者信息
            $mail->addCC($data['send_email']);                      // 添加抄送人
        }
//        $mail->ConfirmReadingTo = '18668999188@163.com';              // 添加发送回执邮件地址，即当收件人打开邮件后，会询问是否发生回执
//        $mail->addBCC('100227760@qq.com');                   // 添加密送者，Mail Header不会显示密送者信息
        //添加多个收件人 则多次调用方法即可
        // $mail->addAddress('xxx@qq.com','lsgo在线通知');
        //添加该邮件的主题
        $mail->Subject = $title;
        //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = '名为'.$data['send_name'].'的给你发了一份邮件';

        //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
        $mail->addAttachment($file,'律师函.pdf');
        if($type == 0 && !empty($list)){//给自己发送三个文件
            $mail->addAttachment($list['shouquanUrl'],'授权书.pdf');
            $mail->addAttachment($list['solid_url_after'],'固化文件.pdf');
        }
        //同样该方法可以多次调用 上传多个附件
        // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');
        $mail->WordWrap=50;//换行字数
        $status = $mail->send();
//        $mail->ErrorInfo;
        //简单的判断与提示信息

        if($status) {
            return true;
        }else{
            return false;
        }
    }
    /**
     * 支付宝支付进行退款
     * $order 自己生成的订单编号
     */
    private function refundMoneyBack($order){
//        $order='JD1523665';
        vendor('zfbpay.AopSdk');//加载支付宝配置
        $res = Db::name('rewards')->where(['order_number'=>$order,'pay_status'=>1])->find();
        if(!$res){return false;}
        $aop = new \AopClient;
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = '2018030702330869';
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEA0hiR+in75x6+Y8Nwv63TiqSCtLNw1E3yJx/RcqwyOX5yi4hUMbBZLYX7oGqkbuMbnigQlTE7KkcsYKsNlfP97XEyEb3exaA99xFI07OUfRFOWI9rQuJLfIrTsgf6kaYb2BKjK1deKV/zSHrWGkDLomDIK/qjd2ABc+ALFQ8ODF+fLJUAjq+8zfXoDYQBEsnhIXR1T5VT9hQLeR2Mk7W00NFaXR6YHEEsvbn/ur5r0g0BTHF1+nwKSmSDs/BP/t7US/3yZmdYy4D7HwTPAeTM/BzfMXBRaVy/x9DLvEjrSxXlnG3wLMKwwHrJCw5NLwPW0pVvKhfkXhRPt1hs1Qr+ZwIDAQABAoIBAGc3RPg3CheoxfZzPQAeYPAbFE+8XHEg/hd0jUyeBmqykYm1Z0+mCJnR2iYcXj5P+vB/VG5Han7Byq6POrRx002EsAmBU2GZ8PdhmXrQHeI3z1q40wRf8p/0AD7VpqdJiSJlPZ58ZLlE91ujZW4uaRyUGaNplkd+dhg8eJVW+RrgN0L9j7Y4hZcqSg6HX76GJtVflMyBUpadUMhmHvGBwb/w+5/HauiE5yvqCAyuPVHh+hnyF+vni/W2tSU/HTS7uBUo6K5gvqxB08+UvTEIrhPr8ey6ths2/UW+nyKzsGxcD1DLE2yL5VmyeyHaNAF9RMJ4JaM2OKtrbiYdb8MFN+ECgYEA9AHd0iiggY6xZ2hkusKg1Jwq+4tmZrImPk/q+Bnbt+9Kg6G5yqsEF8K85hr1+qE0WUT+cnc8hDPrPeije2f+nteEDcB9sp2pnGWbqmNBP04uQJ9a+HKB61q7lG9tH9utPRalD5lSEVEhRK5xcXegNgX9Pa1qo3btqDQwWevNtpECgYEA3GwG4owglQZjAEC8mZXWE6wj+BucssMTLyDLSP/mkoGZcp1AQQb7dNkDIJVnkQimwXUJfQlc3T7BouKqi7433NdrSdCjZKubZxPbhU5n5VO4I9lHrdGIJJc5awj/bB4bAMGvThCeZdc9TNQ3vOiSe0/kFdKMm4INdf0lFdNTkXcCgYEAp4WIhzqBR7Fxtq8DSP1Kce1tzRkdirAQdYNkrEUEhjlxDQJBjhTvUGjQS6KC8jkuuYMWtfuKvrDudqh7ZMQ3GVKZRN+87J41zjwsLUTLjOzd8Fv3ls72x2CZnAUMBG1LeL9NP3Jh16W9k2u4UtBwW+aswGWI6wVBkNOTxoiPySECgYEAnKj+v7deVOykIoLgSLxw2ayIKAff+EGjeeRx9yFBzDDmUcqn50/CGos+qMLnR/KBKpA3PTIRWYIH9+/nzMhWRdSpgV4TMzKwkbNQW0+dkiVNg9UjF8wLWg5NFeDgQQSopoICSZaQLcur2tYeA1q8+X2Pm1745nGphYl0+S0ogQMCgYBJ4uAVwJ50m92aiRWWpmLKl0gYsg0We1iaySrPxkSCHiaDbWzBSfCQa2PXiu3Jk1FUGrL+/PcQB7Dlu8UrjdRozvJ21XPeGcpamC/a8ZNCBfA7QnqykAtPO9s/qTe9DyjXcZ1Glm38dNkkfJczUHDB7l2m+wofBGiz52wdYfedxA==';
        $aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAij23gGVC7hRNfk3UUmefB6mccydcOrMgAtEdsrY8tdKY9GF1E+bNGCMDoFq2+BzPWefdwVP3kDcyqxIMpfPI7PPGbqqh5vQhfq5htyIHaRLQqPKxAnnxUdl4Oyj6mgTKcNuPbj8mqzqQIeKJMLP9GM2AANG7LIDgejOSEF8p44QNK9NgEKaivu7aeUf65BEpaDjhPIVeZjRvILsKFjQygzZ5KsbxuNQ61O/LMqCeWLI89uHgS5asixmbh4J4XZt7oEZWINNZH5UKjGKWl13hAKPWq8BfwSDkJCnfRt6zer7sj/Ve9RuY7/vhhCtp7Cadhj5KCrF2C6RMw/0GT3gbvwIDAQAB';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='UTF-8';
        $aop->format='json';
        $request = new \AlipayTradeRefundRequest();
        $request->setBizContent(json_encode([
            "out_trade_no"=>$res['out_trade_no'],
            "trade_no"=>$res['trade_no'],
            "refund_amount"=>$res['total_amount'],
            "refund_reason"=>"正常退款",
            "out_request_no"=>"",
            "operator_id"=>"",
            "store_id"=>"",
            "terminal_id"=>""

        ]));
        $result = $aop->execute ($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
//        var_dump($responseNode);
//        var_dump($request);
        if(!empty($resultCode)&&$resultCode == 10000){
            Db::name('order_list')->where(['order_number'=>$order])->update(['is_refund_money'=>1,'is_refund'=>1]);
            Db::name('rewards')->where(['order_number'=>$order,'trade_no'=>$res['trade_no']])->update(['pay_status'=>3,'gmt_refund'=>$result->$responseNode->gmt_refund_pay,'refund_id'=>$result->$responseNode->trade_no]);
            return true;
        } else {
            return false;
        }
    }
    /**
     * 设置律师函价格
    **/
    public function setPrice(){
        $price = Request::instance()->param('value');
//        $money =getLayerPrice();
//        if($money < $price){
//            $this->error('价格不能大于'.$money);
//        }
        Db::name('price')->where(['id'=>1])->update(['money'=>$price,'updatetime'=>getFormatTime()]);
        createLog('修改了律师函价格'.$price.'元');
        $this->success('设置成功');
    }

}
