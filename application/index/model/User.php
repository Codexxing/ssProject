<?php
namespace app\index\model;

use think\Model;
use think\Db;
/**
 * Created by PhpStorm.
 * User: liyang
 * Date: 2018/1/12
 * Time: 14:32
 */
class User extends Model
{
    public function userInsert($arr=[]){
        if(count($arr)==0)return false;
        Db::name('user')->insert($arr);
        return Db::name('user')->getLastInsID();
    }
}