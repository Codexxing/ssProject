<?php
/**
 * Created by PhpStorm.
 * User:    @liyang
 * Date: 2018/4/23
 * Time: 14:18
 * File:优惠券系列
 */

namespace app\index\controller;


use app\common\controller\HomeBase;
use app\index\model\User as UserModel;
use think\Db;
use think\Session;
use think\Request;
use think\Config;

class Coupon extends HomeBase
{
    protected $userModel;
    protected $todayDate;
    protected function _initialize(){
        parent::_initialize();
        $this->userModel = new userModel();
        $this->todayDate = date('Y-m-d',time());
    }
    //分享即可得到5块钱优惠券

    /**
     * 查看用户今天是否分享过
     * @param uid用户id
     **/
    public function getIsShare(){
        if(Request::instance()->post()){
            $uid = Request::instance()->param('uid');
            $time =Db::table('os_share')->where(['uid'=>$uid])->value('sharetime');
            if($time){
                if($time == $this->todayDate){
                    jsonSend(0,'今天已分享过了');exit;
                }
            }
            jsonSend(1,'可以分享');exit;
        }else{
            jsonSend(0,'请求类型错误');exit;
        }
    }

    /**
     * 分享成功后记录日期并赠送优惠券
     * @param uid用户id
     **/
    public function recordShareDate(){
        if(Request::instance()->post()){
            $uid = Request::instance()->param('uid');
            $time =Db::table('os_share')->where(['uid'=>$uid])->value('sharetime');
            if($time)
                Db::table('os_share')->where(['uid'=>$uid])->setField('sharetime',$this->todayDate);
            else
                Db::table('os_share')->insert(['uid'=>$uid,'sharetime',$this->todayDate]);

            //赠送优惠券
            Db::name('reduced')->insert([
                'uid'=>$uid,
                'money'=>5,
                'types'=>6,
                'is_use'=>0,
                'reduce_number'=>createNumber(),
                'is_conversion'=>1,
               'overtime'=>date('Y-m-d H:i:s',strtotime("+1 year")),
                'createtime'=>getFormatTime(),
                'updatetime'=>getFormatTime(),
            ]);
            jsonSend(1,'成功');exit;
        }else{
            jsonSend(0,'请求类型错误');exit;
        }
    }

}