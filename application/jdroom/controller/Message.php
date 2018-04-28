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
 * 消息管理
 * Class AdminUser
 * @package app\admin\controller
 */
class Message extends AdminBase
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
            $map['admin_name'] = ['like', "%{$keyword}%"];
        }
        $admin_user_list =Db::name('user_message')->where($map)->order('createtime desc')->paginate();

        return $this->fetch('index', ['res' => $admin_user_list,'keyword'=>$keyword]);
    }

    /**
     * 消息发送展示页面
     **/
    public function add(){
        return $this->fetch();
    }

    /**
     * 消息发送插入
    **/
    public function sendMessage(){
        $data = Request::instance()->param();
        if(empty($data['attr_phone'])){$this->error('请选择用户');exit;}
        if(empty($data['content'])){$this->error('发送内容不能为空');exit;}
        $admin_id = Session::get('admin_id');
       $admin_name= Db::table('os_admin_user')->where(['id'=>$admin_id])->value('username');
        $arr=[
            'admin_name'=>$admin_name,
            'uid'=>$data['userid'],
            'uname'=>$data['attr_phone'],
            'content'=>$data['content'],
            'createtime'=>getFormatTime(),
        ];
        Db::name('user_message')->insert($arr);
        $phone = getOneUserVal(['id'=>$data['userid']],'mobile');
        createLog('给用户'.$phone.'发送了消息，消息内容：'.$data['content']);
        $this->success('发送成功');
    }
    /**
     * 搜索手机号
     **/
    public function searchUser(){
        $phone = Request::instance()->param('phone');
       $id = Db::table('os_user')->where(['mobile'=>$phone])->value('id');
        if($id)
            $this->success('获取成功','',$id);
        else
            $this->error('该手机号未注册');
    }

      /**
     * 查看消息内容
     **/
    public function contentSearch(){
        $id = Request::instance()->param('id');
       $content = Db::table('os_user_message')->where(['id'=>$id])->value('content');
      $this->success('获取成功','',$content);

    }



}