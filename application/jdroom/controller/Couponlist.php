<?php
namespace app\jdroom\controller;

use app\common\model\AdminUser as AdminUserModel;
use app\common\model\AuthGroup as AuthGroupModel;
use app\common\model\AuthGroupAccess as AuthGroupAccessModel;
use app\common\controller\AdminBase;
use think\Config;
use think\Db;
use think\Request;
use think\Session;

/**
 * 优惠券列表
 * Class AdminUser
 * @package app\admin\controller
 */
class Couponlist extends AdminBase
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 列表
     * @return mixed
     */
    public function index($keyword = '')
    {
        $map=[];
        if($keyword){
            $map['reduce_number'] = ['like', "%{$keyword}%"];
        }
        $admin_user_list =Db::name('reduced')->where($map)->paginate();
        $res=$admin_user_list->toArray();
        foreach($res['data'] as $k=>$v){
            if(($v['overtime'] < date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])) || $v['is_conversion']==1){
                $res['data'][$k]['btn']=0;
            }else{
                $res['data'][$k]['btn']=1;
            }
        }

        return $this->fetch('index', ['res' => $admin_user_list,'list'=>$res['data'],'keyword'=>$keyword]);
    }

    /**
     * 生成优惠券
     * @return mixed
     */
    public function add()
    {

        return $this->fetch('add');
    }

    /**
     * 生成优惠券
     * @param $group_id
     */
    public function save()
    {
        $coupMoney = Db::table('os_price')->where('id=1')->value('money');
//        if ($this->request->isPost()) {
            $data            = input();
        if($data['money']==0){
            $this->error('价格不能为0');die;
        }
        if($data['money'] > $coupMoney){
            $this->error('最大金额不得超过律师函价格：'.$coupMoney.'元');die;
        }
        if(!is_int($data['money'])){
            $this->error('金额别输小数了吧');die;
        }
            $arr=[
                'uid'=>'',
                'money'=> intval($data['money']),
                'types'=> 3,
                'is_use'=> 0,
                'is_conversion'=> 0,
                'createtime'=>date('Y-m-d H:i:s',time()),
                'updatetime'=>date('Y-m-d H:i:s',time()),
                'overtime'=>date('Y-m-d H:i:s',strtotime("+100 year"))
            ];
        for($i=1;$i<=intval($data['num']);++$i){
               $num = mt_rand(100000,990000);
               $arr['reduce_number']=$num+intval($i);
                Db::name('reduced')->insert($arr);
           }
        createLog('生成了'.$data['num'].'个新的优惠券');
            $this->success('成功');
//        }
    }

    /**
     * 删除优惠券
     * @param $id
     */
    public function delete($id)
    {
        $re =Db::name('reduced')->where(['id'=>$id])->find();
        createLog('删除了编号为：'.$re['reduce_number'].'的优惠券');
       Db::name('reduced')->where(['id'=>$id])->delete();
        $this->success('删除成功');
    }

    /**
     * 兑换优惠券
     * */
    public function changeCoupon(){
        if(Request::instance()->post()){
            $id = Request::instance()->param('id');
            $conversion = Db::name('reduced')->where(['id' => $id])->find();
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
            Db::name('reduced')->where(['id' => $id])->update(['uid'=>Session::get('admin_id'),'is_conversion'=>1,'updatetime'=>getFormatTime()]);
            createLog('兑换了编号：'.$conversion['reduce_number'].'的优惠券');
            $this->success('兑换成功');
        }else{
            $this->error('请求类型错误');
        }
    }
    //下载未兑换的优惠券
    public function downloadOrder(){
        createLog('导出了未兑换的优惠券Excel');
        $data = Db::name('reduced')->where(['is_conversion' => 0])->field('reduce_number')->select();
//        var_dump($data);die;
        $string = "兑换码\t\n";
        $string = iconv('utf-8','gb2312',$string);
        foreach ($data as $key=>$val) {
            $string .= $val['reduce_number']."\t\n";
        }
        $fileName = '兑换码-'.date("YmdHis").".xls";
        $this->exportexcel($fileName,$string);
    }

    public static function exportexcel($filename='report',$content){
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/vnd.ms-execl");
        header("Content-Type: application/force-download");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Content-Transfer-Encoding: binary");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $content;
    }

}