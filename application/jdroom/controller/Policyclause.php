<?php
namespace app\jdroom\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 法律同意条款
 * Class SlideCategory
 * @package app\admin\controller
 */
class Policyclause extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();

    }

    /**
     *
     * @return mixed
     */
    public function index()
    {
        $blacklist = Db::name('policyclause')->find();

        return $this->fetch('index', ['blacklist' => $blacklist]);
    }

    /**
     * 保存分类
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (Db::name('policyclause')->where(['id'=>1])->update($data)) {
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
    }

}