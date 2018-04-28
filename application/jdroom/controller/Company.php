<?php
namespace app\jdroom\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 * 快递公司集合
 * Class Nav
 * @package app\admin\controller
 */
class Company extends AdminBase
{

    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     *
     * @return mixed
     */
    public function index($keyword='')
    {
        $where='';
        if($keyword){
            $where['company_letter|company_name']=['like', "%{$keyword}%"];
        }
       $res = Db::name('logistics_company')->where($where)->paginate(10);
        return $this->fetch('index',['res'=>$res,'keyword'=>$keyword]);
    }

    /**
     * 添加
     * @param string $pid
     * @return mixed
     */
    public function add($pid = '')
    {
        return $this->fetch('add', ['pid' => $pid]);
    }

    /**
     * 保存导航
     */
    public function save()
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $data['createtime']= $data['updatetime']=date('Y-m-d H:i:s',time());
                if (Db::name('logistics_company')->insert($data)) {
                    createLog('新增了快递名称'.$data['company_name']);
                    $this->success('保存成功');
                } else {
                    $this->error('保存失败');
                }

        }
    }

    /**
     * 编辑导航
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $nav = Db::name('logistics_company')->find($id);

        return $this->fetch('edit', ['nav' => $nav]);
    }

    /**
     * 更新导航
     * @param $id
     */
    public function update($id)
    {
        if ($this->request->isPost()) {
            $data            = $this->request->post();
            $validate_result = $this->validate($data, 'Nav');

            if ($validate_result !== true) {
                $this->error($validate_result);
            } else {
                if ($this->nav_model->save($data, $id) !== false) {
                    $this->success('更新成功');
                } else {
                    $this->error('更新失败');
                }
            }
        }
    }

    /**
     * 删除导航
     * @param $id
     */
    public function delete($id)
    {
        if ($this->nav_model->destroy($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}