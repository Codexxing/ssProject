<?php
//namespace phpmailer;
use think\Db;
use think\Session;
use think\Config;
use tecnickcom\tcpdf\tcpdf;

//快递查询
function searchExpressAli($number){
    $host = "http://jisukdcx.market.alicloudapi.com";
    $path = "/express/query";
    $method = "GET";
    $appcode = config::get('express_appCode');
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    $querys = "number=".$number."&type=auto";
    $bodys = "";
    $url = $host . $path . "?" . $querys;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    if (1 == strpos("$" . $host, "https://")) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    $re = curl_exec($curl);
    $res = json_decode($re);
    curl_close($curl);
    return $res;
}
/**
生成兑换码
**/
function createNumber(){
    return substr(md5(microtime(true)), 0, 6);
}
/**
生成优惠券
@param $data为数组
**/
function giveCoupon($data){
    if(!is_array($data)){return false;}
    $data['createtime']=$data['updatetime']=getFormatTime();
    $data['overtime']='2099-12-31 '. date("H:i:s");
    $data['reduce_number']=createNumber();
    $data['is_use']=0;//是否使用 0未使用
    if(!array_key_exists('is_conversion', $data) ){$data['is_conversion']=1;}//优惠券是否兑换 0未兑换 1已兑换
     return  Db::name('reduced')->insert($data);
}


/**生成证明的PDF**/
function pdfAddImg($content){
    $pdf =new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Nicola Asuni');
    $pdf->SetTitle('TCPDF Example 009');
    $pdf->SetSubject('TCPDF Tutorial');
    $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetHeaderData('', 30, '', '');
//    $pdf->setFooterData(array(0,64,0), array(0,64,128));
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

//set some language-dependent strings
    $pdf->setLanguageArray('a');

// ---------------------------------------------------------

// add a page
    $pdf->AddPage();

// set JPEG quality
    $pdf->setJPEGQuality(75);
    // set default font subsetting mode
    $pdf->setFontSubsetting(true);

//设置字体
    $pdf->SetFont('stsongstdlight', '', 14);
    $str1 = '<h1 style="text-align:center;font-weight:bold;">证明书</h1>';
    $str1 .='<p style="text-indent: 3px;letter-spacing:0.5mm;">需求描述： '.$content['content'].'</p>';

    $pdf->WriteHtml($str1,$str1,'', 0, 'L', true, 0, false, false, 0);
    $content_file = explode(',',$content['file']);
// Image example   设置图片变为两排
    for($i=1;$i<=count($content_file);++$i){
        if($i<=5) {
            $x = 30 * $i; $tk=false;
        }else{
            $x = 30 * ($i-5);
            $tk=true;
        }
        $pdf->Image('./'.$content_file[$i-1], $x, '', 30, 30, '', 'http://www.juedouxin.com'.$i, '', false, 150,'', false, false, 0, false, false, false, false, '',$tk);
    }
    //如果存在补充需求描述
    if(array_key_exists('replenish_content',$content)){
        if($content['replenish_content']) {
            foreach($content['two_need'] as $kv=>$vv) {
                $str2 = '<p></p><p></p><p></p><p></p><p></p><p></p><p></p><p></p><p></p>';
                $str2 .= '<p style="text-indent: 3px;letter-spacing:0.5mm;">补充需求描述'.($kv+1) .'： ' .$vv['content'] . '</p>';
                $pdf->WriteHtml($str2, $str2, '', 0, 'L', true, 0, false, false, 0);
                if($vv['url']) {
                    $rep_file = explode(',', $vv['url']);
// Image example   设置图片变为两排
                    for ($i = 1; $i <= count($rep_file); ++$i) {
                        if ($i <= 5) {
                            $x = 30 * $i;
                            $tk = false;
                        } else {
                            $x = 30 * ($i - 5);
                            $tk = true;
                        }
                        $pdf->Image('./' . $rep_file[$i - 1], $x, '', 30, 30, '', 'http://www.juedouxin.com' . $i, '', false, 150, '', false, false, 0, false, false, false, false, '', $tk);
                    }
                }
            }
        }
    }
    $name = time();
//输出PDF
    $pdf->Output(__DIR__ . '/../public/createPdf/'.$name.'.pdf', 'F');
    return './createPdf/'.$name.'.pdf';
}

/**
 * 多张图片生成一个pdf文件
 * @param $arr多张图片的集合
**/
function morePicToPdf($arr){
    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);


// set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
// set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        require_once(dirname(__FILE__).'/lang/eng.php');
        $pdf->setLanguageArray($l);
    }
    $hostdir='./'; //要操作的目录名
//    $filesnames = scandir($hostdir);//获取全部文件名
//    sort($filesnames,SORT_NUMERIC);//文件名排序，根据数字从小到大排列
//遍历文件名
    foreach ($arr as $name) {
        if(strstr($name,'jpg')){//如果是图片则添加到pdf中
// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
            $pdf->AddPage();//添加一个页面
            $filename = $name;//拼接文件路径
            //gd库操作  读取图片
            $source = imagecreatefromjpeg($filename);
            //gd库操作  旋转90度
            $rotate = imagerotate($source, 0, 0);
            //gd库操作  生成旋转后的文件放入别的目录中
            imagejpeg($rotate,$name.'_1.jpg');
            //tcpdf操作  添加图片到pdf中
            $pdf->Image($name.'_1.jpg', 15, 26, 210, 297, 'JPG', '', 'center', true, 300);

        }
    }
    $name = time();
    $pdf->Output(__DIR__ . '/../public/createPdf/'.$name.'.pdf', 'F');
    return './createPdf/'.$name.'.pdf';
}


/**
 * 生成PDF  授权书
 * $type 1是签署的时候生成的授权书（返回路径没有点）  0 生成的图片的授权书（返回路径含有点）
 * 这是好的 测试通过的
 **/
function createImpowerPdf($type=0,$param){
//    $auth = Db::table('os_user')->where(['id'=>$param['uid']])->value('real_auth');
//    $accept_name = Db::table('os_address')->where(['order_number'=>$param['order_number']])->value('accept_name');
//    $userInfo = json_decode($auth, true);
    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
//    $pdf->SetProtection(array('print', 'copy'), '123456', null, 0, null);//加密
// 设置文档信息
    $pdf->SetCreator('诉舍');
    $pdf->SetAuthor('HZ');
    $pdf->SetTitle('授权委托协议');
    $pdf->SetSubject('TCPDF Tutorial');
    $pdf->SetKeywords('TCPDF, PDF, PHP');

// 设置页眉和页脚信息
//    $pdf->SetHeaderData('wen.jpg', 30, '下载APP', '授权协议书',
//        array(0,64,255), array(0,64,128));
    $pdf->SetHeaderData('', 30, '', '');
    $pdf->setFooterData(array(0,64,0), array(0,64,128));

// 设置页眉和页脚字体
    $pdf->setHeaderFont(Array('stsongstdlight', '', '10'));
    $pdf->setFooterFont(Array('helvetica', '', '8'));

// 设置默认等宽字体
    $pdf->SetDefaultMonospacedFont('courier');

// 设置间距
    $pdf->SetMargins(15, 27, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

// 设置分页
    $pdf->SetAutoPageBreak(TRUE, 25);

// set image scale factor
    $pdf->setImageScale(1.25);

// set default font subsetting mode
    $pdf->setFontSubsetting(true);

//设置字体
    $pdf->SetFont('stsongstdlight', '', 14);

    $pdf->AddPage();
//    $img_file = './we.jpg';
//    $pdf->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);

    $str1 = '<h2 style="text-align:center;font-weight:bold;">授权委托协议</h2>';
    $str1 .='<div style="box-sizing: border-box; font-size: 14px;color: black;">';
    $str1 .='<p><span style="letter-spacing:0.5mm;">委托人 ___'.$param['name'].'____ 因与 ___'.$param['accept_name'].'____ 纠纷一事，特签署</span></p>';
    $str1 .='<p><span style="font-weight: bold;">《授权委托协议》（以下简称“协议”）委托__北京市汉卓律师事务所（受托人）__为代理人。</span></p>';
    $str1 .='<p style="text-indent: 2px;letter-spacing:0.5mm;"><h4>一、 委托事项</h4> 受托人接受委托人的委托，在上述纠纷案件中参与出具律师函这一委托事项。</p>';
    $str1 .='<p style="text-indent: 2px;letter-spacing:0.5mm;"><h4>二、 委托权限</h4> 受托人的代理权限为： __ 出具律师函 __。</p>';
    $str1 .='<p style="text-indent: 2px;letter-spacing:0.5mm;"><h4>三、委托人义务 </h4>  发律师函应当出于正当维权的目的；应当积极、主动地配合受托人的工作；应如实陈述案情，客观、全面、及时、真实、详尽地向受托人提供与委托事项有关的全部文件、证据和背景材料。如有任何变化在代理期限内应及时告知受托人，否则委托人自行承担由此带来的风险和后果。如委托人捏造事实、弄虚作假，受托人有权终止代理，依约所收费用不予退还；如委托人无故终止，代理费不退回。且委托人不得提供、伪造相关信息、证据；不存在任何虚假、瑕疵；不得诋毁或者侵犯他人名誉权的指控。否则任何后果均由其自行承担。受托人对其提交的任何信息无任何审查义务，不承担任何责任及后果。委托人对受托人提出的要求应当明确、合理、合法，不得要求受托人为其提供法律或者律师执业规范所禁止、限制的服务。</p>';
    $str1 .='<p style="text-indent: 2px;letter-spacing:0.5mm;"><h4>四、受托人职责</h4>必须认真履行职责，仅根据委托人提交的相关信息按照本协议约定完成代理权限内事项，提供法律服务，无审查义务。</p>';
    $str1 .='<p style="text-indent: 2px;letter-spacing:0.5mm;"><h4>五、特别说明</h4>1、律师根据《中华人民共和国律师法》等相关法律法规规定的标准按小时收取服务费用。</p>';
    $str1 .='<p style="text-indent: 2px;letter-spacing:0.5mm;">2、委托人应支付的服务费用由“诉舍”APP代付。</p>';
    $str1 .='<p style="text-indent: 2px;letter-spacing:0.5mm;">3、“诉舍”APP仅承担代付服务费这一事项，不承担其他事项的完成及本协议项下的义务。</p>';
    $str1 .='<p style="text-indent: 2px;letter-spacing:0.5mm;"><h4>六、协议的终止</h4> 本协议自受托人将受托出具的律师函邮寄时自行终止。</p>';
    $str1 .='<p><h1>委托人：__'.$param['name'].'____</h1>';
    $str1 .='<p> 时间：'.date('Y').' 年 '.date('m') .' 月 '.date('d').' ⽇</p></div>';

//    $pdf->Write(0,$str1,'', 0, 'L', true, 0, false, false, 0);
    $pdf->WriteHtml($str1,$str1,'', 0, 'L', true, 0, false, false, 0);
    $name = mt_rand();
//输出PDF
    $pdf->Output(__DIR__ . '/../public/createPdf/'.$name.'.pdf', 'F');
    return $type == 1 ? './createPdf/'.$name.'.pdf' : './createPdf/'.$name.'.pdf';
}


/**
 * PDF转图片
 *@param $url为PDF的文件路径；$name是专为图片的图片名称
 **/
function pdf2Img($url,$name)
{
    $PDF = $url;
    $IM =new \imagick();
    $IM->readImage($PDF);
    foreach($IM as $Key => $Var){
        $Var->setImageFormat('jpg');
//        $Filename = './'.md5($Key.time()).'.png';
        $Filename = './pdf2img/'.md5($Key.uniqid()).'.jpg';
        if($Var->writeImage($Filename)==true){
            $Return[]= $Filename;
        }
    }
    //返回转化图片数组，由于pdf可能多页，此处返回二维数组。
    return $Return;
}


/**
 * base64位图片上传  ok的
 * @param $type  T为上传头像  F为资料上传
 **/
function base64Upload($imgarr,$type='F'){
//        $base64=input('img');
//    var_dump($imgarr);die;
    !is_array($imgarr) ? $imgarr = [$imgarr] : $imgarr = $imgarr;
    $imgName = [];
    foreach($imgarr as $k=>$base64) {
        $base64_image = str_replace(' ', '+', $base64);
        //post的数据里面，加号会被替换为空格，需要重新替换回来，如果不是post的数据，则注释掉这一行
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image, $result)) {
            //匹配成功
            if ($result[2] == 'jpeg') {
                $image_name = uniqid() . '.jpg';
                //纯粹是看jpeg不爽才替换的
            } else {
                $image_name = uniqid() . '.' . $result[2];
            }
            $type == 'T' ? $image_file = "./imageT/{$image_name}" :  $image_file = "./letterfile/{$image_name}";
            //服务器文件存储路径
            if (file_put_contents($image_file, base64_decode(str_replace($result[1], '', $base64_image)))) {
//                    return $image_name;
                array_push($imgName,$image_file);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    return $imgName;
}

/**
 * 获取律师函价格
**/
function getLayerPrice(){
    return Db::table('os_price')->where(['id'=>1])->value('money');
}

/**
 * 生成一个不重复的token
*/
function setToken(){
    $str = md5(uniqid(md5(microtime(true)),true));
    $str = sha1($str);
    return $str;
}
/**
 * 获取用户的手机验证码
 * $phone是手机号
 */
function userCode($phone=''){
    if(empty($phone)) return false;
    return Db::name('user_verify')->where(['mobile'=>$phone])->find();
}
/*
 * 验证黑名单
 * */
function blackListValidate($content=''){
    $blacks = Db::name('blacklist')->field('content')->find();
    $black = explode('|',$blacks['content']);
    $blackArr=[];
    for ($i=0; $i < count($black) ; $i++)
    {
        if ($black[$i] == "") {
            continue;  //如果关键字为空就跳过本次循环
        }
        if (strpos($content,trim($black[$i])) != false)
        {
            array_push($blackArr,$black[$i]);
        }
    }
    return $blackArr;
}
/*
 * 根据用户查询未完成订单的数量
 * 未付款的不能超过三个  取消的不算
 * */
function searchOrderNum($uid=''){
    return Db::name('order_list')->where(['uid'=>$uid,'paystatus'=>0,'is_complete'=>0])->count();
}

/**
 * 数字转为中文大写金额
**/
function turnAmount($num){
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角元拾佰仟万拾佰仟亿";
    $num = round($num, 2);
    $num = $num * 100;
    if (strlen($num) > 10) {
        return "数据太长，没有这么大的钱吧，检查下";
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num)-1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        $num = $num / 10;
        $num = (int)$num;
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j-3;
            $slen = $slen-3;
        }
        $j = $j + 3;
    }

    if (substr($c, strlen($c)-3, 3) == '零') {
        $c = substr($c, 0, strlen($c)-3);
    }
    if (empty($c)) {
        return "零元整";
    }else{
        return $c . "整";
    }
}



/*
*验证手机号验证码和token是否过期
 * $type是验证类型  code是手机验证码  token是token验证
 * $phone 手机号
 * $data 传输要验证的数据
* return  1数值不正确   2时间过期了  0代表成功了  3是参数没传或者没有值导致的失效
*/
function validateUser($type='',$phone='',$data=''){
    if(empty($type) ||empty($phone) ||empty($data)  ){return 3;}
    switch($type){
        case 'code':
            $res = userCode($phone);
            if(intval($data) != $res['phonecode']){return 1;exit;}
            if((time()-$res['codetime']) > 600000){ return 2;exit;}
            break;
        case 'token':
            $res = getUserVal(['mobile'=>$phone],'usertoken,tokentime');
            if($data  != $res['usertoken']){return 1;exit;}
            if((time()-$res['tokentime']) > 600000){ return 2;exit;}
            break;
    }
    return 0;
}

//获取当前时间格式化
function getFormatTime(){
    return date('Y-m-d H:i:s',time());
}
/*
 * 查看订单是否被取消  完成
 * */
function getOrderCancle($order){
    return Db::table('os_order_list')->where(['order_number' => $order])->value('is_complete');
}

/*
 * 查看订单是否被接单
 * */
function getOrderReceive($order){
    return  Db::table('os_order_list')->where(['order_number' => $order])->value('auid');
}
/*
 * 输出json格式数据*/
function jsonSend($code="200",$message="",$info=null)
{
    $data['code']   =   $code;
    $data['message'] =   $message;
    $data['data']    =   $info;
    exit(json_encode($data,JSON_UNESCAPED_UNICODE));
}
/**
 * 获取某个用户的单个信息值
 * @return array|bool
 */
function getOneUserVal($param=[],$field=''){
//    var_dump($param);
    if(!is_array($param) || count($param)==0 || empty($field)){return false;}
    return Db::table('os_user')->where($param)->value($field);
}
/**
 * 获取某个用户的某些字段以及整个信息值
 * @return array|bool
 */
function getUserVal($param=[],$field='*'){
    if(!is_array($param) || count($param)==0){return false;}
    return Db::name('user')->where($param)->field($field)->find();
}
/**
 * 获取某个用户的订单号信息
 * @param  是订单号或者uid查询
 * @return array|bool
 */
function getUserOrder($param=[],$field='*'){
    if(!is_array($param) || count($param)==0){return false;}
    return Db::name('order_list')->where($param)->field($field)->find();
}
/**
 * 查询某个表的某个信息的值
 * @param 表名称
 * @return array|bool
 */
function searchSingalVal($table='',$field='id',$where=[]){
    return Db::table($table)->where($where)->value($field);
}

/**
 * 获取分类所有子分类
 * @param int $cid 分类ID
 * @return array|bool
 */
function get_category_children($cid)
{
    if (empty($cid)) {
        return false;
    }

    $children = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->select();

    return array2tree($children);
}
/*
 * 字符串截断
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true){
    $str = strip_tags($str);
    $str  =  preg_replace('/&emsp;/si','',$str);
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    $fix = '';
    if (strlen($slice) < strlen($str)) {
        $fix = '...';
    }
    return $suffix ? $slice . $fix : $slice;
}
/**
 * 根据分类ID获取文章列表（包括子分类）
 * @param int   $cid   分类ID
 * @param int   $limit 显示条数
 * @param array $where 查询条件
 * @param array $order 排序
 * @param array $filed 查询字段
 * @return bool|false|PDOStatement|string|\think\Collection
 */
function get_articles_by_cid($cid, $limit = 10, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->limit($limit)->select();

    return $article_list;
}

/**
 * 根据分类ID获取文章列表，带分页（包括子分类）
 * @param int   $cid       分类ID
 * @param int   $page_size 每页显示条数
 * @param array $where     查询条件
 * @param array $order     排序
 * @param array $filed     查询字段
 * @return bool|\think\paginator\Collection
 */
function get_articles_by_cid_paged($cid, $page_size = 15, $where = [], $order = [], $filed = [])
{
    if (empty($cid)) {
        return false;
    }

    $ids = Db::name('category')->where(['path' => ['like', "%,{$cid},%"]])->column('id');
    $ids = (!empty($ids) && is_array($ids)) ? implode(',', $ids) . ',' . $cid : $cid;

    $fileds = array_merge(['id', 'cid', 'title', 'introduction', 'thumb', 'reading', 'publish_time'], (array)$filed);
    $map    = array_merge(['cid' => ['IN', $ids], 'status' => 1, 'publish_time' => ['<= time', date('Y-m-d H:i:s')]], (array)$where);
    $sort   = array_merge(['is_top' => 'DESC', 'sort' => 'DESC', 'publish_time' => 'DESC'], (array)$order);

    $article_list = Db::name('article')->where($map)->field($fileds)->order($sort)->paginate($page_size);

    return $article_list;
}

/**
 * 数组层级缩进转换
 * @param array $array 源数组
 * @param int   $pid
 * @param int   $level
 * @return array
 */
function array2level($array, $pid = 0, $level = 1)
{
    static $list = [];
    foreach ($array as $v) {
        if ($v['pid'] == $pid) {
            $v['level'] = $level;
            $list[]     = $v;
            array2level($array, $v['id'], $level + 1);
        }
    }

    return $list;
}

/**
 * 构建层级（树状）数组
 * @param array  $array          要进行处理的一维数组，经过该函数处理后，该数组自动转为树状数组
 * @param string $pid_name       父级ID的字段名
 * @param string $child_key_name 子元素键名
 * @return array|bool
 */
function array2tree(&$array, $pid_name = 'pid', $child_key_name = 'children')
{
    $counter = array_children_count($array, $pid_name);
    if (!isset($counter[0]) || $counter[0] == 0) {
        return $array;
    }
    $tree = [];
    while (isset($counter[0]) && $counter[0] > 0) {
        $temp = array_shift($array);
        if (isset($counter[$temp['id']]) && $counter[$temp['id']] > 0) {
            array_push($array, $temp);
        } else {
            if ($temp[$pid_name] == 0) {
                $tree[] = $temp;
            } else {
                $array = array_child_append($array, $temp[$pid_name], $temp, $child_key_name);
            }
        }
        $counter = array_children_count($array, $pid_name);
    }

    return $tree;
}

/**
 * 子元素计数器
 * @param array $array
 * @param int   $pid
 * @return array
 */
function array_children_count($array, $pid)
{
    $counter = [];
    foreach ($array as $item) {
        $count = isset($counter[$item[$pid]]) ? $counter[$item[$pid]] : 0;
        $count++;
        $counter[$item[$pid]] = $count;
    }

    return $counter;
}

/**
 * 把元素插入到对应的父元素$child_key_name字段
 * @param        $parent
 * @param        $pid
 * @param        $child
 * @param string $child_key_name 子元素键名
 * @return mixed
 */
function array_child_append($parent, $pid, $child, $child_key_name)
{
    foreach ($parent as &$item) {
        if ($item['id'] == $pid) {
            if (!isset($item[$child_key_name]))
                $item[$child_key_name] = [];
            $item[$child_key_name][] = $child;
        }
    }

    return $parent;
}

/**
 * 循环删除目录和文件
 * @param string $dir_name
 * @return bool
 */
function delete_dir_file($dir_name)
{
    $result = false;
    if (is_dir($dir_name)) {
        if ($handle = opendir($dir_name)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    if (is_dir($dir_name . DS . $item)) {
                        delete_dir_file($dir_name . DS . $item);
                    } else {
                        unlink($dir_name . DS . $item);
                    }
                }
            }
            closedir($handle);
            if (rmdir($dir_name)) {
                $result = true;
            }
        }
    }

    return $result;
}

/**
 * 判断是否为手机访问
 * @return  boolean
 */
function is_mobile()
{
    static $is_mobile;

    if (isset($is_mobile)) {
        return $is_mobile;
    }

    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false
    ) {
        $is_mobile = true;
    } else {
        $is_mobile = false;
    }

    return $is_mobile;
}

/**
 * 手机号格式检查
 * @param string $mobile
 * @return bool
 */
function check_mobile_number($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    $reg = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';

    return preg_match($reg, $mobile) ? true : false;
}

/**
 * 日志管理
 **/
function createLog($mess=''){
    $uid = Session::get('admin_id');
    $name =Db::table('os_admin_user')->where(['id'=>$uid])->value('username');
    $arr=[
        'uid'=>$uid,
        'admin_name'=>$name,
        'oper_log'=>$mess,
        'createtime'=>getFormatTime(),
        'updatetime'=>getFormatTime()
    ];
    Db::table('os_admin_log')->insert($arr);
    return true;
}