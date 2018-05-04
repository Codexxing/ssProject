<?php
namespace app\jdroom\controller;

use app\common\model\User as UserModel;
use app\common\controller\AdminBase;
use think\Config;
use think\Db;
use think\Request;
use think\Session;

/**
 * 企业白名单
 * Class AdminUser
 * @package app\admin\controller
 */
class Whitecompany extends AdminBase
{
    protected $user_model;

    protected function _initialize()
    {
        parent::_initialize();
        $this->user_model = new UserModel();

    }

    /**
     * 白名单列表
     * @param string $keyword
     * @param int    $page
     * @param int    $status  0个人  1企业  3默认全部
     * @return mixed
     */
    public function index($keyword = '', $page = 1,$status=3)
    {
        $map = [];
        if ($keyword) {
            $map['com_name|com_cod'] = ['like', "%{$keyword}%"];
        }
        $list = Db::name('white_company')->where($map)->order('id DESC')->paginate(15, false, ['page' => $page]);
        return $this->fetch('index', ['list' => $list, 'keyword' => $keyword]);
    }

    /**
     * 添加
     * @return mixed
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 编辑用户
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $user = Db::name('white_company')->find($id);

        return $this->fetch('edit', ['user' => $user]);
    }

    /**
     * 更新用户
     * @param $id
     */
    public function update()
    {
            $data = input();
//            $id =  Db::table('os_white_company')->where(['com_cod'=>$data['com_cod']])->value('com_name');
//            if($id){$this->error('该机构信誉代码对应公司已存在');exit;}
            $arr=[
                'uid'=>Session::get('admin_id'),
                'username'=>$data['username'],
                'com_name'=>$data['com_name'],
                'com_cod'=>$data['com_cod'],
                'status'=>$data['status'],
                'updatetime'=>getFormatTime(),
            ];
            Db::name('white_company')->where(['id'=>$data['com_id']])->update($arr);
        createLog('更新了企业信息  信誉代码：'.$data['com_cod']);
            $this->success('更新成功');
    }

    /**
     * 删除
     * @param $id
     */
    public function delete($id)
    {
            $name =Db::table('os_white_company')->where(['id'=>$id])->value('com_cod');
            createLog('删除了信誉代码为：'.$name.'的白名单信息');
            Db::name('white_company')->where(['id'=>$id])->delete();
            $this->success('删除成功');
    }
    //更新企业账号状态
    public function updateStatus(){
        $id = $_POST['id'];
        $status = $_POST['status'];
        $status ==0 ? $mess = '禁用' : $mess='开启';
        Db::name('white_company')->where(['id'=>$id])->setField('status',$status);
        $res =Db::name('white_company')->where(['id'=>$id])->find();
        createLog('给白名单信息公司名称为'.$res['com_name'].'更新了账号状态为：'.$mess);
        jsonSend(1,$mess.'成功');
    }
    /**
     * 添加企业白名单
     **/
    public  function addsave(){
        $data=input();
        $id =  Db::table('os_white_company')->where(['com_cod'=>$data['com_cod']])->value('id');
        if($id){$this->error('该机构信誉代码对应公司已存在');exit;}
        empty($data['username']) ? $data['username']='暂无' :  $data['username']=$data['username'];
        $data['uid'] = Session::get('admin_id');
        $data['createtime'] =  $data['updatetime']=getFormatTime();
        Db::name('white_company')->insert($data);
        createLog('添加了企业白名单，信誉代码：'.$data['com_cod'].'，名称：'.$data['username']);
        $this->success('添加成功');
    }
}