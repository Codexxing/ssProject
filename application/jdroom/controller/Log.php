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
class Log extends AdminBase
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
        $admin_user_list =Db::name('admin_log')->where($map)->order('createtime desc')->paginate();

        return $this->fetch('index', ['res' => $admin_user_list,'keyword'=>$keyword]);
    }

}