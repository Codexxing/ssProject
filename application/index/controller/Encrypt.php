<?php
namespace app\index\controller;


use app\common\controller\HomeBase;
use app\index\model\User as UserModel;
use think\Db;
use think\Session;
use think\Request;
use think\Config;
class Encrypt extends HomeBase{

    private $key = null;

    // public function _initialize($key){
    //     parent::_initialize();
    //     //加密key
    //     $this->key   = $key;
    // }
    protected function _initialize(){
        parent::_initialize();
         $this->key   = $key;
    }

    /**
     * 加密
     *
     * @param string $string 被加密字符串
     * @return string
     */
    public function jdEncrypt($string){
        $key = !empty($this->key) ? $this->key : "ceshi";
        $key = md5($key);
        $keyLen = strlen($key);
        //被加密的内容长度
        $stringLen = strlen($string);
        //如果加密key没有内容长，那么我们不断重复key，直到大于等于被加密内容
        if($keyLen < $stringLen) {
            $key = str_pad($key, $stringLen, $key);
        }
        $content = '';
        //每个字节与对应的key做异或运算
        for($i = 0; $i < $stringLen; $i++) {
            $content .= chr(ord($string[$i]) ^ ord($key[$i]));
        }
        return $content;
    }

    /**
     * 解密
     *
     * @param string $content 被解密字符串
     * @return string
     */
    function jdDecrypt($content){
        $key = !empty($this->key) ? $this->key : "ceshi";
        $key = md5($key);
        //被解密的内容长度
        $stringLen = strlen($content);
        //解密
        $string = '';
        for($i = 0; $i < $stringLen; $i++) {
            $string .= chr(ord($content[$i]) ^ ord($key[$i]));
        }
        return $string;
    }
}