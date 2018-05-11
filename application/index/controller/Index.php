<?php
namespace app\index\controller;

use app\common\controller\HomeBase;
use think\Db;
use PHPMailer\PHPMailer\PHPMailer;
use tcpdf\tcpdf;

class Index extends HomeBase
{
    public function index()
    {
////        $this->sendMaild('951402124@qq.com',1,1);
//        header("Content-type: text/html; charset=utf-8");
////        $rd =turnAmount(1030.32);
//        $transaction_id = 'SH'.date('YmdHis',time()).time().mt_rand(100000,999999);
//        var_dump($transaction_id);

        return $this->fetch();
    }

    public function about(){
        return $this->fetch();
    }
    public function helpcenter(){
        return $this->fetch();
    }


    /**这是好的 测试通过的**/
    public function sendMaild($to){
        //实例化PHPMailer核心类
        $mail = new PHPMailer();
        //是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        $mail->SMTPDebug = 1;
        //使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        //smtp需要鉴权 这个必须是true
        $mail->SMTPAuth=true;
        //链接qq域名邮箱的服务器地址
//        $mail->Host = 'smtp.163.com';
        $mail->Host = 'smtp.exmail.qq.com';
        //设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        //设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
        $mail->Port = 465;
        //设置smtp的helo消息头 这个可有可无 内容任意
        $mail->Helo = '汉卓律师事务所邮箱查收';
        //设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
        $mail->Hostname = '';
        //设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
        $mail->CharSet = 'UTF-8';
        //设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName = '我是测试的';
        //smtp登录的账号 这里填入字符串格式的qq号即可
//        $mail->Username ='ly671205@163.com';
        $mail->Username ='shuqin@hanzhuo.cn';
        //smtp登录的密码 使用生成的授权码 你的最新的授权码
//        $mail->Password = 'ly67120521';
        $mail->Password = 'cp5o6bJmUGziPGZu';
        //设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”
//        $mail->From = 'ly671205@163.com';
        $mail->From = 'shuqin@hanzhuo.cn';
        //邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
        $mail->isHTML(true);
        //设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
        $mail->addAddress($to,'测试通知');
        $mail->addBCC('951402124@qq.com');                     // 添加密送者，Mail Header不会显示密送者信息
        $mail->addCC('1647045064@qq.com');           // 添加抄送人
//        $mail->ConfirmReadingTo = '18668999188@163.com';              // 添加发送回执邮件地址，即当收件人打开邮件后，会询问是否发生回执
//        $mail->addBCC('100227760@qq.com');                   // 添加密送者，Mail Header不会显示密送者信息
        //添加多个收件人 则多次调用方法即可
        // $mail->addAddress('xxx@qq.com','lsgo在线通知');
        //添加该邮件的主题
        $mail->Subject = '使得粉红丝带哈佛是大佛寺后的符号分红是否';
        //添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
        $mail->Body = 'iwe哦i活动i后跟分红送股或送i更好';

        //为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
         $mail->addAttachment('./mytest.pdf','mm.pdf');
        //同样该方法可以多次调用 上传多个附件
        // $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');
        $mail->WordWrap=50;//换行字数
        $status = $mail->send();
//        $mail->ErrorInfo;
        //简单的判断与提示信息
        if($status) {
            return true;
        }else{
            return false;
        }
    }

}
