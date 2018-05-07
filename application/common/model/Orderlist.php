<?php
namespace app\common\model;

use think\Model;
use think\Db;
use think\Session;

class Orderlist extends Model{
    protected $insert = ['create_time'];
    /**
     * 获取订单列表
     * $status 0是获取全部订单   1获取未完成订单
    */

    public function getOrderList($num,$page,$status,$keyword){
        Db::startTrans();
        $res =[];
        if($status==0){$order = 'createtime desc';$where='';}else{$order = 'updatetime desc';$where='is_complete != 1';}
        $map['order_number'] = ['like', "%{$keyword}%"];
        try{
            $res = Db::name('order_list')
                ->order($order)
                ->where($where)
                ->where($map)
//                ->where('list.'.$order,'between time',[$where['start'],$where['end']])
                ->paginate($num, false, ['page' => $page,'query' => request()->param()]);
            $ask =$res->toArray();
            $ask = $this->getDelData($ask);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
           return $res;
        }

        return  [$ask,$res];
    }
    /**
     * 我的接单
    **/
    public function getMyOrderList($num,$page,$status,$keyword){
        Db::startTrans();
        $loginId=Session::get('admin_id');
        $res =[];
        $order = 'updatetime desc';$where=['auid'=>$loginId];
        $map['order_number'] = ['like', "%{$keyword}%"];
        try{
            $res = Db::name('order_list')
                ->order($order)
                ->where($where)
                ->paginate($num, false, ['page' => $page,'query' => request()->param()]);
            $ask =$res->toArray();
            $ask = $this->getDelData($ask);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            return $res;
        }

        return  [$ask,$res];
    }
    /**
     * （已完成）已支付的且快递号正确  按订单更新时间排序
     *  0已支付但未完成  1支付并完成
     * @return bool|string
     */
    public function getPayComplete($num,$page,$status,$keyword){
        Db::startTrans();
        $res =[];
        try{
            $where['list.paystatus']=1;
            $where['list.order_number']=['like', "%{$keyword}%"];
            $status ==1 ? $where['list.is_complete']=1 :  $where['list.is_complete']=0;
            $res = Db::name('order_list')->alias('list')
                ->join('address','list.order_number=address.order_number','LEFT')
                ->join('admin_user','list.auid=admin_user.id','LEFT')
                ->field('list.*,admin_user.username,address.send_name,address.accept_name')
                ->where($where)
                ->order('list.updatetime desc')
                ->paginate($num, false, ['page' => $page,'query' => request()->param()]);
//            echo Db::name('order_list')->getLastSql();die;
            $ask =$res->toArray();
            $ask = $this->getDelData($ask);
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            return $res;
        }
        return  [$ask,$res];
    }
    /**
     * 已取消的status为1 和 未支付且未取消的status为0  按订单更新时间排序
     * @return bool|string
     */
    public function getCancleOrder($num,$page,$where,$status,$keyword){
        Db::startTrans();
        $res =[];
        try{
            if($status==1){
                $where['is_complete']=2;//已经取消的
            }else if($status==0){
                $where['is_complete'] = $where['paystatus']=0;//未支付且未取消的
            }
        $where['order_number'] = ['like', "%{$keyword}%"];
            $res = Db::name('order_list')
                ->where($where)
                ->order('updatetime desc')
//                ->field('id,order_number,auid,is_complete,content,paystatus,createtime,updatetime')
//                ->where('list.'.$order,'between time',[$where['start'],$where['end']])
                ->paginate($num, false, ['page' => $page,'query' => request()->param()]);
//            echo Db::name('order_list')->getLastSql();
            $ask =$res->toArray();
            $ask = $this->getDelData($ask);
            Db::commit();
        } catch (\Exception $e) {
            return $res;
        }
        return  [$ask,$res];
    }

    /**
     * 数据处理
     * @return bool|string
     */
    private function getDelData($ask){
        $uid = Session::get('admin_id');//当前登录的id
        /**
         * isreceive 接单状态  0代表接单（即还没有接单人）  1代表不同管理员看见的现实已接单  2是一个管理员显示取消订单
         * iscancle   是否能取消订单  0不能  1能  只有本人和超级管理员才能取消
        **/
//        header("Content-type:text/html;charset=utf-8");
        foreach ($ask['data'] as  $key => $val) {
            $hash = $val['signServiceId'];
            $acccept = Db::name('address')->where(['order_number' => $val["order_number"]])->field('accept_name,send_name')->find(); //根据ID查询相关其他信息
            $uid = Db::table('os_order_list')->where(['order_number' => $val["order_number"]])->value('uid'); //根据ID查询相关其他信息
//            $ask['data'][$key]['accept_name'] = wordwrap(iconv("GB2312", "UTF-8", ), 8, "\n", true);
            $user = getUserVal(['id'=>$uid],'id,mobile,usertype,userauth');
//            $ask['data'][$key]['mobile'] =getOneUserVal(['id'=>$uid],'mobile');
            $ask['data'][$key]['user'] =$user;
//            $ask['data'][$key]['usertype'] =$user['usertype'];
//            $ask['data'][$key]['userauth'] =$user['userauth'];

            $ask['data'][$key]['accept_name'] = $acccept['accept_name'];
            $ask['data'][$key]['accept_name_s'] = msubstr($acccept['accept_name'],0,3);
            $ask['data'][$key]['send_name'] = $acccept['send_name'];
            $ask['data'][$key]['send_name_s'] = msubstr($acccept['send_name'],0,3);
//            $ask['data'][$key]['send_name'] = wordwrap(iconv("UTF-8", "gbk", $acccept['send_name']), 8, "\n", true);
//            $ask['data'][$key]['send_name'] = wordwrap($acccept['send_name'], 4, "\n", true);
            //如果是超级管理员和本人可以对接单进行操作，否则就禁用成为已接单
            $ask['data'][$key]['username'] = Db::table('os_admin_user')->where(['id' => $val["auid"]])->value('username');
            //当前登陆者和接单人是同一个人或者是超级管理员  按钮可操作
            //当前按钮可点击的前提是上一步的步骤确认没问题才可以点击
            //是否取消订单
//            if($val['is_complete'] == 2){
//                $ask['data'][$key]['iscancle'] = 2;//已取消
//            }else {
                if ($val['auid'] && $val["auid"] != 0) {//当存在接单人的时候
                    if ($val['auid'] == $uid || $uid == 1) {//当是同一个人或者是超级管理员时可点击
                        $val['is_issue'] == 1 ? $ask['data'][$key]['iscancle'] = 0 : $ask['data'][$key]['iscancle'] = 1;
                    } else {
                        $ask['data'][$key]['iscancle'] = 0;
                    }
                } else {//不存在接单人就可以点击
                    $ask['data'][$key]['iscancle'] = 1;//1为按钮可点击   0按钮不可点击
                }
//            }
            /**
             * 是否能接单
             * 已经接单时：当是同一个人或者超级管理员时显示取消接单
             * 当没有接单就显示没接单
             * isreceive 0接单  1已接单  2可点击的取消接单  3不可点击的取消接单  4未支付的情况下是不能接单的
            **/
            if($val['paystatus'] == 1) {//已支付
                if ($val['auid'] && $val["auid"] != 0) {//当存在接单人的时候
                    if ($val['auid'] == $uid || $uid == 1) {
                        //如果订单已经传到第三方也是不能取消接单的
                        (!empty($hash) && !is_null($hash)) ? $ask['data'][$key]['isreceive'] = 3 : $ask['data'][$key]['isreceive'] = 2;
                    } else {
                        $ask['data'][$key]['isreceive'] = 1;
                    }
                } else {
                    $ask['data'][$key]['isreceive'] = 0;
                }
            }else{
                $ask['data'][$key]['isreceive'] = 4;//没有支付禁止接单
            }
            /**
             * 订单状态
             * 待支付->已支付->待出函->待修改->已出函->待确定->待签字->待发出->已完成
            **/
            switch($val['is_complete']){
                case 0://未完成
                    if($val['auid'] == 0){
                        $ask['data'][$key]['orderstatus'] = '待接单';
                    }else {
                        if ($val['paystatus'] == 1) {
                            //已支付了 用户是否确认律师函或者正在出涵  或者等待用户确认
                            if ($val['layerletter_have'] == 0) {//是否已出律师函 0正在出涵  1已出函
                                $ask['data'][$key]['orderstatus'] = '正在出函';
                            } else if ($val['layerletter_have'] == 1) {
                                switch ($val['is_issue']) {//用户对律师函有问题  0有问题  1没问题确认发函  2等待用户确认
                                    case 0:
                                        $ask['data'][$key]['orderstatus'] = '律师函待修改';
                                        break;
                                    case 1:
                                        if ($val['signServiceId'])
                                            $ask['data'][$key]['orderstatus'] = '准备发函';
                                        else
                                            $ask['data'][$key]['orderstatus'] = '待用户签字';
                                        break;
                                    case 2:
                                        $ask['data'][$key]['orderstatus'] = '待用户确认';
                                        break;
                                }
                            } else if ($val['layerletter_have'] == 2) {//还没有进行到律师函步骤
                                if ($val['layerIssue'] == 1) {
                                    $ask['data'][$key]['orderstatus'] = '准备出函';
                                } else if ($val['layerIssue'] == 0) {
                                    $ask['data'][$key]['orderstatus'] = '待用户补充需求';
                                } else if ($val['layerIssue'] == 3) {
                                    $ask['data'][$key]['orderstatus'] = '需资料审核';
                                }
                            } else {
                                if ($val['auid'] == 0) {
                                    $ask['data'][$key]['orderstatus'] = '已支付待接单';
                                }

                            }

                        } else if ($val['paystatus'] == 0) {
                            $ask['data'][$key]['orderstatus'] = '待支付';
                        }
                    }
                    break;
                case 1://已完成
                    $ask['data'][$key]['orderstatus'] = '已完成';
                    break;
                case 2://已取消
                    $ask['data'][$key]['orderstatus'] = '已取消';
                    break;

            }


        }
//        echo "<pre>";
//        var_dump($ask);die;
        return $ask;
    }


    /**
     * 创建时间
     * @return bool|string
     */
    protected function setCreateTimeAttr()
    {
        return date('Y-m-d H:i:s');
    }
}