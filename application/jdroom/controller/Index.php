<?php
namespace app\jdroom\controller;

use app\common\controller\AdminBase;
use think\Db;
use think\Session;

/**
 * 后台首页
 * Class Index
 * @package app\admin\controller
 */

class Index extends AdminBase
{
    protected function _initialize()
    {
        parent::_initialize();
//        vendor('phonepush.demo');
//        $demo = new \Demo('123','123');
//        var_dump($demo);die;
    }

    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
//        $redis = new \Redis();
             //连接
//     $redis->connect('127.0.0.1', 6379);
     //检测是否连接成功
//     echo "redis Server is running: " . $redis->ping();
    
        $version = Db::query('SELECT VERSION() AS ver');
        $config  = [
            'url'             => $_SERVER['HTTP_HOST'],
            'document_root'   => $_SERVER['DOCUMENT_ROOT'],
            'server_os'       => PHP_OS,
            'server_port'     => $_SERVER['SERVER_PORT'],
            'server_soft'     => $_SERVER['SERVER_SOFTWARE'],
            'php_version'     => PHP_VERSION,
            'mysql_version'   => $version[0]['ver'],
            'max_upload_size' => ini_get('upload_max_filesize')
        ];
       $user =  Db::name('admin_user')->where(['id'=>Session::get('admin_id')])->find();
       $user['count'] =  Db::name('admin_user')->count();

//var_dump($tt);die;
        return $this->fetch('index', ['config' => $config,'user'=>$user]);
    }
    //随机数
    function getrandstr(){
        $str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $randStr = str_shuffle($str);//打乱字符串
        $rands= substr($randStr,0,6);//substr(string,start,length);返回字符串的一部分
        return $rands;
    }

}