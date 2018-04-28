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
 * 反馈列表
 * Class AdminUser
 * @package app\admin\controller
 */
class Feedback extends AdminBase
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 列表
     * @return mixed
     */
    public function index()
    {
        $list =Db::table('os_feedback')
            ->alias('f')
            ->join('os_user u','f.uid = u.id','LEFT')
            ->field('f.*,u.mobile')
            ->order('f.createtime desc')
            ->paginate();
        echo Db::table('os_feedback')->getLastSql();
        return $this->fetch('index', ['res' => $list]);
    }

}