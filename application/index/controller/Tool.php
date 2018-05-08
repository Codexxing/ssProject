<?php
/**
 * Created by PhpStorm.
 * User: liyang
 * Date: 2018/1/12
 * Time: 14:02
 */

namespace app\index\controller;

use app\common\controller\HomeBase;
use think\config;
use think\exception;
use think\Request;
use think\File;
use think\Db;
use think\Session;
use think\ucpaas\Ucpaas;
use think\zmopclient\ZmopClient;
use app\index\controller\Alipay as Alipay;
//use think\zmopclient\request\ZhimaCustomerCertificationInitializeRequest;

class Tool extends HomeBase{
    //数据编码格式
   public $charset = "UTF-8";
   public $appId = '300002222';//应用appId;
    public $gatewayUrl = 'https://zmopenapi.zmxy.com.cn/openapi.do';
    public $privateKeyFile = './rsa_private_key.pem';//私钥文件路径，绝对路径或者相对路径
    public $zmPublicKeyFile = './rsa_public_key.pem';//公钥文件路径，绝对路径或者相对路径
    public $zhiMaPublicKeyFilePath ='./rsa_zmgy_public.pem';//芝麻公钥文件路径，绝对路径或者相对路径
    /*
    *发送验证码  测试成功的  暂停  以后测试用
    */
    public function sendSmsw($mobile='')
    {
        if(empty($mobile)){
            $request = Request::instance();
            if(! $request->isPost() ){
                jsonSend(0,'请求类型错误');exit;
            }
            $phone=$request->param('mobile');
            $templateId = "266796";
        }else{
            $phone=$mobile;
            $templateId = "266797";
        }
        $options['accountsid']='86e769613c7d0b3b552fb1764ba78666';
        $options['token']='31e343b1b1df94a26d7976b9ba309883';
        $ucpass = new Ucpaas($options);
        $ucpass->getDevinfo('xml');
        $appId = "438943b2ad73453d849f3bf921728a2e";
        $to = $phone;

        $param=mt_rand(100000, 999999);
        $response = $ucpass->templateSMS($appId,$to,$templateId,$param);
        $resp =  json_decode($response,true);
        switch($resp['resp']['respCode']){
            case 000000:
               $p = Db::table('os_user_verify')->where(['mobile'=>$phone])->value('mobile');
               if($p){
                   Db::name('user_verify')->where(['mobile'=>$phone])->update(['phonecode'=>$param,'codetime'=>time()]);
               }else{
                   Db::name('user_verify')->insert(['mobile'=>$phone,'phonecode'=>$param,'codetime'=>time()]);
               }

                jsonSend(1,'验证码发送成功');exit;
                break;
            case 100008:
                jsonSend(0,'手机号码不能为空');exit;
                break;
            case 100009:
                jsonSend(0,'手机号为受保护的号码');exit;
                break;
            case 100001:
                jsonSend(0,'账户余额/套餐包余额不足');exit;
                break;
            case 100015:
                jsonSend(0,'号码不合法');exit;
                break;
            case 102103:
                jsonSend(0,'应用未上线');exit;
                break;
            case 105150:
                jsonSend(0,'短信发送频率过快');exit;
                break;
            case 105166:
                jsonSend(0,'请求频率过快');exit;
                break;
            case 105153:
                jsonSend(0,'手机号码格式错误');exit;
                break;
            case 300003:
                jsonSend(0,'该手机号为空号');exit;
                break;
            case 300005:
                jsonSend(0,'发送太频繁');exit;
                break;
            default:
                jsonSend(0,'发送失败');exit;
                break;
        }
    }
    /*
    *发送验证码  测试成功的
     * @param $data['order,mobile,content']
    */
    public function sendSms($data='')
    {
        $request = Request::instance();

        if($request->isPost() && empty($data)){
            $phone=$request->param('mobile');
            $templateId = "266796";
            $param=mt_rand(100000, 999999);
        }else{
            $phone=$data['mobile'];
            $templateId = "266797";
            $order=$data['order']; $content=$data['content'];
            $param = "$order,$content";
        }
        $options['accountsid']='86e769613c7d0b3b552fb1764ba78666';
        $options['token']='31e343b1b1df94a26d7976b9ba309883';
        $ucpass = new Ucpaas($options);
        $ucpass->getDevinfo('xml');
        $appId = "438943b2ad73453d849f3bf921728a2e";
        $to = $phone;
        $response = $ucpass->templateSMS($appId,$to,$templateId,$param);
        $resp =  json_decode($response,true);
        if($request->isPost()  && empty($data)) {
            switch ($resp['resp']['respCode']) {
                case 000000:
                    $p = Db::table('os_user_verify')->where(['mobile' => $phone])->value('mobile');
                    if ($p) {
                        Db::name('user_verify')->where(['mobile' => $phone])->update(['phonecode' => $param, 'codetime' => time()]);
                    } else {
                        Db::name('user_verify')->insert(['mobile' => $phone, 'phonecode' => $param, 'codetime' => time()]);
                    }

                    jsonSend(1, '验证码发送成功');
                    exit;
                    break;
                case 100008:
                    jsonSend(0, '手机号码不能为空');
                    exit;
                    break;
                case 100009:
                    jsonSend(0, '手机号为受保护的号码');
                    exit;
                    break;
                case 100001:
                    jsonSend(0, '账户余额/套餐包余额不足');
                    exit;
                    break;
                case 100015:
                    jsonSend(0, '号码不合法');
                    exit;
                    break;
                case 102103:
                    jsonSend(0, '应用未上线');
                    exit;
                    break;
                case 105150:
                    jsonSend(0, '短信发送频率过快');
                    exit;
                    break;
                case 105166:
                    jsonSend(0, '请求频率过快');
                    exit;
                    break;
                case 105153:
                    jsonSend(0, '手机号码格式错误');
                    exit;
                    break;
                case 300003:
                    jsonSend(0, '该手机号为空号');
                    exit;
                    break;
                case 300005:
                    jsonSend(0, '发送太频繁');
                    exit;
                    break;
                default:
                    jsonSend(0, '发送失败');
                    exit;
                    break;
            }
        }else{return true;}
    }

    /*
     * 后台正常文件和图片上传
     * */
    public function fileUpload(){
		$u = $_SERVER['HTTP_REFERER'];
		$arrurl = explode('?',$u);
		$order = substr($arrurl[1] , 6 );
        $is_issue = Db::name('order_list')->where(['order_number'=>$order])->field('is_issue,uid')->find();
        if($is_issue['is_issue']==2){jsonSend(0,'用户正在确认律师函，请稍后...');exit();}
        $data['times'] =  Db::table('os_layer_file')->where(['order_number'=>$order])->count();
        (empty($data['times']) || is_null($data['times'])) ? $times = 0 : $times =  $data['times'] ;
		 if($data['times'] >= 3){jsonSend(0,'上传修改次数已经达到3次');exit();}
        $file = request()->file('file');
        $size = $file->getSize();//上传的大小
        if($size > config::get('max_upload')){
            jsonSend(0,'请上传1M以内的文件');exit;
        }
        $name =  pathinfo($file->getInfo('name'));//上传文件的类型
        if(!in_array($name['extension'],config::get('extensionsName'))){
            jsonSend(0,'请上传pdf格式文件');exit;
        }
        $error = $_FILES['file']['error'];// 如果$_FILES['file']['error']>0,表示文件上传失败
        if($error){
            jsonSend(0,'文件上传失败');exit;
        }
        //上传的时候的原文件名
        $filename = $file -> getInfo()['name'];
        // $dir = config('upload_path');// 自定义文件上传路径
        $dir = ROOT_PATH . 'public' . DS . 'layerfile/';// 自定义文件上传路径
        if (!is_dir($dir)) {
            mkdir($dir,0777,true);
        }
        $info = $file->move($dir);// 将文件上传指定目录
        //获取文件的全路径
        $data['attrurl'] = str_replace('\\', '/', $info->getSaveName());//GetPathName返回文件路径(盘符+路径+文件名)
		
		$fileUrl = './layerfile/'.$data['attrurl'];
		$arr=[
			'login_id'=>Session::get('admin_id'),
			'files'=>$fileUrl,
			'filename'=>$filename,
			'times'=> ($times == 0 ? 1 : intval($times)+1),
			'order_number'=>$order,
			'createtime'=>date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
			'updatetime'=>date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
		];
			Db::name('layer_file')->insert($arr);
            $layerTime =  Db::table('os_layer_file')->where(['order_number'=>$order])->count();

            Db::name('order_list')->where(['order_number'=>$order])->update(['layerletter_have'=>1,'is_issue'=>2]);//设置成已出函并且设置成等待用户确认
			$data['filename']=$filename;
			$data['times']=$times;
			$data['url']=$arr['files'];
            $userPhone = getOneUserVal(['id'=>$is_issue['uid']],'mobile');
            $this->sendSms(['order'=>$order,'mobile'=>$userPhone,'content'=>'律师已出函']);
			jsonSend(1,'上传成功',$data);
		}

    /*
    * base64图片处理上传
    * */
    public function basePicUpload($img){
        $base64_img = trim($_POST['img']);
        $up_dir = './upload/';//存放在当前目录的upload文件夹下

        if(!file_exists($up_dir)){
            mkdir($up_dir,0777);
        }

        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file = $up_dir.date('YmdHis_').'.'.$type;
                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                    $img_path = str_replace('../../..', '', $new_file);
                    echo '图片上传成功</br>![](' .$img_path. ')';
                }else{
                    echo '图片上传失败</br>';
                }
            }else{
                //文件类型错误
                echo '图片上传类型错误';
            }

        }else{
            //文件错误
            echo '文件错误';
        }

    }

    /**
     * 查询用户否有注册和是否有订单
    **/
    public function userIsRegister(){
        if(Request::instance()->isPost()){
            $phone = Request::instance()->param('mobile');
            $users = getUserVal(['mobile'=>$phone],'mobile,id');
            if($users['mobile']){
//                $num =searchOrderNum($users['id']);//订单数量
//                $num > 0 ? $isorder=1 :  $isorder=0;
                jsonSend(0,'用户已注册',['userId'=>$users['id']]);
//                jsonSend(0,'用户已注册',['num'=>$num,'order'=>$isorder,'uid'=>$users['id']]);
            }else{
                jsonSend(1,'用户未注册');exit;
            }

        }else{
            jsonSend(0,'请求类型错误');
        }
    }

    /**
     * 验证验证码是否正确和过期
     **/
    public function phoneCodeValidate(){
        if(Request::instance()->isPost()){
            $phone = Request::instance()->param('mobile');
            $code = Request::instance()->param('code');
            $res = validateUser('code',$phone,$code);
            switch($res){
                case 1:
                    jsonSend(0,'验证码不正确');exit;
                    break;
                case 2:
                    jsonSend(0,'验证码已过期');exit;
                    break;
            }
            jsonSend(1,'验证码正确');
        }else{
            jsonSend(0,'请求类型错误');
        }
    }

    /**
     * 查询openid是否绑定了手机号和注册
    **/
    public function searchOpenidIsHave(){
        if(Request::instance()->isPost()){
            $openid = Request::instance()->param('openid');
            $type = Request::instance()->param('type');
            $type == 0 ? $field='wx_openid' : $field='qq_openid' ;
            $isphone =getUserVal([$field=>$openid],'id,mobile');
            if($isphone['id']) {
                $token = setToken();
                $arr=[
                    'tokentime'=>time(),
                    'updatetime'=>getFormatTime(),
                    'last_login_time'=>getFormatTime(),
                    'usertoken'=>$token,
                ];
                Db::name('user')->where([$field=>$openid])->update($arr);
                jsonSend(1, '已绑定手机号', ['userId' => $isphone['id'], 'mobile' => $isphone['mobile'], 'token' => $token]);
            } else {
                jsonSend(0, '没有绑定手机号');
            }
        }else{
            jsonSend(0,'请求类型错误');
        }
    }

    /**
     * 查询用户的信息
     **/
    public function getUserInfo(){
        if(Request::instance()->isPost()){
            Db::startTrans();
            try{
                $uid = Request::instance()->param('uid');
                $token = Request::instance()->header('token');
                $userss = getUserVal(['id'=>$uid],'usertoken,mobile');
                $rd = validateUser('token',$userss['mobile'],$token);
                if($rd>0){
                    jsonSend(3,'验证信息已失效');exit;
                }
                $users = getUserVal(['id'=>$uid],'id,img,mobile,status,real_auth,is_agree,usertype,company_id,userauth');
                if($users){
                    if($users['real_auth'])
                        $users['real_auth']=json_decode($users['real_auth'],true);
                    else
                        $users['real_auth'] = ['name'=>'','idno'=>''];
                    if($users['company_id']){
                        //企业的信息展示
                        $company =Db::name('company')->where(['id'=>$users['company_id']])->field('uid,username,com_name,com_cod,com_mail,mobile,is_verify_company,is_verify_pay,is_bind,bindtime,verifytime,cardno,subbranch,bank,city,provice')->find();
                    }else{
                        $company = ['uid'=>$uid,'username'=>'','com_name'=>'','com_cod'=>'','com_mail'=>'','mobile'=>'','is_verify_company'=>0,'is_verify_pay'=>0,'is_bind'=>0,'bindtime'=>'','verifytime'=>'','cardno'=>'','subbranch'=>'','bank'=>'','city'=>'','provice'=>''];
                    }
                        $reduced = Db::name('reduced')->where(['id'=>$uid,'is_use'=>0])->field('id,money,types,is_use')->select();

                        $num =searchOrderNum($users['id']);//订单数量
                        $num > 0 ? $isorder=1 :  $isorder=0;
                        jsonSend(1,'获取成功',['num'=>$num,'order'=>$isorder,'user'=>$users,'reduced'=>$reduced,'company'=>$company]);
                }else{
                    jsonSend(0,'该用户不存在');
                }
            }catch (\Exception $e) {
                jsonSend(0,'哎呀，数据出错啦');
            }

        }else{
            jsonSend(0,'请求类型错误');
        }
    }

    /**
     * 同意接口请求
     **/
    public function agreeProtocol(){
        if(Request::instance()->isPost()){
            $param = Request::instance()->param();
            $token = Request::instance()->header('token');
            $phone = getOneUserVal(['id'=>$param['uid']],'mobile');
            $v = validateUser('token',$phone,$token);
            if($v>0){
                jsonSend(3,'验证信息已失效');exit;
            }
            Db::table('os_user')->where(['id'=>$param['uid']])->update(['is_agree'=>1,'updatetime'=>getFormatTime()]);
            jsonSend(1,'操作成功',['userId'=>$param['uid']]);
        }else{
            $uid = Request::instance()->param('uid');
            jsonSend(0,'请求类型错误',['userId'=>$uid]);
        }
    }

    /**
     * 获取律师发送的律师函,授权书以及证明文件(需求+补充需求+律师函)
     **/
    public function getLayerLetter(){
        if(Request::instance()->isPost()){
            $param = Request::instance()->param();//order_number uid token
            $token = Request::instance()->header('token');
            $phone = getOneUserVal(['id'=>$param['uid']],'mobile');
            $v = validateUser('token',$phone,$token);
            if($v>0){
                jsonSend(3,'验证信息已失效');exit;
            }
            //var_dump($impower_img);
            $file = Db::name('layer_file')->where(['order_number'=>$param['order_number']])->field('files')->order('times desc')->find();
           $lay_img = pdf2Img($file['files'],'lv');
            jsonSend(1,'获取成功',['file'=>$lay_img]);
        }else{
            $param=Request::instance()->param();
            jsonSend(0,'请求类型错误',['userId'=>$param['uid'],'order_number'=>$param['order_number']]);
        }
    }

    /**
     * 获取律师函  授权代理书   授权书
     **/
    public function getFiles(){
        if(Request::instance()->isPost()){
            $param = Request::instance()->param();//order_number uid token
            $token = Request::instance()->header('token');
            $phone = getUserVal(['id'=>$param['uid']],'mobile,real_auth,usertype,company_id');
            $v = validateUser('token',$phone['mobile'],$token);
            if($v>0){
                jsonSend(3,'验证信息已失效');exit;
            }
            if($phone['usertype']==1){
                $name = Db::table('os_company')->where(['id'=>$phone['company_id']])->value('com_name');
            }else{
                $username = json_decode($phone['real_auth'],true);
                $name = $username['name'];
            }
            $signUrls = Db::name('order_list')->where(['order_number'=>$param['order_number']])->field('shouquanUrl,solid_url_before')->find();//查看是否存在签署之后的授权书
            $signUrl = $signUrls['shouquanUrl'];
            $layLetter_img='';
            //生成授权书并取得授权书的路径  存在签署之后就用签署的 没有就直接是生成的
            $accept_name =Db::table('os_address')->where(['order_number'=>$param['order_number']])->value('accept_name');
            $signUrl ? $impower=$signUrl :  $impower = createImpowerPdf(0,['name'=>$name,'accept_name'=>$accept_name]);
            //授权书PDF转为图片
            $impower_img= pdf2Img($impower,'impower');//授权书图片路径  数组形式
            $file = Db::name('layer_file')->where(['order_number'=>$param['order_number']])->field('files')->order('times desc')->find();
            if($file['files']){
                $layLetter_img= pdf2Img($file['files'],'layLetter');//律师函图片路径  数组形式
//                var_dump($layLetter_img);die;
            }
            //查找需求描述与需求的图片
//            $conten = Db::table('os_order_list')
//                ->alias('list')
//                ->join('os_letters_file file','list.order_number = file.order_number','LEFT')
//                ->field('list.content,list.replenish_content,file.*')
//                ->where(['list.order_number'=>$param['order_number']])
//                ->select();
//            $contArr=[];
//            foreach($conten as $k=>$v){
//                $contArr['content']=$v['content'];
//                if($v['status']==0){ $contArr['content_file']=$v['file'];}//第一次需求
//                if($v['replenish_content']){
//                    $contArr['replenish_content']=$v['replenish_content'];
//                    if($v['status']==1){ $contArr['rep_file']=$v['file'];}//补充的
//                }
//            }
//           $contPdf = pdfAddImg($contArr);
            $conten =  Db::table('os_order_list')
                ->alias('list')
                ->join('os_letters_file file','list.order_number = file.order_number','LEFT')
                ->field('list.content,list.replenish_content,file.*')
                ->where(['list.order_number'=>$param['order_number'],'file.types'=>0])
                ->find();
            $conten['two_need']=Db::name('replenish')->where(['order_number'=>$param['order_number']])->select();
            $contPdf = pdfAddImg($conten);
            $zhengm = pdf2Img($contPdf,'zhengming');
            $resArr = ['impower_img'=>$impower_img,'layLetter_img'=>$layLetter_img,'zhengming'=>$zhengm];
            $imgarr = [];
            foreach($resArr as $k=>$o){
                if(is_array($o)){
                    foreach($o as $o1){
                        array_push($imgarr,$o1);
                    }
                }
            }
//            var_dump($imgarr);
            //把图片固化成一个pdf文件
            $solidArrUrl = morePicToPdf($imgarr);//固化pdf文件
//            var_dump($solidArrUrl);die;
            $signUrl ? $fie = 'solid_url_after' : $fie = 'solid_url_before';
            Db::table('os_order_list')->where('order_number',$param['order_number'])->setField($fie, $solidArrUrl);
            $signUrl ? $resArr['signUrl'] = $signUrl : $resArr['signUrl']='';
            $signUrls['solid_url_before'] ? $resArr['solid_url_before'] = $signUrls['solid_url_before'] : $resArr['solid_url_before']='';
            $resArr['solidUrl'] = $solidArrUrl;
            jsonSend(1,'获取成功',$resArr);
        }else{
            $param=Request::instance()->param();
            jsonSend(0,'请求类型错误',['userId'=>$param['uid'],'order_number'=>$param['order_number']]);
        }
    }

    /**
     * 取消订单接口
     **/
    public function cancleOrder(){
        $param =Request::instance()->param();//uid  order_number
        $token = Request::instance()->header('token');
        $phone = getOneUserVal(['id'=>$param['uid']],'mobile');
        $v = validateUser('token',$phone,$token);
        if($v>0){
            jsonSend(3,'验证信息已失效');exit;
        }
        if(Request::instance()->isPost()){
//            $cpmplete = getOrderCancle($param['order_number']);
           $signServiceId = Db::name('order_list')->where(['order_number'=>$param['order_number']])->field('signServiceId,is_complete,paystatus')->find();
            if($signServiceId['signServiceId']){ jsonSend(0,'订单已签署不可取消');exit;}
            if($signServiceId['is_complete'] == 2){
                jsonSend(0,'该订单已经被取消,不能在取消');exit;
            }else if($signServiceId['is_complete'] == 1){
                jsonSend(0,'该订单已完成,不能在取消');exit;
            }
               $r = Db::name('order_list')->where(['order_number' => $param['order_number'],'uid'=>$param['uid']])->update( [
                    'is_complete' =>2,
                    'cancletime' => getFormatTime()
                ]);
            if($signServiceId['paystatus'] ==1){
                $pay = new Alipay();
                $rewards =Db::name('rewards')->where(['order_number'=> $param['order_number'],'pay_status'=>1])->find();
                if($rewards) {
                    if ($rewards['client_type'] == 1) {//支付宝
                        $pay->refundMoneyBack1( $param['order_number']);
                    } else {//微信
                        $pay->wxrefundapi( $param['order_number']);
                    }
                }
            }
            if($r){jsonSend(1,'订单取消成功',['userId'=>$param['uid']]);}else{jsonSend(1,'订单取消失败',['userId'=>$param['uid']]);}
        }else{
            jsonSend(0,'请求类型错误',['userId'=>$param['uid'],'order_number'=>$param['order_number']]);
        }
    }

    /**
     * 获得优惠券的列表
     * 参数  uid token
     * 可以查找已使用（is_use）和已过期的（is_due）
    **/
    public function getCouponList(){
        if(Request::instance()->isPost()){
            $param = Request::instance()->param();
            $token = Request::instance()->header('token');
            $phone = getUserVal(['id'=>$param['uid']],'mobile,usertype,company_id');
            $v = validateUser('token',$phone['mobile'],$token);
            if($v>0){            jsonSend(3,'验证信息已失效');exit;        }
            $name='';
            if($phone['company_id'] && $phone['usertype']==1 ){//正式认证为企业了
                $codes = Db::table('os_company')->where(['id'=>$phone['company_id']])->value('com_cod');
                $name =Db::table('os_white_company')->where(['com_cod'=>$codes,'status'=>1])->value('com_name');//公司名称
            }
//            var_dump($name);
            if(empty($name)) {
                array_key_exists('is_use', $param) ? $use = 1 : $use = 0;//查询已使用的优惠券   默认查询未使用的
                array_key_exists('is_due', $param) ? $due = '< time' : $due = '> time';//查询已过期的优惠券  默认查询未过期的
                $res = Db::name('reduced')->where(['uid' => $param['uid'], 'is_use' => $use])->where('overtime', $due, getFormatTime())->select();
                foreach ($res as $k => $v) {
                    ($v['overtime'] < date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])) ? $res[$k]['is_due'] = 0 : $res[$k]['is_due'] = 1;//是否过期
                    $res[$k]['overtime'] = date('Y-m-d', strtotime($v['overtime']));
                    $res[$k]['createtime'] = date('Y-m-d', strtotime($v['createtime']));
                    $res[$k]['updatetime'] = date('Y-m-d', strtotime($v['updatetime']));
                }
            }else{
                $res = [];//白名单企业信息不展示优惠券
            }
            jsonSend(1,'获取成功',$res);
        }else{
            jsonSend(0,'请求类型错误');
        }
    }

    /**
     * 兑换优惠券
     * 参数  uid或者单号 token
     **/
    public function conversionCoupon(){
        if(Request::instance()->isPost()) {
            $param = Request::instance()->param();
            $token = Request::instance()->header('token');
            $phone = getOneUserVal(['id'=>$param['uid']],'mobile');
            $v = validateUser('token',$phone,$token);
            if($v>0){
                jsonSend(3,'验证信息已失效');exit;
            }
            $conversion = Db::name('reduced')->where(['reduce_number' => $param['order']])->find();
            if(count($conversion) == 0){
                jsonSend(0, '优惠券不存在');
                exit;
            }
            if ($conversion['overtime'] < date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])) {
                jsonSend(0, '该优惠券已经过期');
                exit;
            }
            if ($conversion['is_conversion'] == 1) {
                jsonSend(0, '该优惠券已经兑换');
                exit;
            }
            Db::name('reduced')->where(['reduce_number' => $param['order']])->update(['uid'=>$param['uid'],'is_conversion'=>1,'updatetime'=>getFormatTime()]);
            jsonSend(1,'兑换成功',['reduce_number'=>$param['order'],'userId'=>$param['uid'],'money'=>$conversion['money']]);
        }else{
            jsonSend(0, '请求类型错误');
        }
    }

    /**
     * 查询条款政策信息
    **/
    public function getPolicyClause(){
        if(Request::instance()->isPost()){
            $token = Request::instance()->header('token');
            $uid = Request::instance()->param('uid');
//            $phone = getOneUserVal(['id'=>$uid],'mobile');
//            $v = validateUser('token',$phone,$token);
//            if($v>0){
//                jsonSend(3,'验证信息已失效');exit;
//            }
            $cont = Db::name('policyclause')->field('content')->find();
            jsonSend(1,'获取成功',['userId'=>$uid,'content'=>$cont['content']]);
        }else{
            jsonSend(0,'请求类型错误');
        }
    }

    /**
     * 查快递查询接口
     **/
    public function expressSearch(){
        if(Request::instance()->isPost()) {
            $number = Request::instance()->param('number');
            $uid = Request::instance()->param('uid');
            $token = Request::instance()->header('token');
            $phone = getOneUserVal(['id'=>$uid],'mobile');
            $v = validateUser('token',$phone,$token);
//            if($v>0){
//                jsonSend(3,'验证信息已失效');exit;
//            }
            $res = searchExpressAli($number);
            $res->status == 0 ?  $data =  $res->result->list : $data ='';
            jsonSend($res->status, $res->msg,$data);
            exit;
        }
    }
    /**
     * 修改手机号
     * @param  uid  mobile
    **/
    public function changePhone(){
        if(Request::instance()->isPost()){
            $phone = Request::instance()->param('mobile');
            $uid = Request::instance()->param('uid');
            $is_exit =getOneUserVal(['mobile'=>$phone],'mobile');
            if($is_exit){jsonSend(0,'该手机号已经注册',['userId'=>$uid]);exit;}
            Db::name('user')->where(['id'=>$uid])->update(['mobile'=>$phone,'updatetime'=>getFormatTime()]);
            jsonSend(1,'修改成功',['mobile'=>$phone,'userId'=>$uid]);
        }else{
            jsonSend(0,'请求类型错误');
        }
    }
    /**
     * 忘记密码
     * @param mobile 手机号   password新的密码
     **/
    public function forgetPassword(){
        if(Request::instance()->isPost()){
            $phone = Request::instance()->param('mobile');
            $password = Request::instance()->param('password');
            $is_exit =getOneUserVal(['mobile'=>$phone],'mobile');
            if(!$is_exit){jsonSend(0,'该手机号没有注册');exit;}
            Db::name('user')->where(['mobile'=>$phone])->update(['password'=>md5($password . Config::get('salt')),'updatetime'=>getFormatTime()]);
            jsonSend(1,'修改成功',['mobile'=>$phone]);
        }else{
            jsonSend(0,'请求类型错误');
        }
    }

    /**
     * 修改密码
     * @param old_password 旧密码   new_password新的密码
     * @param token验证  uid用户id
     **/
    public function changePassword(){
        if(Request::instance()->isPost()){
            $param = Request::instance()->param();
            $token = Request::instance()->header('token');
            $user_info = getUserVal(['id' => $param['uid']], 'usertoken,mobile,password');
            $rd = validateUser('token', $user_info['mobile'], $token);
            if ($rd > 0) {
                jsonSend(3, '验证信息已失效');
                exit;
            }
            if($user_info['password'] != md5($param['old_password'] . Config::get('salt'))){
                jsonSend(0, '旧密码错误');exit;
            }
            Db::name('user')->where(['id'=>$param['uid']])->update(['password'=>md5($param['new_password'] . Config::get('salt')),'updatetime'=>getFormatTime()]);
            jsonSend(1,'修改成功');
        }else{
            jsonSend(0,'请求类型错误');
        }
    }


    /**
     * 用户上传头像
     * @param image base64图片   uid用户id  token用户tokem
   * */
    public function imageUploadT(){
        if(Request::instance()->isPost()) {
            $image = Request::instance()->param('image');
            $uid = Request::instance()->param('uid');
            $token = Request::instance()->header('token');
            $userss = getUserVal(['id' => $uid], 'usertoken,mobile');
            $rd = validateUser('token', $userss['mobile'], $token);
            if ($rd > 0) {
                jsonSend(3, '验证信息已失效');
                exit;
            }
            $fileUrl = base64Upload($image, 'T');
            count($fileUrl) == 1 ? $url = $fileUrl[0] : $url = implode(',', $fileUrl);
            Db::name('user')->where(['id' => $uid])->update(['img' => $url, 'updatetime' => getFormatTime()]);
            jsonSend(1, '上传成功', ['userId' => $uid,'img'=>$url]);
        }else{
            jsonSend('0', '请求类型错误');
        }
    }

    /**
     * 意见反馈
     **/
    public function optionFeedBack(){
        if(Request::instance()->isPost()){
            $mail = Request::instance()->param('mail');
            $uid = Request::instance()->param('uid');
            $content = Request::instance()->param('content');
           // $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
           // if ( !preg_match( $pattern, $mail ) ){
               // jsonSend(0,'邮箱格式错误');exit;
            // }
            $data = [
                'uid'=>$uid,
                'content'=>htmlspecialchars($content),
                'email'=>$mail,
                'types'=>0,
                'createtime'=>getFormatTime(),
                'updatetime'=>getFormatTime(),
            ];
            Db::name('feedback')->insert($data);
            jsonSend(1,'提交成功');
        }else{
            jsonSend(0,'请求类型错误');
        }
    }
    //e签宝的接口使用
    public function eSignApi(){
//        if(Request::instance()->isPost()){
            $uid = Request::instance()->param('uid');//用户id
            $number = Request::instance()->param('order_number');//订单号
            $base = Request::instance()->param('imgbase');//签名的base64图片的
            //首先查询是否已经进行了e签宝账户添加
            $info = getUserVal(['id'=>$uid],'real_auth,accountid,usertype,company_id');
            $info['base'] = $base;

                if($info['usertype'] == 0) {
                    $type = 'addPerson';//企业认证还是个人用户认证
                    $cert = json_decode($info['real_auth'], true);//需要格式化才可以  转化为数组的k=>value形式
                   $name = $info['cert_name'] = $cert['name'];
                    $info['cert_idno'] = $cert['idno'];

              }else if($info['usertype']==1){
                    $type = 'addOrganize';
                    $company = Db::name('company')->where(['id'=>$info['company_id']])->field('mobile,com_name,com_cod')->find();
                    $info['mobile'] = $company['mobile'];$info['com_name'] = $company['com_name'];$info['com_cod'] = $company['com_cod'];
                    $name=$info['com_name'];
                }
            if(!$info['accountid']) {
                $resonse = $this->curlExec($info, $type);//创建个人或者企业账户
                $rr = json_decode($resonse, true);

                if($rr['errCode'] != 0){jsonSend(0,$rr['msg']);exit;}
                Db::name('user')->where(['id' => $uid])->update(['accountid' => $rr['accountId'], 'updatetime' => getFormatTime()]);
                $info['accountid'] =  $rr['accountId'];
            }
//        array_key_exists('name',$info) ? $name = $info['']
            $accept_name =Db::table('os_address')->where(['order_number'=>$number])->value('accept_name');
                //创建印章并签署到pdf上
                $info['shouquan'] = createImpowerPdf(1,['name'=>$name,'accept_name'=>$accept_name]);//创建授权书
                $tr = $this->createSealSignPdf($info);
                $trd = json_decode($tr, true);
        var_dump($trd);die;
                if ($trd && $trd[0]['errCode'] == 0) {
                    Db::name('order_list')->where(['order_number' => $number])->update(['signServiceId' => $trd[0]['signServiceId'], 'updatetime' => getFormatTime(),'shouquanUrl'=>$trd[1]]);
                    jsonSend(1, '签署成功',['pdfurl'=>$trd[1],'shouquan'=> $info['shouquan']]);
                } else {
                    jsonSend(0, '签署失败');exit;
                }
                exit;

//        }else{
//            jsonSend(0,'请求类型错误');
//        }
    }

    //开始调用生成盖章并签署文件上
    private function createSealSignPdf($info){
        return $this->curlExec($info,'addTemplateSeal');
    }
    //执行curl数据
    private function curlExec($data,$function){
        $data['action'] = $function;
        $host = config::get('twoHost');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host.'/index.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resonse =curl_exec($ch);
        curl_close($ch);
        return $resonse;
    }

    /**
     * 芝麻信用的初始化以及认证
    **/
    public function zmxyInit(){
        $params=Request::instance()->param();
        $param['username'] = $params['username'];
        $param['cert_no'] = $params['cert_no'];
        $param['action'] = 'zmrz';
        $host = config::get('twoHost');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host.'/polop/index.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resonse =curl_exec($ch);
        $info =json_decode($resonse,true);
        curl_close($ch);
        if(!$resonse){
            jsonSend(0,'获取失败','');
        }else {
            jsonSend(1, '获取成功', $info);
        }
    }
    /**
     * 芝麻信用的结果查询
     **/
    public function zmxyResultSearch(){
        $biz_no=Request::instance()->param('biz_no');
        $uid =Request::instance()->param('uid');
        $idno =Request::instance()->param('idno');
        $name =Request::instance()->param('name');
        $param['biz_no'] = $biz_no;
        $param['action'] = 'search';
        $host = config::get('twoHost');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host.'/polop/index.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resonse =curl_exec($ch);
        $info =json_decode($resonse,true);
        curl_close($ch);
        $info['success'] ? $status =1 : $status=0;//接口是否通过，接口通就反1  不通就是0
        $info['passed'] == "true" ? $success =1 : $success=0;//认证是否成功
        if($status == 0){
            jsonSend(0,$info['error_message'],$info);
        }else if($status == 1){
            if($status==1 && $success==1){//接口通并且认证成功才算成功
                $res = $this->varifyUserFace(['uid'=>$uid,'idno'=>$idno,'name'=>$name,'istrue'=>1]);
                $mess ='';
            }else{
                $mess =$info['failed_reason'];
            }
            jsonSend(1,$mess,$info);
        }
    }
    /**
     * 芝麻认证认证阶段回调地址
     * 回调后面会加上加密加签后的params和sign参数，服务端解密验签可以拿到认证结果
    **/
    public function zmrzCallBack(){}
    /**
     * 芝麻信用的验签
    **/
    public function zmxyVerify(){
        $param = Request::instance()->param();
        $init = $this->zmxyInit($param);
//        $params = 'BFMqwAYz615BnJQIloDJw5h8mfLMTv%2FjvoitHU2PFu7E%2FdO1cTprm0jZ6N6V73BU9KIO5Lc43DrkyEJ9P7%2BDnjUfsFOfbIuV4rSL%2BMe8IEMHtGC3KR6lUn4PZ5qc3VDx5hgdc0D5sCy8v3KgYeEGuXNcNws7F2dL30ze45yps%2FkW1f%2BUbs%2BFcXMYpoZz1dfh7LF78NsjmD1d0D9doM9z8yydgPdZ%2F8kdszCKnLre0iuq%2Bv%2FBHHcDr0NyRvhJQotNJqm%2BA590wUfb%2BpcI168g81av5a9naQHech%2F1z5OF%2BjHADMw%2BSdR6jklASJTCPq0p8rHTLmH0QOnOm7G6ePrG9w%3D%3D';
//        $sign = 'YKbTxhXrEE8VmD7cdpD9FK6Wd00WwkgLn9N2zppfukIOMzQfL4WRsKcCJgHe3YFJRZB%2FVV%2BqGk7chQF5PAaVr1iJyocxGC4cp4UB7HhDnEf01OxGLsjdtqA735Tze3dJv4qzcssBj1edSx1DWECJhthecKaevUxcf2%2BLoe0cRQI%3D';
        $params = Request::instance()->param('params');
        $sign = Request::instance()->param('sign');
        $client = new ZmopClient ( $this->gatewayUrl, $this->appId, $this->charset,$this->privateKeyFile, $this->zhiMaPublicKeyFilePath);
        $result = $client->decryptAndVerifySign ($params, $sign);
        if($result[0] == 1){

        }else{
            jsonSend(0,'验签失败');
        }
    }

    /**
     * 验证用户是不是已经验证了
     * @param uid 用户id
     * @param idno 身份证号
     * @param name 姓名
     * @param istrue 是否验证成功  0 失败  1成功
    **/
    private function varifyUserFace($data){
//        $data = Request::instance()->param();
        $arr=[
            'userauth'=>$data['istrue'],
            'updatetime'=>getFormatTime(),
        ];
        if($data['istrue'] == 1){
            $auth = json_encode(['name'=>$data['name'],'idno'=>$data['idno']]);
             $arr['real_auth']=$auth;
             $arr['facetime']=getFormatTime();//认证时间
        }
        Db::name('user')->where(['id'=>$data['uid']])->update($arr);
        return true;
    }

    //只是测试数据
    public function testData(){

    }

    /**
     * 订单详情
     * @param uid用户id
     * @param order_number 订单编号
     * 电子邮件  快递单号，下单时间，发函方式，支付方式，总金额，优惠券，运费（免邮），实付款
    **/
    public function getOrderDetail(){
        if(Request::instance()->isPost()){
            $param = Request::instance()->param();
          $list=  Db::name('order_list')
                ->where(['order_number'=>$param['order_number']])
                ->field('email_complete,express_number,createtime')->find();
            $sendtype = Db::table('os_address')
                ->where(['order_number'=>$param['order_number']])
                ->value('accept_type');
            $money = Db::name('rewards')
                ->where(['order_number'=>$param['order_number'],'pay_status'=>1])
                ->field('total_amount,is_coupon,coupon_id,client_type')->find();
            if($money['is_coupon']==1){
                $youhuiMoney = Db::table('os_reduced')->where(['uid'=>$param['uid']])->value('money');
            }else{
                $youhuiMoney =0;
            }
            $arr=[
                'express_number'=>$list['express_number'],//快递单号
                'email'=>$list['express_number']==0 ? '未选择此发送方式' : '已发送到服务器',//邮件
                'createtime'=>$list['createtime'],//下单时间
                'order_number'=>$param['order_number'],//订单编号
                'send_type'=>$sendtype==0 ? '快递' : ($sendtype==1 ? '电子邮件' : '快递，电子邮件'),
                'pay_type'=>$money['client_type']==0 ? '微信' : '支付宝',//支付方式
                'ele_bill'=>[
                    'header'=>'电子普通发票',
                    'top_header'=>'个人',
                    'detail'=>'明细',
                ],//电子发票
                'money'=>[
                    'total'=>128.00,
                    'reduced'=>$youhuiMoney,
                    'freight'=>'免邮',
                    'real_pay'=>$money['total_amount'],
                ]

            ];
            jsonSend(1,'获取成功',$arr);
        }else{
            jsonSend(0,'请求类型错误');
        }
    }
    /**
     * 用户资料知否删除的接口查询
     * @param order_number
    **/
    public function userFileIsDel(){
        if(Request::instance()->isPost()){
            $order = Request::instance()->param('order_number');
            $del =Db::table('os_order_list')->where(['order_number'=>$order])->value('is_delete');
            jsonSend(1,'获取成功',$del);
        }else{
            jsonSend(0,'请求类型错误');
        }
    }

    /**
     * 获取律师函的价格
     * @param
     **/
    public function getPrice(){
        if(Request::instance()->isPost()){
            $money =Db::table('os_price')->where('id=1')->value('money');
            jsonSend(1,'获取成功',$money);
        }else{
            jsonSend(0,'请求类型错误');
        }
    }

    /**
     * 计算优惠券的金额和实付金额
     * @param id优惠券的id
    **/
    public function countMoney(){
        if(Request::instance()->isPost()){
            $coupMoney = Db::table('os_price')->where('id=1')->value('money');
            $id = Request::instance()->param('id');
            if($id != 0){
                $money = Db::table('os_reduced')->where(['id'=>$id])->value('money');//优惠券的钱
                $real_moneys =$coupMoney-$money;//实际付款
                $coupMoney =$coupMoney.'.00';
                $real_moneys <= 0? $real_money = 0.01 : $real_money=$real_moneys.'.00';
            }else{
                $coupMoney =$real_money=$coupMoney.'.00';
                $money='0.00';
            }
            jsonSend(1,'获取成功',['real_money'=>$real_money,'recud_money'=>$money,'all_money'=>$coupMoney]);
        }else{
            jsonSend(0,'请求类型错误');
        }
    }

}