<?php
namespace app\api\controller;

use think\Controller;
use think\Session;
use think\Config;
use think\Db;

/**
 * 通用上传接口
 * Class Upload
 * @package app\api\controller
 */
class Upload extends Controller
{
    protected function _initialize()
    {
        parent::_initialize();
        if (!Session::has('admin_id')) {
            $result = [
                'error'   => 1,
                'message' => '未登录'
            ];

            return json($result);
        }
    }

    /**
     * 通用图片上传接口
     * @return \think\response\Json
     */
    public function upload()
    {
        $config = [
            'size' => 2097152,
            'ext'  => 'jpg,gif,png,bmp'
        ];

        $file = $this->request->file('file');

        $upload_path = str_replace('\\', '/', ROOT_PATH . 'public/uploads');
        $save_path   = '/uploads/';
        $info        = $file->validate($config)->move($upload_path);

        if ($info) {
            $result = [
                'error' => 0,
                'url'   => str_replace('\\', '/', $save_path . $info->getSaveName())
            ];
        } else {
            $result = [
                'error'   => 1,
                'message' => $file->getError()
            ];
        }

        return json($result);
    }

    /**
     * 文件上传
     * @type l为律师函  f为发票
     **/
    public function filesUpload(){

        $order = input('order');
        $type = input('type');
       $signServiceId = Db::table('os_order_list')->where(['order_number'=>$order])->value('signServiceId');
        if(!$signServiceId){  return json_encode(['error'=>0,'message'=>'请等待用户签署之后在进行上传']);exit;}
        $file = request()->file('file');
//        $size = $file->getSize();//上传的大小
//        var_dump($size);die;
//        if($size > config::get('max_upload')){
//            return json_encode(['error'=>0,'message'=>'请上传1M以内的文件']);exit;
//        }
        $name =  pathinfo($file->getInfo('name'));//上传文件的类型
        if($name['extension'] != 'pdf'){
            return json_encode(['error'=>0,'message'=>'请上传pdf格式文件']);exit;
        }
        if($_FILES['file']['error']){// 如果$_FILES['file']['error']>0,表示文件上传失败
            return json_encode(['error'=>0,'message'=>'文件上传失败']);exit;
        }
        //上传的时候的原文件名
        $data['name'] = $file -> getInfo()['name'];
        if($type=='l')//盖好章的律师函
            $dir = ROOT_PATH . 'public' . DS . 'files/layerfile_stamp/';// 律师函
        else if($type=='f')//电子发票
            $dir = ROOT_PATH . 'public' . DS . 'files/elect_bill/';// 电子发票

        if (!is_dir($dir)) {    mkdir($dir,0777,true);   }
        $info = $file->move($dir);// 将文件上传指定目录
        //获取文件的全路径
        $data['attrurl'] = str_replace('\\', '/', $info->getSaveName());//GetPathName返回文件路径(盘符+路径+文件名)
        $arr=[
            'admin_id'=>Session::get('admin_id'),
            'order_number'=>$order,
            'updatetime'=>date('Y-m-d H:i:s',time()),
        ];
        if($type=='l') {//盖好章的律师函
            $arr['file_stamp'] = $data['url'] = './files/layerfile_stamp/' . $data['attrurl'];// 律师函
            $arr['file_stamp_name'] = $data['name'];
        }else if($type=='f') {//电子发票
            $arr['elect_bill'] = $data['url'] = './files/elect_bill/' . $data['attrurl'];// 电子发票
            $arr['elect_bill_name'] = $data['name'];
        }
            $id = Db::table('os_file_stamp')->where(['order_number'=>$order])->value('id');
        if($id){
            Db::name('file_stamp')->where(['order_number'=>$order])->update($arr);
        }else{
            $arr['createtime']=date('Y-m-d H:i:s',time());
            Db::name('file_stamp')->insert($arr);
        }
        return json_encode(['error'=>1,'message'=>'文件上传成功','data'=>$data]);
    }
}