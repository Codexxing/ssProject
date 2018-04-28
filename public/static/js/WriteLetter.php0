<?php
/**
 * Created by PhpStorm.
 * User: liyang
 * Date: 2018/1/15
 * Time: 15:43
 * File:律师函model类
 */

namespace app\index\model;
use think\Model;
use think\Db;
use think\config;

class WriteLetter extends Model
{
    //获取针对订单号写函的所有的信息
    public function getLetter($data=[]){
        $arr=[];
        $list = Db::name('order_list')
            ->field('uid,order_number,content,replenish_content,is_complete,email_complete,receive_order,express_complete,is_refund,step,express_number,send_type,layerIssue,layerletter_have,paystatus,times,is_issue,likes,canclereceivetime,receivetime,completetime,cancletime,expresstime,signServiceId,createtime,updatetime,recude_id')
            ->where(['uid'=>$data[0],'send_type'=>$data[1],'order_number'=>$data[2]])->find();
        if($list['recude_id'] !=0){
            $list['reduce'] = Db::table('os_reduced')->where(['id'=>$list['recude_id']])->value('money') ;//优惠的钱
            $money  = 168- $list['reduce'];//实际付款
           $money <=0 ? $list['real_money']=0.01 : $list['real_money']=$money;
        }else{
            $list['reduce_id']=$list['reduce']=0.00;
            $list['real_money'] =168.00;
        }

        if(!$list['signServiceId']){$list['signServiceId']='';}
        $list ? $arr['list'] =$list : $arr['list']='';
       $address = Db::name('address')->where(['uid'=>$data[0],'order_number'=>$data[2]])->find();
        $address ? $arr['address'] =$address : $arr['address']='';
        $file = Db::name('letters_file')->where(['order_number'=>$data[2]])->select();
        $file ? $arr['files'] =$file : $arr['files']='';
        $message = Db::name('message')->field('order_number,uid,content,status,send_type,createtime,updatetime')->where(['uid'=>$data[0],'send_type'=>1,'order_number'=>$data[2]])->order('createtime desc')->find();
        $message ? $arr['message'] =$message : $arr['message']='';
        return $arr;
    }
    /*
     * 写信的保存与更新
     * $params 保存的数据
     * $type  add代表新建  update代表修改
     * */
    public function writeSaveUpdate($params='',$type='add'){
        Db::startTrans();
        try{
            if(!empty($params['file'])){
                $fileUrl = base64Upload($params['file']);
                count($fileUrl)==1 ? $url =$fileUrl[0] : $url = implode(',',$fileUrl);
            }else{
                $url='';
            }

        if($type=='add'){
            $order = 'SH'.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            $time=getFormatTime();
            $data=[
                'uid'=>$params['uid'],
                'order_number'=>$order,
                'send_type'=>$params['send_type'],
                'is_complete'=>0,
                'step'=>1,
                'layerletter_have'=>2,
                'layerIssue'=>3,
                'paystatus'=>0,
                'is_have'=>2,
                'content'=> htmlspecialchars($params['content']),
                'createtime'=> $time,
                'updatetime'=> $time
            ];
            Db::name('order_list')->insert($data);
            if(!empty($url)){
                Db::name('letters_file')->insert(['order_number'=>$order,'file'=>$url,'status'=>0,'createtime'=>$time,'updatetime'=>$time]);
            }
        }else{
            $order =$params['order_number'];
            $arr=[
                'content'=> htmlspecialchars($params['content']),
                'layerletter_have'=>0,
                'layerIssue'=>3,
                'updatetime'=> getFormatTime()
            ];
                Db::name('order_list')->where(['order_number'=>$params['order_number'],'uid'=>$params['uid'],'send_type'=>$params['send_type']])->update($arr);
                Db::name('letters_file')->where(['order_number'=>$params['order_number'],'status'=>0])->update(['file'=>$url,'updatetime'=>getFormatTime()]);
        }
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            return 1;
        }
        return [$order,$fileUrl];
    }


    /*
     * 地址的保存与更新
     * $params 保存的数据
     * $type  add代表新建  update代表修改
     * */
    public function writeAddress($params='',$type=''){
        Db::startTrans();
        try{
            $data=[
                'uid'=>$params['uid'],
                'order_number'=>$params['order_number'],
                'send_name'=>$params['send_name'],
                'send_phone'=>$params['send_phone'],
                'send_address'=>$params['send_address'],
                'send_email'=>$params['send_email'],
                'accept_name'=>$params['accept_name'],
                'accept_phone'=>$params['accept_phone'],
                'accept_address'=>$params['accept_address'],
                'accept_email'=>$params['accept_email'],
                'accept_type'=>array_key_exists('accept_type',$params) ? $params['accept_type'] : 0,
                'updatetime'=> getFormatTime()
            ];
            if(array_key_exists('companyCode',$params) && !empty($params['companyCode'])){$data['companyCode'] = $params['companyCode'];}else{$data['companyCode'] ='';}//公司信誉代码
            if($type=='add'){
                $data['createtime'] =getFormatTime();
               Db::name('address')->insert($data);
                Db::name('order_list')->where(['order_number'=>$params['order_number']])->setField('step',2);//更新步骤
                if(array_key_exists('file',$params)){
                    $fileUrl = base64Upload($params['file']);
                    count($fileUrl)==1 ? $url =$fileUrl[0] : $url = implode(',',$fileUrl);
                    Db::name('letters_file')->insert([
                        'order_number'=>$params['order_number'],
                        'file'=>$url,
                        'status'=>0,
                        'types'=>1,
                        'createtime'=> getFormatTime(),
                        'updatetime'=> getFormatTime()
                    ]);
                }
            }else   if($type=='update'){
                    Db::name('address')->where(['order_number'=>$params['order_number'],'uid'=>$params['uid']])->update($data);
                    Db::name('order_list')->where(['order_number'=>$params['order_number']])->update(['updatetime'=>getFormatTime(),'step'=>2]);//更新步骤
                if(array_key_exists('file',$params)){
                    $fileUrl = base64Upload($params['file']);
                    count($fileUrl)==1 ? $url =$fileUrl[0] : $url = implode(',',$fileUrl);
                   $re= Db::name('letters_file')->where(['order_number'=>$params['order_number']])->find();
                    if($re){
                        Db::name('letters_file')->where(['order_number'=>$params['order_number']])->update([
                            'file'=>$url,
                            'updatetime'=> getFormatTime()
                        ]);
                    }else{
                        Db::name('letters_file')->insert([
                            'order_number'=>$params['order_number'],
                            'file'=>$url,
                            'status'=>0,
                            'types'=>1,
                            'createtime'=> getFormatTime(),
                            'updatetime'=> getFormatTime()
                        ]);
                    }
                }
            }
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            return false;
        }
        return true;
    }
    /**
     * 用户给律师的修改意见
    **/
    public function postMarkIdea($param='',$times){
        if(empty($param)) return false;
        //首先查找对应律师的id
        $aid = getOneUserVal(['id'=>$param['uid'],'auid']);
        $time =getFormatTime();
        switch($param['is_issue']){
            case 0:
                //修改意见
                $data=[
                    'aid'=>$aid,
                    'order_number'=>$param['order_number'],
                    'uid'=>$param['uid'],
                    'content'=>$param['message'],
                    'send_type'=>2,
                    'status'=>3,
                    'createtime'=>$time,
                    'updatetime'=>$time,
                ];
                Db::name('message')->insert($data);
                 $changtime = intval($times)+1;

                //0有问题  1没问题
                Db::name('order_list')->where(['order_number'=>$param['order_number'],'uid'=>$param['uid']])->update(['is_issue'=>$param['is_issue'],'layerletter_have'=>0,'times'=>$changtime]);
                break;
            case 1:
                Db::name('order_list')->where(['order_number'=>$param['order_number'],'uid'=>$param['uid']])->update(['is_issue'=>$param['is_issue'],'is_refund'=>1,'step'=>4]);
                break;
        }

        $complete = Db::name('order_list')->where(['order_number'=>$param['order_number'],'uid'=>$param['uid']])->value('is_complete');
       return ['is_complete'=>$complete,'order_number'=>$param['order_number'],'userId'=>$param['uid']];
    }


}