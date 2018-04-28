<?php
/**
 * Created by PhpStorm.
 * User: liyang
 * Date: 2018/2/27
 * Time: 14:02
 * File ：e签宝第三方接口
 */

namespace app\index\controller;

use app\common\controller\HomeBase;
use think\config;
use think\Request;
use think\File;
use think\Db;
use think\Session;



use org\api\eSignOpenAPI;
//use think\eapi;
use org\api\core\eSign;
use org\tech\constants\PersonArea;
use org\tech\constants\PersonTemplateType;
use org\tech\constants\OrganizeTemplateType;
use org\tech\constants\SealColor;
use org\tech\constants\UserType;
use org\tech\constants\OrganRegType;
use org\tech\constants\SignType;
use org\tech\constants\LicenseType;
use org\tech\core\Util;

class EsignApi extends HomeBase{

    protected function _initialize(){
        parent::_initialize();
    }
    /**
     * 初始化和登录
     */
    function init(){

        $sign = null;
        //实例化
        global $sign;//声明全局引用
        try {
            $sign = new eSign();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
        $iRet = $sign->init();
        if (0 === $iRet) {
            $array = array(
                "errCode" => 0,
                "msg" => "初始化成功",
                "errShow" => true
            );
            echo Util::jsonEncode($array);
        }
    }
}