<?php
namespace app\jdroom\controller;

use app\common\controller\AdminBase;
use think\Db;
use think\Session;
use think\Config;

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
    }
    /**
     * 首页
     * @return mixed
     */
    public function index()
    {

//        $param['type'] = 'Broadcast';
//        $param['message'] = 'wos是测试';
//        $host = config::get('twoHost');
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $host.'/push/Demo.php');
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $resonse =curl_exec($ch);
//        curl_close($ch);
//        if(!$resonse){
//            jsonSend(0,'获取失败','');
//        }else {
//            jsonSend(1, '获取成功', $resonse);
//        }die;
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