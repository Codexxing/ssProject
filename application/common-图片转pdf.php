<?php

use think\Db;
use tecnickcom\tcpdf\tcpdf;

//生成PDF
function createpdf($html='<img src="wen.jpg">'){
    vendor('Tcpdf.tcpdf');

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
    $hostdir='public/images'; //要操作的目录名
    $filesnames = scandir($hostdir);//获取全部文件名
    sort($filesnames,SORT_NUMERIC);//文件名排序，根据数字从小到大排列
//遍历文件名
    foreach ($filesnames as $name) {
        if(strstr($name,'jpg')){//如果是图片则添加到pdf中
// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
            $pdf->AddPage();//添加一个页面
            $filename = $hostdir.'\\'.$name;//拼接文件路径

            //gd库操作  读取图片
            $source = imagecreatefromjpeg($filename);
            //gd库操作  旋转90度
            $rotate = imagerotate($source, 0, 0);
            //gd库操作  生成旋转后的文件放入别的目录中
//            imagejpeg($rotate,$hostdir.'\\123\\'.$name.'_1.jpg');
            imagejpeg($rotate,$hostdir.''.$name.'.jpg');
            //tcpdf操作  添加图片到pdf中
            $pdf->Image($hostdir.''.$name.'.jpg', 15, 26, 210, 297, 'JPG', '', 'center', true, 300);

        }
    }


    $pdf->Output('1.pdf', 'I'); //输出pdf文件

    
//    $pdf = new \Tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
//    // 设置打印模式
//    $pdf->SetCreator(PDF_CREATOR);
//    $pdf->SetAuthor('Nicola Asuni');
//    $pdf->SetTitle('TCPDF Example 001');
//    $pdf->SetSubject('TCPDF Tutorial');
//    $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
//    // 是否显示页眉
//    $pdf->setPrintHeader(true);
//    // 设置页眉显示的内容
//    $pdf->SetHeaderData('logo.jpg', 60, 'qidada.com', '企答答', array(0,64,255), array(0,64,128));
//    // 设置页眉字体
//    $pdf->setHeaderFont(Array('dejavusans', '', '12'));
//    // 页眉距离顶部的距离
//    $pdf->SetHeaderMargin('5');
//    // 是否显示页脚
//    $pdf->setPrintFooter(true);
//    // 设置页脚显示的内容
//    $pdf->setFooterData(array(0,64,0), array(0,64,128));
//    // 设置页脚的字体
//    $pdf->setFooterFont(Array('dejavusans', '', '10'));
//    // 设置页脚距离底部的距离
//    $pdf->SetFooterMargin('10');
//    // 设置默认等宽字体
//    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//    // 设置行高
//    $pdf->setCellHeightRatio(1);
//    // 设置左、上、右的间距
//    $pdf->SetMargins('10', '10', '10');
//    // 设置是否自动分页  距离底部多少距离时分页
//    $pdf->SetAutoPageBreak(TRUE, '15');
//    // 设置图像比例因子
//    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
//    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
//        require_once(dirname(__FILE__).'/lang/eng.php');
//        $pdf->setLanguageArray($l);
//    }
//    $pdf->setFontSubsetting(true);
//    $pdf->AddPage();
//    // 设置字体
//    $pdf->SetFont('stsongstdlight', '', 14, '', true);
//    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
//    $pdf->Output('example_001.pdf', 'I');
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
 * */
function searchOrderNum($uid=''){
    return Db::name('order_list')->where(['uid'=>$uid,'is_complete'=>0])->count();
}
/*
*验证手机号验证码和token是否过期
 * $type是验证类型  code是手机验证码  token是token验证
 * $phone 手机号
 * $data 传输要验证的数据
* return  1数值不正确   2时间过期了  0代表成功了
*/
function validateUser($type='',$phone='',$data=''){
    if(empty($type) ||empty($phone) ||empty($data)  ){return false;}
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
    return 3;
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