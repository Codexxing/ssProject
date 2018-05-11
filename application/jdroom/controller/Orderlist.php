<?php
namespace app\jdroom\controller;

use app\common\model\Orderlist as OrderlistModel;
use app\common\controller\AdminBase;
use think\Config;
use think\Db;
use think\Request;
use think\Session;

/**
 * 订单管理
 * Class AdminUser
 * @package app\admin\controller
 */
class Orderlist extends AdminBase
{
    protected $order_list;

    protected function _initialize()
    {
        parent::_initialize();
        $this->order_list = new OrderlistModel();
    }

    /**
     * 订单管理
     * @param string $keyword
     * @param int    $page
     * @param 默认为今天的时间
     * @param 已支付是已经支付但未完成 按更新时间倒序
     * @param 未完成：未支付，已支付，已取消的订单  按更新时间倒序
     * @param 未支付：所有未支付的订单  按更新时间倒序
     * @param 已取消：所有已经取消的订单  按更新时间倒序
     * @param 已完成：所有已经完成的订单  按更新时间倒序
     * @param 所有订单：所有订单  按创建时间倒序
     * @return mixed
     */
    public function index($keyword = '', $page = 1,$paystatus=1,$time=1){
        $loginId = Session::get('admin_id');
        $num = config::get('listNum');
        switch($time){
            case -1://昨天
                $date = date("Y-m-d", strtotime("-1 day"));
                break;
            case 1: //默认今天的时间
                $date = date("Y-m-d");
                break;
            case 7://七天
                $date = date("Y-m-d", strtotime("-7 day"));
                break;
            case 30://30天
                $date = date("Y-m-d", strtotime("-30 day"));
                break;
            default:
                break;
        }

        $where=[];
        switch($paystatus){
            case 0://全部订单  创建时间
                $order_list = $this->getOrderList($num,$page,0,$keyword);
                break;
            case 1://已支付未完成  按更新时间
                $paystatus=1;
                $order_list = $this->getPayComplete($num,$page,0,$keyword);
                break;
            case 2://未完成  按更新时间
                $order_list = $this->getOrderList($num,$page,1,$keyword);
                break;
            case 3://未支付且未取消  按更新时间
                $order_list = $this->getCancleOrder($num,$page,$where,0,$keyword);
                break;
            case 4://已取消  按更新时间
                $order_list = $this->getCancleOrder($num,$page,$where,1,$keyword);
                break;
            case 5://（已完成）已支付的且快递号正确  按更新时间
                $order_list = $this->getPayComplete($num,$page,1,$keyword);
                break;
            case 6://我的接单
                $order_list = $this->getMyOrderList($num,$page,2,$keyword);
                break;
        }
//        var_dump($order_list);die;
        $num=[];
        $num['payComplete'] = Db::name('order_list')->where(['paystatus'=>1,'is_complete'=>0])->count(); //已支付
        $num['payNoComplete'] = Db::name('order_list')->where(['paystatus'=>0,'is_complete'=>0])->count();//未支付
        $num['payCancle'] = Db::name('order_list')->where(['is_complete'=>2])->count();//已取消
        $num['complete'] = Db::name('order_list')->where(['is_complete'=>1,'paystatus'=>1])->count();//已完成
        $num['mynum'] = Db::name('order_list')->where(['auid'=>$loginId])->count();//我的接单数量
        $num['all'] = Db::name('order_list')->count();//已完成
        $num['nocomplete'] = intval( $num['payNoComplete'])+intval( $num['payCancle'])+intval($num['payComplete']);//未完成

        $st =  $date . ' 00:00:00';
        $en =  $date . ' 23:59:59';
        return $this->fetch('index', ['list' => $order_list, 'keyword' => $keyword,'paystatus'=>$paystatus,'date'=>[$st,$en],'num'=>$num]);
    }

    /**
     *  按更新时间
     * @return mixed
     * @return $status 完成状态  0已支付但未完成  1支付并完成
     */
    private function getPayComplete($num=10,$page=1,$status,$keyword){
       return $this->order_list->getPayComplete($num,$page,$status,$keyword);
    }
    /**
     * 我的接单
     * $status  2是我的接单列表
    **/
    public function getMyOrderList($num=10,$page=1,$status,$keyword){
        return $this->order_list->getMyOrderList($num,$page,$status,$keyword);
    }
    /**
     *已取消的订单和未支付且未取消的  按更新时间
     *
     * $status 1已取消的   0未支付且未取消的
     * @return mixed
     */
    private function getCancleOrder($num=10,$page=1,$where,$status,$keyword){
       return $this->order_list->getCancleOrder($num,$page,$where,$status,$keyword);
    }

    /**
     * 获取全部订单和未完成订单  按创建时间排序
     *$status  0是全部订单   1是未完成订单
    **/
    private function getOrderList($num=10,$page=1,$status,$keyword){
        return $this->order_list->getOrderList($num,$page,$status,$keyword);
    }

    /**
     * 订单详情展示
     * @return mixed
     */
    public function detail()
    {
        $order=input('order');
        $list = Db::name('order_list')->where(['order_number'=>$order])->find();//订单
        $list['username'] = Db::table('os_admin_user')->where(['id'=>$list['auid']])->value('username');
        $address = Db::name('address')->where(['order_number'=>$order])->find();//地址
        $files = Db::name('letters_file')->where(['order_number'=>$order,'status'=>0])->find();//第一次资料
        $files['url'] = explode(',',$files['file']);
        foreach($files['url'] as $k=>$v){
            $files['url'][$k]=strstr($v,'/');
        }
        if($list['is_have'] == 1) {//用户存在补充的资料
            if($list['replenishId'] == 0)
                $replenishfiles['url'] = '';
            else {
                $replenishfiles = Db::name('letters_file')->where(['id' => $list['replenishId'], 'status' => 1])->find();//补充的资料
                $replenishfiles['url'] = explode(',', $replenishfiles['file']);
                foreach ($replenishfiles['url'] as $k => $v) {
                    $replenishfiles['url'][$k] = strstr($v, '/');
                }
            }
        }else{
            $replenishfiles['url'] = '';
        }
        $this->assign('replenishfiles',$replenishfiles);
        if($list['send_type'] == 1){
            $company = Db::name('letters_file')->where(['order_number'=>$order,'status'=>0,'types'=>1])->find();//企业资料
            $company['url'] = explode(',',$company['file']);
        }else{
            $company['url'] ='';
        }
        $this->assign('company',$company);
        $message = Db::name('message')->where(['order_number'=>$order])->select();//用户给律师的律师函发送的信息
        //是否有补充需求  如果存在页面就展示补充需求
        $is_replenish = Db::name('letters_file')->where(['order_number'=>$order,'status'=>1])->find();
//查找提交律师函的修改需求与资料
       $layerFile =  Db::name('layer_file')->where(['order_number'=>$order])->order('createtime asc')->select();
        if($layerFile) {
            foreach ($layerFile as $k => $v) {
                $layerFile[$k]['pdf'] = explode(',', $v['files']);
                foreach( $layerFile[$k]['pdf'] as $ke=>$ve){
                    $layerFile[$k]['pdf'][$ke] =ltrim($ve,'.');//去除路径前面的点，保证能在浏览器打开
                }
            }
            $layerFile['count'] = count($layerFile);
        }
//        echo '<pre>';
//        dump($layerFile);
//        die;

        $changeContent =  Db::name('message')->where(['order_number'=>$order,'send_type'=>2,'status'=>3])->order('createtime asc')->select();

        $changeContent['count'] = count($changeContent);
        if(collection($changeContent)->isEmpty()) {
            $changeContent['content'] = '';
        }
        //查找生成的hash值
//        $hash = Db::name('timestamp_hash')->where(['order_number'=>$order])->find();
        $list = $this->getBtnIsClick($list,$list['signServiceId']);
//        var_dump($list);die;
        $flag=0;
        if($list['layerIssue'] == 1){//提交律师函定位
            $flag=2;
        }else
        if($list['signServiceId']){//用户的授权资料
            $flag=3;
        }else
        if($list['express_number']){//快递单号
            $flag=4;
        }
        $fvp =Db::name('message')->where(['order_number'=>$order,'status'=>2,'send_type'=>1])->find();
        $fvp['content'] ? $interact=1 : $interact=0;//;//是否有互动 0没有
        return $this->fetch('detail',['order'=>$order,'list'=>$list,
            'address'=>$address,'files'=>$files,
            'message'=>$message,'is_replenish'=>$is_replenish,
            'layerFile'=>$layerFile,'changeContent'=>$changeContent,
            'flag'=>$flag,'is_interact'=>$interact
        ]);
    }
    /**
     * 获取按钮的权限状态
     * 0不可点 1可点
    **/
    private function getBtnIsClick($list,$hash){
        $login_id = Session::get('admin_id');
        if($list['is_complete'] ==2){//当为取消订单或者完成订单的状态下按钮都不可点的状态下
            $list['uploadBtn'] =0; $list['iscancle'] = 2; $list['orderstatus'] = '已取消';
        }if($list['is_complete'] ==1){//当为取消订单或者完成订单的状态下按钮都不可点的状态下
            $list['uploadBtn'] =0; $list['iscancle'] = 0;  $list['orderstatus'] = '已完成';
        }else {//当未完成的情况下的按钮点击
            //取消订单状态和完成订单状态
            if ($list['auid'] && $list["auid"] != 0) {//当存在接单人的时候
                if ($list['auid'] == $login_id || $login_id == 1) {//当是同一个人或者是超级管理员时可点击
                    $hash ? $list['iscancle'] = 0 : $list['iscancle'] = 1;
                } else {
                    $list['iscancle'] = 0;//完成订单不可点
                }
            } else {//不存在接单人就可以点击
                $list['iscancle'] = 1;//1为按钮可点击   0按钮不可点击
            }

            /**当资料全部没有问题时才可以进行点击上传
             * 选择多文件和开始上传按钮状态   0不可点击  1可点击
             * 当已经上传了，但正在让用户等待的同时不可点击
             **/
//            ($list['is_have'] == 1  || $list['is_issue'] == 1) ? $list['uploadBtn'] = 1 : $list['uploadBtn'] = 0;//当用户确认没问题确认发函时不可点
            ($list['layerIssue'] == 1 ) ? $list['uploadBtn'] = 1 : $list['uploadBtn'] = 0;//0不可点击  1可点击
            ($list['is_issue']==0 && $list['layerIssue'] == 1) ?  $list['uploadBtn'] = 1 :  $list['uploadBtn'] = 0;//律师函有问题可点并且资料没问题
//            ($list['is_issue'] == 0 ) ? $list['uploadBtn'] = 0 : $list['uploadBtn'] = 1;//0不可点击  1可点击
            /**
             * 订单状态
             * 待支付->已支付->待出函->待修改->已出函->待确定->待签字->待发出->已完成
             **/
            switch($list['is_complete']){
                case 0://未完成
                    if($list['paystatus']==1){
                        $list['orderstatus'] = '已支付';
                        //已支付了 用户是否确认律师函或者正在出涵  或者等待用户确认
                        if($list['layerletter_have'] == 0 && $list['layerIssue'] == 1){//是否已出律师函 0正在出涵  1已出函
                            $list['orderstatus'] = '正在出函';
                        }else  if($list['layerletter_have'] == 1){
                            switch($list['is_issue']){//用户对律师函有问题  0有问题  1没问题确认发函  2等待用户确认
                                case 0:
                                    $list['orderstatus'] = '律师函待修改';
                                    break;
                                case 1:
                                    if($list['signServiceId'])
                                        $list['orderstatus'] = '准备发函';
                                    else
                                        $list['orderstatus'] = '待用户签字';
                                    break;
                                case 2:
                                    $list['orderstatus'] = '待用户确认';
                                    break;
                            }
                        }

                    }else if($list['paystatus']==0){
                        $list['orderstatus'] = '待支付';
                    }
                    break;
                case 1://已完成
                    $list['orderstatus'] = '已完成';
                    break;
                case 2://已取消
                    $list['orderstatus'] = '已取消';
                    break;

            }

        }
//echo "<pre>";
//        var_dump($list);die;
        return $list;
    }
    /**
     * 下载授权书
    **/
    public function downLoadPdf(){
        $url = $_GET['url'];
        if (false == file_exists($url)) {
            return false;
        }
        // http headers
        header('Content-Type: application-x/force-download');
        header('Content-Disposition: attachment; filename="' . basename($url) .'"');
        header('Content-length: ' . filesize($url));

        // for IE6
        if (false === strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) {
            header('Cache-Control: no-cache, must-revalidate');
        }
        header('Pragma: no-cache');
        return readfile($url);;
    }
    //查看需求描述
    public function contentSearch(){
        $order = Request::instance()->param('order');
        $cont = Db::table('os_order_list')->where(['order_number'=>$order])->value('content');
        jsonSend(1,'获取成功',$cont);
//        $this->success('获取成功',$cont);
    }
    //上传发票与律师函的展示页面
    public function getShowUpload(){
        $list =Db::name('file_stamp')->where(['order_number'=> input('order')])->find();
        $list['order'] =Db::table('os_order_list')->where(['order_number'=> input('order')])->value('order_number');
        return $this->fetch('upload',['list'=>$list]);
    }
    //查看律师与客户的互动消息
    public function lookinteract(){
        $order = Request::instance()->param('order');
        $list =Db::name('message')->where(['order_number'=>$order,'status'=>2])->order('createtime asc')->select();
        $replenish =Db::name('replenish')->where(['order_number'=>$order])->order('id asc')->select();
        foreach($replenish as $k=>$v){
            if($v['url']){
                $url = explode(',',$v['url']);
                $replenish[$k]['urls'] = $url;
            }else{
                $replenish[$k]['urls'] = '';
            }
        }
//        echo "<pre>";
//        var_dump($replenish[2]);die;
        return $this->fetch('interact',['list'=>$list,'replenish'=>$replenish]);
    }

}