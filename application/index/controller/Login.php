<?php
/**
 * Created by PhpStorm.
 * User: liyang
 * Date: 2018/1/12
 * Time: 10:56
 * file:登录类
 */

namespace app\index\controller;


use app\common\controller\HomeBase;
use app\index\model\User as UserModel;
use think\Db;
use think\Session;
use think\Request;
use think\Config;

class Login extends HomeBase{
    protected $userModel;
    protected function _initialize(){
        parent::_initialize();
        $this->userModel = new userModel();
    }
    //用户登录验证
    public function userLogin(){
        if(Request::instance()->isPost()){
            $data  = Request::instance()->param();
            $where['mobile'] = $data['mobile'];
            if(array_key_exists('password',$data)){
                $where['password'] = md5($data['password'] . Config::get('salt'));//密码登录
            }else{
                $code= $data['verify'];
            }
            $user = getUserVal($where,'id,mobile,status');
            if(array_key_exists('password',$data)){
                if (empty($user)) {jsonSend(0,'用户名或密码错误');exit;}
            }else{
                if (empty($user)) {
                    jsonSend(0, '手机号未注册'); exit;
                }else{
                    if($user['status']==0){ jsonSend(0,'你已被禁止登录');exit;}
                    $rd = validateUser('code',$data['mobile'],$code);
                    switch($rd){
                        case 1:
                            jsonSend(0,'验证码不正确');exit;
                            break;
                        case 2:
                            jsonSend(0,'验证码已过期');exit;
                            break;
                    }
                }
            }
            $token = setToken();
            Db::name('user')->where(['mobile'=>$data['mobile']])->update([
                'last_login_time'=>date('Y-m-d H:i:s',time()),
                'usertoken'=>$token,
                'tokentime'=>time()
            ]);
        jsonSend(1,'登陆成功',['mobile'=>$data['mobile'],'userId'=>$user['id'],'token'=>$token]);exit;
        }else{
            jsonSend(0,'请求错误');exit;
        }
    }

    /**
     * 用户注册
     * @param type代表注册类型  0 普通用户  1企业注册
     @param is_agree   是否同意协议 0不同意 1同意
     @param password   密码
     @param mobile   手机号
     @param invite_phone   邀请者的手机号 在分享页面注册的时候传入
     **/
    public function userRegister(){
        if(Request::instance()->isPost() ){
//            Db::startTrans();
//            try{
                $data = $this->request->param();
                $is_phone = getOneUserVal(['mobile'=>$data['mobile']],'mobile');
//                if($is_phone){ jsonSend(0,'该手机号已经注册');exit;}
                if($is_phone){echo json_encode(['code'=>0,'msg'=>'该手机号已经注册']);exit;}
                 //首先看是不是存在邀请id invite_id
                if(array_key_exists('invite_phone', $data)){
                    $data['password']=mt_rand();
                    $data['invite_id'] = getOneUserVal(['mobile'=>$data['invite_phone']],'id');
                }else{
                    $data['invite_id']=0;
                }
                    $time = date('Y-m-d H:i:s',time());
                    $token = setToken();
                    $arr=[
                        'mobile'=>$data['mobile'],
                        'username'=>mt_rand(),
                        'password'=>md5($data['password'] . Config::get('salt')),
                        'status'=>1,
                        'create_time'=>$time,
                        'last_login_time'=>$time,
                        'is_agree'=>$data['is_agree'],
                        'updatetime'=>$time,
                        'usertoken'=>$token,
                        'usertype'=>0,
                        'invite_id'=>$data['invite_id'],
                        'tokentime'=>time()
                    ];
                   $res =  $this->userModel->userInsert($arr);
                    if($res > 0){
                        if(array_key_exists('invite_phone', $data)){//首先对邀请者赠送优惠券
                             $data=[
                                'uid'=>$data['invite_id'],
                                'money'=>config::get('coupon.invite_reg'), 
                                 'types'=>4
                             ];
                             giveCoupon($data);
                        }
                         $data=[
                                'uid'=>$res,
                                'money'=>config::get('reduce_money'), 
                                 'types'=>1
                             ]; //然后对注册者赠送优惠券

                        $r =giveCoupon($data);
                        if($r){
                            jsonSend(1,'注册成功',['mobile'=>$arr['mobile'],'userId'=>$res,'token'=>$token]);exit;
                        }else{
                            jsonSend(0,'注册失败');exit;
                        }
                    }else{
                        jsonSend(0,'注册失败');exit;
                    }
//                } catch (\Exception $e) {
//                    // 回滚事务
//                    Db::rollback();
//                }
        }else{
            jsonSend(0,'请求类型错误');exit;
        }
    }
    /**
     * 把微信或者QQ的头像下载到本地返回本地路径
     * @param $url是头像路径  ，$saveName 保存的头像名称，$path 保存的路径
    **/
    private function changeLocalImg($url,$saveName,$path) {
//        $url = "http://wx.qlogo.cn/mmhead/Q3auHgzwzM6kqfcibzzVc8MDGBch53mIgJjWrbKSwkBnzcsWBOMOGlg/0";
//        $saveName = 'imgs.jpg';
//        $path = './imageT/';
        // 设置运行时间为无限制
        set_time_limit ( 0 );
        $url = trim ( $url );
        $curl = curl_init ();
        // 设置你需要抓取的URL
        curl_setopt ( $curl, CURLOPT_URL, $url );
        // 设置header
        curl_setopt ( $curl, CURLOPT_HEADER, 0 );
        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
        // 运行cURL，请求网页
        $file = curl_exec ( $curl );
        // 关闭URL请求
        curl_close ( $curl );
        // 将文件写入获得的数据
        $filename = $path . $saveName;
        $write = @fopen ( $filename, "w" );
        if ($write == false) {
            return false;
        }
        if (fwrite ( $write, $file ) == false) {
            return false;
        }
        if (fclose ( $write ) == false) {
            return false;
        }
    }

    /**
     * 微信QQ注册和登录接口
     * 需要传递参数：openid,mobile,nickname,imgurl, unionid, type  (1->QQ, 0->微信)
     * mobile 手机号为空说明已经绑定了手机号，直接登录，否则就是注册
     **/
    public function thirdAccountDeal(){
        if(Request::instance()->isPost()){
//            Db::startTrans();
//            try{
                $param = Request::instance()->param();
                $param['type'] == 0 ? $field='wx_openid' :  $field='qq_openid' ;
                $token = setToken();
                $arr=[
                    'tokentime'=>time(),
                    'updatetime'=>getFormatTime(),
                    'last_login_time'=>getFormatTime(),
                    'usertoken'=>$token,
                ];
                if(empty($param['mobile'])){
                    //直接登录
                    $info =getUserVal([$field=>$param['openid']],'id,status');
                    if($info['status']==0){jsonSend(0,'你已被禁止登陆');exit;}
                    Db::name('user')->where([$field=>$param['openid']])->update($arr);
                    $id = $info['id'];
                }else{
                    $rd = validateUser('code',$param['mobile'],$param['code']);
                    switch($rd){
                        case 1:
                            jsonSend(0,'验证码不正确');exit;
                            break;
                        case 2:
                            jsonSend(0,'验证码已过期');exit;
                            break;
                        case 3:
                            jsonSend(0,'请输入手机验证码');exit;
                            break;
                    }
                    $isOpenid =getUserVal(['mobile'=>$param['mobile']],'id,'.$field);
                    //手机号已经注册了
                    if($isOpenid) {
                        //说明手机号存在  然后在查看是不是存在openid  存在的话 如果一样就更新  不一样就报错
                        if($isOpenid[$field]){//存在openid
                            if ($isOpenid[$field] != $param['openid']) {//存在并且不相等的时候就报错
                                jsonSend(0, '该手机号已绑定另一个第三方账号'); exit;
                            }//存在了 相等的话就直接更新
                            Db::name('user')->where(['mobile'=>$param['mobile']])->update($arr);
                            $id = $isOpenid['id'];
                        }else{//不存在openid
                            $rand = uniqid();
                            $path = './imageT/';//随机数当作头像名称
                            $this->changeLocalImg($param['imgurl'], $rand . '.jpg', $path);//上传头像成本地
                            $arr['username'] = $param['nickname'];
                            if($param['type'] == 0){//微信
                                $arr['wx_name']  = time();
                                $arr['wx_openid'] = $param['openid'];
                                $arr['wx_img'] = $param['imgurl'];
                                $arr['wx_unid'] = $param['unionid'];
                            }else{//qq的
                                $arr['qq_name'] = $arr['username'] = time();
                                $arr['qq_openid'] = $param['openid'];
                                $arr['qq_img'] = $param['imgurl'];
                                $arr['qq_unid'] = $param['unionid'];
                            }
                            Db::name('user')->where(['mobile'=>$param['mobile']])->update($arr);
                            $id = $isOpenid['id'];
                        }

                    }else {//手机号不存在数据 即手机号没有注册过
                        $open=getUserVal([$field=>$param['openid']],'id,mobile');//在手机号没有注册的情况下，账号是否绑定了另一个手机号
                        if($open['mobile']) {//openid对应的手机号存在
                            if ($open['mobile'] != $param['mobile']) {
                                jsonSend(0, '该第三方账号已绑定另一个手机号');exit;
                            } else {
                                Db::name('user')->where([$field => $param['openid']])->update($arr);
                                $id = $isOpenid['id'];
                            }
                        }else {//openid对应的手机号不存在
                            $arr['username'] = time();
                            $rand = uniqid();
                            $path = './imageT/';//随机数当作头像名称
                            $this->changeLocalImg($param['imgurl'], $rand . '.jpg', $path);//上传头像成本地
                            if($param['type'] == 0){//微信
                                $arr['wx_name']  = time();
                                $arr['wx_openid'] = $param['openid'];
                                $arr['wx_img'] = $param['imgurl'];
                                $arr['wx_unid'] = $param['unionid'];
                            }else{//qq的
                                $arr['qq_name'] = $arr['username'] = time();
                                $arr['qq_openid'] = $param['openid'];
                                $arr['qq_img'] = $param['imgurl'];
                                $arr['qq_unid'] = $param['unionid'];
                            }
                            $arr['img'] = './imageT/' . $rand . '.jpg';
                            $arr['t_type'] = $param['type'];
                            $arr['status'] = 1;
                            $arr['userauth'] = $arr['usertype'] = 0;
                            $arr['is_agree'] = $param['is_agree'];
                            $arr['create_time'] = getFormatTime();
                            $arr['mobile'] = $param['mobile'];
                            $arr['password'] = uniqid();//随机生成一个密码
                            //注册
                            Db::name('user')->insert($arr);
                            $id = Db::name('user')->getLastInsID();
                            Db::name('reduced')->insert([
                                'uid'=>$id,
                                'money'=>config::get('reduce_money'),
                                'types'=>1,
                                'is_use'=>0,
                                'reduce_number'=>mt_rand(100000,999999),
                                'is_conversion'=>1,
                                'createtime'=>date('Y-m-d H:i:s',time()),
                                'updatetime'=>date('Y-m-d H:i:s',time()),
                                'overtime'=>date('Y-m-d H:i:s',strtotime("+1 year")),
                            ]);

//                            echo  Db::name('reduced')->getLastSql();
                        }
                    }
                }
                if($id){
                    jsonSend(1,'操作成功',['userId'=>$id,'mobile'=>$param['mobile'],'token'=>$token]);exit;
                }else{
                    jsonSend(0,$id);exit;
                }
//            }catch (\Exception $e) {
//                jsonSend(0,'哎呀，数据出错啦');
//            }
        }else{
            jsonSend(0,'请求类型错误');
        }
    }
    /**
     * 第三方验证企业的真实信息验证
     * @param uid  用户id
     * @param mobile  手机号
     * @param name 企业名称  必传
     * @param codeORG 组织机构代码  可选
     * @param codeUSC 社会统一信用代码（必选）  legalName 法人姓名（必选）   legalIdno 法人身份证号码（可选）
     *
     * @param cardno 企业对公银行账号
     * @param subbranch 企业银行账号开户行支行全称
     * @param bank 企业银行账号开户行名称
     * @param provice 企业银行账号开户行所在省份     city 企业银行账号开户行所在城市
     *
    **/
    public function companyValid(){

        if(Request::instance()->isPost()) {
            $param = Request::instance()->param();//获取的有name codeUSC  legalName

            $indol =Db::name('company')->where(['mobile' => $param['mobile'], 'com_cod' => $param['codeUSC'], 'username' => $param['legalName'], 'com_name' => $param['name']])->find();
            if(!$indol){
                Db::name('company')->insert([
                    'uid' => $param['uid'],
                    'username' => $param['legalName'],
                    'com_name' => $param['name'],
                    'com_cod' => $param['codeUSC'],
                    'com_mail' => $param['mail'],
                    'serviceId' =>'',
                    'com_times' => 0,
                    'money' => 0,
                    'mobile' => $param['mobile'],
                    'cardno' => $param['cardno'],
                    'subbranch' => $param['subbranch'],
                    'bank' => $param['bank'],
                    'provice' => $param['provice'],
                    'city' => $param['city'],
                    'is_bind' => 0,
                    'is_verify_company'=>0,
                    'is_verify_pay'=>0,
                    'createtime' => getFormatTime(),
                    'verifytime' => getFormatTime(),
                ]);
                $id =Db::name('user')->getLastInsID();
            }else {
                if ($indol['serviceId']) {jsonSend(0, '该企业已被认证信息真实');   exit;          }
                if ($indol['com_times'] >= 3) { jsonSend(0, '企业信息验证次数已达三次，请联系客服'); exit;            }
                $id=$indol['id'];
            }
//        $dataq = array('name' => '廊坊市安次区银河南路青年超市', 'codeORG' => '', 'codeUSC' => 'JY11310020005969', 'legalName' => '龚秀爱', 'legalIdno' => '');
            //将个人银行四要素信息转成JSON字符串
            $data = json_encode([
                'name' => $param['name'], 'codeORG' => '',
                'codeUSC' => $param['codeUSC'], 'legalName' => $param['legalName'], 'legalIdno' => ''
            ]);
//            $url = "http://smlrealname.tsign.cn:8080/realname/rest/external/organ/infoAuth";
            $url = config::get('eSignInfo.esignUrlC')."/realname/rest/external/organ/infoAuth";
            $df = $this->http_post_data($url, $data);
            $info = json_decode($df[1]);
            if ($info->errCode == 0) {
                $times = intval($indol['com_times']) + 1;
                //成功认证  认证id $info->serviceId
                Db::name('company')->where(['id'=>$id])->update(['serviceId' => $info->serviceId, 'is_verify_company'=>1,'com_times' => $times, 'verifytime' => getFormatTime()]);//企业真实性存在
                Db::table('os_user')->where(['id'=>$param['uid']])->setField('company_id',$id);
                $code =$this->giveCompanyPay($param,$info->serviceId);//打款
                $retunData =  ['name' => $param['name'], 'serviceId' => $info->serviceId];
//                jsonSend(1, '企业信息验证成功,请验证打款金额',$retunData);
                $code->errCode == 0 ? jsonSend(1, '企业信息验证成功,请验证打款金额',$retunData) :  jsonSend(0, '企业信息验证成功',$retunData);exit;
            } else {
                Db::name('company')->where(['id'=>$id])->setInc('com_times', 1);//企业真实性存在
                jsonSend(0, $info->msg);
                exit;
            }
        }else{
            jsonSend(0, '请求类型错误');
            exit;
        }
    }

    /**
     * 用户给企业进行打款
     **/
    private function giveCompanyPay($param,$serviceId){
        $data1 = [
            "name"=> $param['name'],//对公账户户名（一般来说即企业名称）
            "cardno"=> $param['cardno'],//企业对公银行账号
            "subbranch"=>  $param['subbranch'],//企业银行账号开户行支行全称
            "bank"=>  $param['bank'],//企业银行账号开户行名称
            "provice"=>  $param['provice'],//开户行所在省份
            "city"=>  $param['city'],//开户行所在城市
            "notify"=> "",//打款完成通知地址
            "serviceId"=> $serviceId,//校验成功之后返回的id
            "prcptcd"=> "",
            "pizId"=> ""
        ];
        $data = json_encode($data1);
//        $url = "http://smlrealname.tsign.cn:8080/realname/rest/external/organ/toPay";//测试的
        $url =  config::get('eSignInfo.esignUrlC')."/realname/rest/external/organ/toPay";
        $df= $this->http_post_data($url,$data);
        $info =  json_decode($df[1]);
        return $info;
    }

    /**
     * 用户验证企业打款金额信息核实
     * @param mobile  this is mobile
     * @param codeUSC  this is 企业信誉代码或者组织机构代码
     * @param cash  this is 打款金额
     * @param legalName  this is 法人姓名
     * @param name  this is 公司名称
     **/
    public function valiCompanyPay(){
        $param = Request::instance()->param();
        $info = Db::name('company')->where(['mobile' => $param['mobile'], 'com_cod' => $param['codeUSC'], 'username' => $param['legalName'], 'com_name' => $param['name']])->field('id,serviceId,cash_times,is_verify_pay')->find();
//        jsonSend(1,'huo',$info);die;
        if(!$info['serviceId']){jsonSend(0,'请先验证企业信息');exit;}
        if($info['is_verify_pay'] == 1){jsonSend(0,'打款金额已经验证成功,请勿重复提交');exit;}
        if($info['cash_times'] >= 3){jsonSend(0,'金额验证已达三次，请联系客服');exit;}
        $data = json_encode(['serviceId'=>$info['serviceId'],'cash'=>$param['cash']]);
//        $url = "http://smlrealname.tsign.cn:8080/realname/rest/external/organ/payAuth";//测试的
        $url =    config::get('eSignInfo.esignUrlC')."/realname/rest/external/organ/payAuth";//正式的
        $df= $this->http_post_data($url,$data);
        $infos =  json_decode($df[1]);
        Db::name('company')->where(['id'=>$info['id']])->setInc('cash_times',1);//验证次数
        if($infos->errCode == 0){
            //成功认证 //认证id $info->serviceId
            Db::name('user')->where(['mobile'=> $param['mobile']])->setField('usertype',1);//变成企业用户
            Db::name('company')->where(['id'=>$info['id']])->update(['is_bind'=>1,'verifytime'=>getFormatTime(),'is_verify_pay'=>1]);//验证成功
            jsonSend(1,'验证绑定成功');exit;
        }else{
            jsonSend(0, $infos->msg);exit;
        }
    }

    private function http_post_data($url, $data) {
        $projectId = config::get('eSignInfo.projectId');
        $signature = $this->getSignature($data,config::get('eSignInfo.projectSecret'));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-timevale-mode:package", "X-timevale-project-id:" . $projectId, "X-timevale-signature:" . $signature, "X-timevale-signature-algorithm:hmac-sha256", "Content-Type:application/json"));
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array($return_code, $return_content);
    }

    //计算请求签名值
    private function getSignature($message, $projectSecret) {
//        $projectSecret = '95439b0863c241c63a861b87d1e647b7';
        $signature = hash_hmac('sha256', $message, $projectSecret, FALSE);
        return $signature;
    }

}