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
    }

    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        $param['order_number']='SH2018042110255101';
        $conten =  Db::table('os_order_list')
            ->alias('list')
            ->join('os_letters_file file','list.order_number = file.order_number','LEFT')
            ->field('list.content,list.replenish_content,file.*')
            ->where(['list.order_number'=>$param['order_number'],'file.types'=>0])
            ->find();
        $conten['two_need']=Db::name('replenish')->where(['order_number'=>$param['order_number']])->select();
        $contPdf = pdfAddImg($conten);
        var_dump($contPdf);die;
    
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