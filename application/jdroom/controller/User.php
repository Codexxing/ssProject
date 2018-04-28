<?php
namespace app\jdroom\controller;

use app\common\model\User as UserModel;
use app\common\controller\AdminBase;
use think\Config;
use think\Db;
use think\Request;
use think\Session;

/**
 * 用户管理
 * Class AdminUser
 * @package app\admin\controller
 */
class User extends AdminBase
{
    protected $user_model;

    protected function _initialize()
    {
        parent::_initialize();
        $this->user_model = new UserModel();

    }

    /**
     * 用户管理
     * @param string $keyword
     * @param int    $page
     * @param int    $status  0个人  1企业  3默认全部
     * @return mixed
     */
    public function index($keyword = '', $page = 1,$status=3)
    {
        $map = [];
        if ($keyword) {
            $map['username|mobile|email'] = ['like', "%{$keyword}%"];
        }
        if($status == 0 || $status == 1 ){$map['usertype'] = $status;}
        $user_list = $this->user_model->where($map)->order('id DESC')->paginate(15, false, ['page' => $page]);
        $allCount = $this->user_model->count();//全部用户
        $userCount = $this->user_model->where(['usertype'=>0])->count();//个人用户
        $companyCount = $allCount-$userCount;//企业用户
        return $this->fetch('index', ['user_list' => $user_list, 'keyword' => $keyword,'status'=>$status,'count'=>[$allCount,$userCount,$companyCount]]);
    }

    /**
     * 添加用户
     * @return mixed
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 保存用户
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'User');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $data['password'] = md5($data['password'] . Config::get('salt'));
                if ($this->user_model->allowField(true)->save($data)) {
                    createLog('添加了新用户'.$data['username']);
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    /**
     * 编辑用户
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $user = $this->user_model->find($id);

        return $this->fetch('edit', ['user' => $user]);
    }

    /**
     * 更新用户
     * @param $id
     */
    public function update($id)
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'User');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                $user           = $this->user_model->find($id);
                $user->id       = $id;
                $user->username = $data['username'];
                $user->mobile   = $data['mobile'];
                $user->email    = $data['email'];
                $user->status   = $data['status'];
                if (!empty($data['password']) && !empty($data['confirm_password'])) {
                    $user->password = md5($data['password'] . Config::get('salt'));
                }
                if ($user->save() !== false) {
                    createLog('编辑了用户'. $user->username.'的信息');
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    /**
     * 删除用户
     * @param $id
     */
    public function delete($id)
    {
        if ($this->user_model->destroy($id)) {
            $name =Db::table('os_admin_user')->where(['id'=>$id])->value('username');
            createLog('删除了用户：'.$name);
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
    //查询某个用户的信息与公司信息
    public function searchCompany(){
        $user = $this->user_model->find($_POST['id']);
        if($user['usertype']==0){
            $com = json_decode($user['real_auth'],true);
        }else {
            $com = Db::name('company')->where(['id' => $user['company_id']])->find();
        }
        jsonSend(1,'',$com);
    }
    //更新用户账号状态
    public function updateStatus(){
        $id = $_POST['id'];
        $status = $_POST['status'];
        $status ==0 ? $mess = '禁用' : $mess='开启';
        Db::name('user')->where(['id'=>$id])->setField('status',$status);
        $phone = getOneUserVal(['id'=>$id],'mobile');
        createLog('给手机号为'.$phone.'更新了账号状态为：'.$mess);
        jsonSend(1,$mess.'成功');
    }
    /**
     * 赠送优惠券
    **/
    public function sendCoupon(){
        $param = Request::instance()->param();
        $money =getLayerPrice();
        if($param['value'] > $money){$this->error('优惠券金额不得大于'.$money.'元');exit;}
       $phone = Db::table('os_user')->where(['id'=>$param['id']])->value('mobile');
        $arr=[
            'uid'=>$param['id'],
            'money'=>$param['value'],
            'types'=>3,
            'is_use'=>0,
            'reduce_number'=>mt_rand(100000,999999),
            'is_conversion'=>0,
            'createtime'=>date('Y-m-d H:i:s',time()),
            'updatetime'=>date('Y-m-d H:i:s',time()),
            'overtime'=>date('Y-m-d H:i:s',strtotime("+1 year"))
        ];
        Db::name('reduced')->insert($arr);
       createLog('给手机号为'.$phone.'赠送了价值 '.$param['value'].' 元的优惠券');
        $this->success('赠送成功');
    }
//    /**
//     * 添加企业白名单
//     **/
//    public  function addWhiteCompany(){
//        $data=input();
//        $id =  Db::table('os_white_company')->where(['com_cod'=>$data['com_cod']])->value('id');
//        if($id){$this->error('该机构信誉代码对应公司已存在');exit;}
//        empty($data['username']) ? $data['username']='暂无' :  $data['username']=$data['username'];
//        $data['uid'] = Session::get('admin_id');
//        $data['createtime'] =  $data['updatetime']=getFormatTime();
//       Db::name('white_company')->insert($data);
//        createLog('添加了企业白名单，信誉代码：'.$data['com_cod'].'，名称：'.$data['username']);
//       $this->success('添加成功');
//    }

}