<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/17
 * Time: 18:14
 */

namespace app\index\controller;


use app\common\controller\HomeBase;
use app\index\controller\Login;
use app\index\controller\Writeletter;

class Curlop extends HomeBase{
    protected function _initialize(){
        parent::_initialize();
    }
    public function index(){
        $login = new Writeletter();
        $login->writeAddress(
            [
                'mobile'=>15810816521,
                'order_number'=>'JDX1516256628',
                'uid'=>5,
                'send_name'=>'生生世世',
                'send_address'=>'sdfsdssssssssssssssssssssfsdf',
                'send_email'=>'dsfsfsdffd',
                'accept_name'=>'sdxvxvxcv',
                'accept_phone'=>'1584122222',
                'accept_address'=>'wddddd',
                'accept_email'=>'sdddddddddd',
                'accept_type'=>'3',
                'real_auth'=>'3'
            ],'031082faa4c635da64219310e6bec7fe094fa266'
        );
    }

}
