<?php
/**
 * Created by PhpStorm.
 * User: liyang
 * Date: 2018/1/12
 * Time: 14:02
 * File：支付类
 */

namespace app\index\controller;

use app\common\controller\HomeBase;
use think\config;
use think\Request;
use think\File;
use think\Db;
use think\Session;
use think\ucpaas\Ucpaas;
//use vendor\zfbpay;

class Alipay extends HomeBase{
    //微信支付接口API URL前缀
    const API_URL_PREFIX = 'https://api.mch.weixin.qq.com';
    //微信支付下单地址URL
    const UNIFIEDORDER_URL = "/pay/unifiedorder";
    //微信支付查询订单URL
    const ORDERQUERY_URL = "/pay/orderquery";
    //微信支付关闭订单URL
    const CLOSEORDER_URL = "/pay/closeorder";
    //微信支付公众账号ID
    private $appid;
    //微信支付商户号
    private $mch_id;
    //微信支付随机字符串
    private $nonce_str;
    //微信支付签名
    private $sign;
    //微信支付商品描述
    private $body;
    //微信支付商户订单号
    private $out_trade_no;
    //微信支付支付总金额
    private $total_fee;
    //微信支付终端IP
    private $spbill_create_ip;
    //支付结果回调通知地址
    private $notify_url;
    //微信支付交易类型
    private $trade_type;
    //微信支付支付密钥
    private $key;
    //微信支付证书路径
    private $SSLCERT_PATH;
    private $SSLKEY_PATH;
    public function _initialize() {
        vendor('zfbpay.AopSdk');//加载支付宝配置
        $this->key=config::get('wxConfig.key');
        $this->SSLCERT_PATH ='/var/www/html/sushe/public/cert/apiclient_cert.pem';//微信证书
        $this->SSLKEY_PATH = '/var/www/html/sushe/public/cert/apiclient_key.pem';//微信证书
    }
    /**
     * 支付宝支付的信息
     * @param uid用户id
     * @param order_number  订单号
    **/
    public function zfbPay(){
        if(Request::instance()->isPost()) {
            $lvMoney = 168;
            $uid = Request::instance()->param('uid');
            $order_number = Request::instance()->param('order_number');
            $reduced = Request::instance()->param('reduced_id');//默认为0
            $reduced == 0 ? $reduced_id = 0 : $reduced_id = $reduced;
            if($reduced_id != 0){
                $red_money = Db::table('os_reduced')->where(['uid'=>$uid,'id'=>$reduced_id])->value('money');
                $money = $lvMoney-$red_money;
                $money <= 0 ? $lvMoney = 0.01 : $lvMoney=$money;
            }
//            dump($lvMoney);die;
            $aop = new \AopClient;
            $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
            $aop->appId = "2018030702330869";
            $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAuCIQoK/SoMOhgJEoez0v4vu95Yk10hBC3Tgllpkwb2dhGc5Ogb7KltCCjxexHci5HdL1e8lDhYTzanrPJlBo6P+f6dfN0HKVLbnrJyNRa0G+ftq37ffatX5VssgbtaHAbEAQVSnLVY1MGXepu36nUh22JT5gUj+oWig7yLhXzRDLNYJzuYwQ1LmYssXY1WoA2YCUT+MTT6Uq6YB4+ClZkOVCRHq5L9Wl/4D3TabdA7OOmlOxyYdqYjIOer2jvpaAEZt5mIwKLIyem8iBsrmpjGwQpKCE6ekU7R8qMfhWP8dJ+BKW3lGaqdvSAz86GOzY8QeU+j2UlHyQnhbdNZAm6QIDAQABAoIBAQCgI5g8d82CXcHG0/fV/qf4C6LizwtMzzGnvZ4LNN2H9evgCqoCcxE0StFRa3Rxh9FfW8p9xtN/etpMX9Rq6QHkqfTE5hesUwrkws39styroHjxUH0obCf9MouLujdHJw731lueBYQ+um1VtNmcJBGW/Boel/ojbhOXnWgpMBHEWH3e+LTu8IKdTs1Q5t3XCuieVzZPUPwvrdqWY0xUKv3deQBSRLWiRLQZmYBX4lXc9U07W6roAwhDf8jMGhK6rRgHi9vLD6WDnmuw1TsJYVNFO0EnjLke/wBGpR1ukJXCjW2d+K6tY2q8a+NujVYtE5bkTxFOnCmTyk/Z/SJXKlrRAoGBAN/ndF71ac2rwDCONBzTcNeBtXrrL89ryd0s28DzEQY6PLJ7mbET37hbGSSiR0pntsqPflt3LfQuSS2kRJch/STsjKbXF0mxwP5XOFboi1arBFPPQU607Kvtj0WiDSpD9utL4w/TwRGfHZLHvH3iMGolYTjNmdCglgCnqNSVd+YdAoGBANKHJLbK9p7r5uUJ0BO8Qyxh7m0W5K65vXF1/kMOOq48FBxW4GTyqP4KB/5IyFrui1Ly4qPE+fVfKGQHjBNsAbZIXaWd6JxxhnupR7T51XGYWWUH3ZJUAzDn7kRicCBuVPvpmJo3CwtAECGtON2FSdC0lPUVTxZG6ZGZxDgLfvo9AoGAHUks027OE/SvAqrW7h5J2CZDEnzImBzFHoTLiYVBaMsdPUslYx/yVy6zLzN6l6TRJ9V09Ym1HQcg0zN1NT9g2P+HthrUFPOHBr1pxRRNhVyBCGVHaYAIpMRBxR3ZEvooxcX7QQq1ahShzZ0KbnyzUG7rNH7P65XViOhlEMksEZECgYAcMrG+Mg06WAqDHv9ZxVuR9EQFJI3YwVQYgF9gB+XgNHfVG+XOX5o2/Iz5iDIdy9mpcJLesKFyNh6o10Hx0AOisKqqZi4j1ijXI2NRYC0wm+FNYPJSiSIsSMRnMHAoR97mJvGjpj+6cAg5GoSFVzb911IkSYhgSXs7X0ci4pdNWQKBgQCKApJdHuNhnyMFA+sGFDA8YkU8TO92zdvfonWZ+g0Os8oWk0Bw/ex9bRzs7zTNZ+YErJ2GR/RN+7bFgOWw3zv17nskRTdYqIt2U11lZB29LIHz7NprJYoOJKW008vVXtlORXqD/h54Fi7POTiuwdcNStN8BXk2ot6VIoh2NP8Lfg==';
            $aop->format = "json";
            $aop->charset = "UTF-8";
            $aop->signType = "RSA2";
            $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAij23gGVC7hRNfk3UUmefB6mccydcOrMgAtEdsrY8tdKY9GF1E+bNGCMDoFq2+BzPWefdwVP3kDcyqxIMpfPI7PPGbqqh5vQhfq5htyIHaRLQqPKxAnnxUdl4Oyj6mgTKcNuPbj8mqzqQIeKJMLP9GM2AANG7LIDgejOSEF8p44QNK9NgEKaivu7aeUf65BEpaDjhPIVeZjRvILsKFjQygzZ5KsbxuNQ61O/LMqCeWLI89uHgS5asixmbh4J4XZt7oEZWINNZH5UKjGKWl13hAKPWq8BfwSDkJCnfRt6zer7sj/Ve9RuY7/vhhCtp7Cadhj5KCrF2C6RMw/0GT3gbvwIDAQAB';
            //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
            $request = new \AlipayTradeAppPayRequest();
            //SDK已经封装掉了公共参数，这里只需要传入业务参数
            $num = 'SH' . time() . 'S' . mt_rand();
            $bizcontent = json_encode([
                'body' => '律师函支付',
                'subject' => '诉舍',
                'out_trade_no' => $num,//此订单号为商户唯一订单号
                'total_amount' => $lvMoney,//保留两位小数
                'timeout_express' => '10m',
                'product_code' => 'QUICK_MSECURITY_PAY'
            ]);
            $request->setNotifyUrl("http://www.juedouxin.com/Alipay/zfbPayCallBack");////你在应用那里设置的异步回调地址
            $request->setBizContent($bizcontent);
            //这里和普通的接口调用不同，使用的是sdkExecute
            $response = $aop->sdkExecute($request);
           $id = Db::table('os_rewards')->where(['uid'=>$uid,'order_number'=>$order_number,'client_type'=>1])->value('id');
            if($id)
                Db::name('rewards')->where(['id'=>$id])->update([
                    'out_trade_no' => $num,
                    'total_amount' => $lvMoney,
                    'coupon_id' => $reduced_id,
                    'updatetime' => getFormatTime()
                ]);
            else
                Db::name('rewards')->insert([
                    'uid' => $uid,
                    'order_number' => $order_number,
                    'out_trade_no' => $num,
                    'client_type' => 1,
                    'pay_status' => 0,
                    'total_amount' => $lvMoney,
                    'platform_money' => 0,
                    'coupon_id' => $reduced_id,
                    'createtime' => getFormatTime(),
                    'updatetime' => getFormatTime()
                ]);
                Db::name('order_list')->where(['order_number' => $order_number])->update(['recude_id' => $reduced_id,'updatetime'=>getFormatTime()]);
            jsonSend(1, '成功', $response);//就是orderString 可以直接给客户端请求，无需再做处理。
        }else{
            jsonSend(0, '请求类型错误');exit;
        }
    }


    /**
     * 支付宝支付异步回调地址
     */
    public function zfbPayCallBack(){
        $data = [
            'callbackinfo'=>json_encode($_POST),
            'createtime'=> date('Y-m-d H:i:s',time()),
            'types' => 1
        ];
        Db::name('pay_callback')->insert($data);
        $id =  Db::name('pay_callback')->getLastInsID();
        $arr=$_POST;
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAij23gGVC7hRNfk3UUmefB6mccydcOrMgAtEdsrY8tdKY9GF1E+bNGCMDoFq2+BzPWefdwVP3kDcyqxIMpfPI7PPGbqqh5vQhfq5htyIHaRLQqPKxAnnxUdl4Oyj6mgTKcNuPbj8mqzqQIeKJMLP9GM2AANG7LIDgejOSEF8p44QNK9NgEKaivu7aeUf65BEpaDjhPIVeZjRvILsKFjQygzZ5KsbxuNQ61O/LMqCeWLI89uHgS5asixmbh4J4XZt7oEZWINNZH5UKjGKWl13hAKPWq8BfwSDkJCnfRt6zer7sj/Ve9RuY7/vhhCtp7Cadhj5KCrF2C6RMw/0GT3gbvwIDAQAB';
        $flag = $aop->rsaCheckV1($arr, NULL, $arr['sign_type']);
        $arr['trade_status'] == 'TRADE_SUCCESS'  ? $paystatus=1 : $paystatus=0;
        $info =  Db::name('rewards')->where(['out_trade_no'=>$arr['out_trade_no']])->field('uid,coupon_id,order_number')->find();
        Db::name('rewards')->where(['out_trade_no'=>$arr['out_trade_no']])->update([
            'trade_no'=>$arr['trade_no'],
            'trade_status'=>$arr['trade_status'],
            'pay_status'=>1,
            'is_coupon'=>$info['coupon_id'] == 0 ? 0 : 1,
            'total_amount'=>$arr['total_amount'],
            'updatetime'=>getFormatTime(),
            'callback_id'=>$id
        ]);

        if($flag){
            //验证成功
            if($paystatus==1){
                Db::name('order_list')->where(['order_number'=>$info['order_number']])->update(['paystatus'=>1,'step'=>3,'layerIssue'=>3]);
                if($info['coupon_id'] >0){
                    Db::name('reduced')->where(['id'=>$info['coupon_id']])->update(['is_use'=>1,'updatetime'=>getFormatTime()]);
                }
            }
            exit('success');//这个必须返回给支付宝，响应个支付宝，
        } else {
            exit('fail');
        }
    }
    public function zfbCallBack(){
        echo 'success';
    }

    /**
     * 支付宝支付进行退款
     * $order 自己生成的订单编号
     */
    public function refundMoneyBack1($order){
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
//        $rr = json_decode($request,true);
        if(!empty($resultCode)&&$resultCode == 10000){
            Db::name('order_list')->where(['order_number'=>$order])->update(['is_refund_money'=>1,'is_refund'=>1]);
            Db::name('rewards')->where(['order_number'=>$order,'trade_no'=>$res['trade_no']])->update(['pay_status'=>3,'gmt_refund'=>$result->$responseNode->gmt_refund_pay,'refund_id'=>$result->$responseNode->trade_no]);            return true;
        } else {
            return false;
        }
    }




    /***********************************************以下是微信的付款与退款********************************************************************/

    /**
     * 微信支付开始下单了
     * @param order_number 订单号  uid用户id
     */
    public function unifiedOrder(){
        $data=Request::instance()->param();
        $lvMoney=16800;
        $params=[];
        $this->appid = config::get('wxConfig.appid');
        $this->mch_id = config::get('wxConfig.mch_id');
        if($data['reduced_id'] != 0){
            $red_money = Db::table('os_reduced')->where(['uid'=>$data['uid'],'id'=>$data['reduced_id']])->value('money');
            $money = 168-$red_money;
            $money <= 0 ? $lvMoney = 1 : $lvMoney=$money*100;
        }

        $this->body = '诉舍-律师函支付';

        $this->out_trade_no =  'SH' . time() . 'S' . mt_rand();
        $this->total_fee =$lvMoney;//单位为分
        $this->trade_type = 'APP';
        $this->notify_url = 'http://www.juedouxin.com/Alipay/getWxCallBack';//支付成功后的结果通知
        $this->nonce_str = $this->genRandomString();
        $this->spbill_create_ip = $_SERVER['REMOTE_ADDR'];
        $this->params['appid'] = $this->appid;
        $this->params['mch_id'] = $this->mch_id;
        $this->params['nonce_str'] = $this->nonce_str;
//        $this->params['nonce_str'] = 'Oz8VtySPhVSrc816prvYjigqMmrtrpq3';
        $this->params['body'] = $this->body;
        $this->params['out_trade_no'] = $this->out_trade_no;
        $this->params['total_fee'] = $this->total_fee;
        $this->params['spbill_create_ip'] = $this->spbill_create_ip;
        $this->params['notify_url'] = $this->notify_url;
        $this->params['trade_type'] = $this->trade_type;
        //var_dump($this->params);
        //获取签名数据
        $this->sign = $this->MakeSign( $this->params );
        $this->params['sign'] = $this->sign;
        $xml = $this->data_to_xml($this->params);
        $response = $this->postXmlCurl($xml, self::API_URL_PREFIX.self::UNIFIEDORDER_URL);

        if( !$response ){
            jsonSend(0,'获取数据失败');exit;
        }
        $result = $this->xml_to_data( $response );
        if( $result['return_code'] == 'FAIL'){
            jsonSend(0, $result['return_msg']);exit;//支付失败
        }

        if( $result['return_code'] == 'SUCCESS'){
            array_key_exists('err_code',$result) ? $mm = $result['err_code'] : $mm=$result['return_code'];
            $code = $this->wxErrorCode($mm);//支付成功后但返回的错误码还是为success那就是没问题
            if($code != 'SUCCESS'){
                jsonSend(0, $code);exit;
            }
        }

        $result['return_code'] == 'SUCCESS' ? $prepayid = $result["prepay_id"] : $prepayid='';
        $da = $this->xml_to_data($xml);
        $reDa = [
            'appid'=> $this->appid,
            'partnerid'=> $this->mch_id,//商户
            'package'=> 'Sign=WXPay',
            'noncestr'=> $da['nonce_str'],//签名
            'prepayid'=>$prepayid,
            'timestamp'=> time(),
//            'timestamp'=> $this->getMillisecond(),sssss
        ];

        $reDa['sign']= $this->MakeSign($reDa);
        $reDa['packageValue']= 'Sign=WXPay';
        $id = Db::table('os_rewards')->where(['uid'=>$data['uid'],'order_number'=>$data['order_number'],'client_type'=>0])->value('id');
        if($id) {
            Db::name('rewards')->where(['id' => $id])->update([
                'out_trade_no' => $this->out_trade_no,
                'total_amount' => $lvMoney,
                'coupon_id' => $data['reduced_id'],
                'updatetime' => getFormatTime()
            ]);
        }else {
            $rewards = [
                'uid' => $data['uid'],
                'order_number' => $data['order_number'],
                'out_trade_no' => $this->out_trade_no,
                'client_type' => 0,
                'pay_status' => 0,
                'platform_money' => 0,
                'total_amount' => $lvMoney,
                'coupon_id' => $data['reduced_id'],
                'createtime' => getFormatTime(),
                'updatetime' => getFormatTime(),
            ];
            Db::name('rewards')->insert($rewards);
        }
        Db::name('order_list')->where(['order_number' => $data['order_number']])->update(['recude_id' => $data['reduced_id'],'updatetime'=>getFormatTime()]);
        $result['return_code'] == 'SUCCESS' ? $dc =1 : $dc=0;
        jsonSend($dc,array_key_exists('return_msg',$result) ? $result['return_msg'] : $result['err_msg'],$reDa);
    }

    /**
     *
     * 获取支付结果通知数据  微信支付
     * return array
     */
    public function getNotifyData(){
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $data = array();
        if( empty($xml) ){
            return false;
        }
        $data = $this->xml_to_data( $xml );
        if( !empty($data['return_code']) ){
            if( $data['return_code'] == 'FAIL' ){
                return false;
            }
        }
//        var_dump($data);
        return $data;
    }


    /**
     * 微信支付提交
     * 以post方式提交xml到对应的接口url
     * @param string $xml 需要post的xml数据
     * @param string $url url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second url执行超时时间，默认30s
     * @throws WxPayException
     */
    private function postXmlCurl($xml, $url, $useCert = false, $second = 30){
        $ch = curl_init();
//设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
//设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($useCert == true){
//设置证书
//使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
//curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
//curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
        }
//post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
//运行curl
        $data = curl_exec($ch);
//返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }

    /**
     * 生成签名
     * @return 签名
     */
    public function MakeSign( $params ){
//签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);
//签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->key;
//签名步骤三：MD5加密
        $string = md5($string);
//签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 接收通知成功后应答输出XML数据
     * @param string $xml
     */
    public function replyNotify(){
        $data['return_code'] = 'SUCCESS';
        $data['return_msg'] = 'OK';
        $xml = $this->data_to_xml( $data );
        echo $xml;
        die();
    }

    /**
     * 生成APP端支付参数
     * @param $prepayid 预支付id
     */
    public function getAppPayParams( $prepayid ){
        $data['appid'] = $this->appid;
        $data['partnerid'] = $this->mch_id;
        $data['prepayid'] = $prepayid;
        $data['package'] = 'Sign=WXPay';
        $data['noncestr'] = $this->genRandomString();
        $data['timestamp'] = time();
        $data['sign'] = $this->MakeSign( $data );
        return $data;
    }

    /**
     * 将参数拼接为url: key=value&key=value
     * @param $params
     * @return string
     */
    public function ToUrlParams( $params ){
        $string = '';
        if( !empty($params) ){
            $array = array();
            foreach( $params as $key => $value ){
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }

    /**
     * 组装成XML并输出xml字符
     * @param $params 参数名称
     * return string 返回组装的xml
     **/
    private function data_to_xml( $params ){
        if(!is_array($params)|| count($params) <= 0)
        {
            return false;
        }
//        var_dump($params);die;
        $xml = "<xml>";
        foreach ($params as $key=>$val)
        {
//            if (is_numeric($val)){
//                $xml.="<".$key.">".$val."</".$key.">";
//            }else{
//                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
//                  $xml .= '<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
                  $xml .= '<'.$key.'>'.$val.'</'.$key.'>';
//                var_dump($xml);
//            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function xml_to_data($xml){
        if(!$xml){
            return false;
        }
    //将XML转为array
    //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    /**
     * 产生一个指定长度的随机字符串,并返回给用户
     * @param type $len 产生字符串的长度
     * @return string 随机字符串
     */
    private function genRandomString($len = 32) {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
// 将数组打乱
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    /**
     * 微信错误代码
     * @param $code 服务器输出的错误代码
     * return string
     */
    private function wxErrorCode( $code ){
        $errList = array(
            'NOAUTH' => '商户未开通此接口权限',
            'NOTENOUGH' => '用户帐号余额不足',
            'ORDERNOTEXIST' => '订单号不存在',
            'ORDERPAID' => '商户订单已支付，无需重复操作',
            'ORDERCLOSED' => '当前订单已关闭，无法支付',
            'SYSTEMERROR' => '系统错误!系统超时',
            'APPID_NOT_EXIST' => '参数中缺少APPID',
            'MCHID_NOT_EXIST' => '参数中缺少MCHID',
            'APPID_MCHID_NOT_MATCH' => 'appid和mch_id不匹配',
            'LACK_PARAMS' => '缺少必要的请求参数',
            'OUT_TRADE_NO_USED' => '同一笔交易不能多次提交',
            'SIGNERROR' => '参数签名结果不正确',
            'XML_FORMAT_ERROR' => 'XML格式错误',
            'REQUIRE_POST_METHOD' => '请使用post方法 ',
            'POST_DATA_EMPTY' => 'post数据不能为空',
            'NOT_UTF8' => '未使用指定编码格式',
        );
        if( array_key_exists( $code , $errList ) ){
            return $errList[$code];
        }else{
            return $code;
        }
    }

    /**
     * 处理微信的回调结果
    **/
    public function getWxCallBack(){
        $input = file_get_contents("php://input");//接收回调数据
//        var_dump($input);die;
        $xml = $this->xml_to_data($input);
        $money = $xml['total_fee'];//支付金额
        $return_code = $xml['return_code'];//支付的结果代码
        $out_trade_no = $xml['out_trade_no'];
        $transaction_id = $xml['transaction_id'];
        if($return_code){
            Db::name('pay_callback')->insert([
                'callbackinfo'=>json_encode($xml),
                'types'=>0,
                'createtime'=>getFormatTime()
            ]);
            $id =  Db::name('pay_callback')->getLastInsID();
            $info =  Db::name('rewards')->where(['out_trade_no'=>$out_trade_no])->field('uid,coupon_id,order_number')->find();
            if($info){
                Db::name('rewards')->where(['out_trade_no'=>$out_trade_no])->update([
                    'trade_no'=>$transaction_id,
                    'total_amount'=>$money,
                    'pay_status'=>1,
                    'trade_status'=>$return_code,
                    'updatetime'=>getFormatTime(),
                    'callback_id'=>$id
                ]);
                Db::name('order_list')->where(['order_number'=>$info['order_number']])->update(['paystatus'=>1,'step'=>3,'layerIssue'=>3]);
                if($info['coupon_id'] >0){
                    Db::name('reduced')->where(['id'=>$info['coupon_id']])->update(['is_use'=>1,'updatetime'=>getFormatTime()]);
                }
            }
            exit('success');
        }else{
            exit('fail');
        }
    }

    /**
     * 处理微信退款
     * @param $order 订单号
     **/
    public function wxrefundapi($order){
//        $order = Request::instance()->param('order_number');
       $trade_no = Db::name('rewards')->where(['order_number'=>$order,'pay_status'=>1])->find();
        $money = floatval($trade_no['total_amount']);
        //通过微信api进行退款流程
        $parma = array(
            'appid'=> config::get('wxConfig.appid'),
            'mch_id'=> config::get('wxConfig.mch_id'),
            'nonce_str'=> $this->genRandomString(),
            'transaction_id'=> $trade_no['trade_no'],//微信生成的订单号，在支付通知中有返回
            'out_refund_no'=>  'SH' . time() . 'S' . mt_rand(),//商户退款单号
            'total_fee'=> $money,//订单金额 订单总金额，单位为分，只能为整数
            'refund_fee'=> $money,//退款金额
        );
        $parma['sign'] = $this->MakeSign($parma);
        $xmldata = $this->data_to_xml($parma);
        $xmlresult = $this->postXmlSSLCurl($xmldata,'https://api.mch.weixin.qq.com/secapi/pay/refund');
        $result = $this->xml_to_data($xmlresult);
        if($result['return_code'] == 'SUCCESS'){
            Db::name('order_list')->where(['order_number'=>$order])->update(['is_refund_money'=>1,'is_refund'=>1]);
            Db::name('rewards')->where(['order_number'=>$order,'trade_no'=>$trade_no['trade_no']])->update([
                'pay_status'=>3,
                'gmt_refund'=>getFormatTime(),
                'refund_id'=>$result['refund_id']
            ]);
//            jsonSend(1,'退款成功');
        }
        return $result;
    }
    //需要使用证书的请求
    private function postXmlSSLCurl($xml,$url,$second=30)
    {

        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, $this->SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, $this->SSLKEY_PATH);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            curl_close($ch);
            return false;
        }
    }

}