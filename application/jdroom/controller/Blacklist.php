<?php
namespace app\jdroom\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 轮播图分类
 * Class SlideCategory
 * @package app\admin\controller
 */
class Blacklist extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 轮播图分类
     * @return mixed
     */
    public function index()
    {
        $blacklist = Db::name('blacklist')->find();

        return $this->fetch('index', ['blacklist' => $blacklist]);
    }

    /**
     * 保存
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (Db::name('blacklist')->where(['id'=>1])->update($data)) {
                createLog('编辑了黑名单');
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
    }

}