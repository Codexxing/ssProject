<?php
/**
 * Created by PhpStorm.
 * User:    @liyang
 * Date: 2018/5/11
 * Time: 9:41
 * File:数据统计类
 */

namespace app\jdroom\controller;
use app\common\controller\AdminBase;
use think\Db;
use think\Request;


class Chart extends AdminBase
{
    protected function _initialize(){
        parent::_initialize();
    }
    //用户统计首页展示
    public function user(){
        if(Request::instance()->isPost()){
            $start = Request::instance()->param('start');
            $end = Request::instance()->param('end');
        }
        return $this->fetch();
    }

    //订单统计首页展示
    public function order(){
        return $this->fetch();
    }
}