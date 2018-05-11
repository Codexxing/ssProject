<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用命名空间
    'app_namespace'          => 'app',
    // 应用调试模式
    'app_debug'              => false,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PRC',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => 'htmlspecialchars',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,
    'dateTime'              =>'2018-01-25 16:25:25',
    //微信的支付配置
    'wxConfig'=>[
        'appid'=>'wxa4e6fb16c895c141',//appid
        'secret'=>'7866a114291d49b2790f3feeeb3563e6',
        'mch_id'=>'1500622031',//商户号
        'key'=>'Q7Knu5HSW99sELYBLTcrKneOMsiuGu4x',//商户号密钥
    ],
    //支付宝的支付配置
    'zfbConfig'=>[
        'appid'=>'wx0a4fc51e6fca7216',//appid
        'mch_id'=>'1441640702',//商户号
    ],
    'coupMoney'=>168,//律师函价格

    // auth配置
    'auth'                   => [
        // 权限开关
        'auth_on'           => 1,
        // 认证方式，1为实时认证；2为登录认证。
        'auth_type'         => 1,
        // 用户组数据不带前缀表名
        'auth_group'        => 'auth_group',
        // 用户-用户组关系不带前缀表
        'auth_group_access' => 'auth_group_access',
        // 权限规则不带前缀表
        'auth_rule'         => 'auth_rule',
        // 用户信息不带前缀表
        'auth_user'         => 'admin_user',
    ],

    'sms_appid'             =>'438943b2ad73453d849f3bf921728a2e', //短信appid
    'templete_id'             =>'266796',//短信模板id

    // 全站加密密钥
    'salt'                   => 'PpQXn7hiuCOfe9kA',
    //优惠券的钱数
    'reduce_money'=>168,
    //上传的路径
    //'upload_path'=>ROOT_PATH . 'public' . DS . 'uploads/',
	 'upload_path'=>'./public/layerfile',
    //pdf与图片的上传大小限制  1M
    'max_upload'=>1048576,
    //上传的后缀名限制
    'extensionsName'=>['jpeg','jpg','png','pdf'],
    //生成的订单号
    'order_number'=>'JDX'.$_SERVER['REQUEST_TIME'],
    //支付时的商户号
    'wx_appid'=>'',

    'zfb_appid'=>'',
    //e签宝的项目配置信息
    'eSignInfo'=>[
        'projectId' => "1111565198",
        //公共测试项目密钥
        'projectSecret' => "e8219a412ba6a9e090df4e742aaf5780",
        'esignUrlC'=>'http://openapi2.tsign.cn:8081',//企业认证打款的线上环境
        'esignUrlTest'=>'http://smlrealname.tsign.cn:8080'//企业认证打款的测试环境
    ],
    'indexUrl'=>'/static/index',//前端页面的路径
//    'adminUrl'=>'/static/newlayui',//后台新版layui路径

    // 验证码配置
    'captcha'                => [
        // 验证码字符集合
        'codeSet'  => '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY',
        // 验证码字体大小(px)
        'fontSize' => 14,
        // 是否画混淆曲线
        'useCurve' => true,
        // 验证码位数
        'length'   => 4,
        // 验证成功后是否重置
        'reset'    => true
    ],
    //快递信息配置
//    'express_appCode'=>'150a983c45d246f6913cdc2f34dcd46c',//原始的
//    'express_appKey'=>'24803712',
//    'express_appSecret'=>'483fb239bd4668183faeb20ae5f71d2b',
    'express_appCode'=>'409149924c6542ddbb9ca6589ab0f206',
    'express_appKey'=>'24868634',
    'express_appSecret'=>'656cbe9618fcd31653fb85ac06aa0244',

    //调用e签宝二级域名
    'twoHost'=>'http://api.juedouxin.com',
    'base64Header'=>'data:image/png;base64,',//base64的头部
    //友盟appkey
    'youmeng'=>[
        'y_appkey_now_ios'=>'5aefaf7e8f4a9d59c4000116', //友盟ios现在appkey
        'y_secret_now_ios'=>'aidlt2bu4thgs7uq1yfdbysuqxqydfsc', //友盟ios现在secret
        'y_appkey_old'=>'593e30e0a40fa31bc6000304', //友盟以前appkey
        'y_secret_old'=>'456' //友盟以前secret
    ],



    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'index',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,
    'http_exception_template'    =>  [
        // 定义404错误的重定向页面地址
        404 =>  APP_PATH.'404.html',
        // 还可以定义其它的HTTP status
        401 =>  APP_PATH.'401.html',
    ],

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'              => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // 手机模板开启
    'mobile_theme'          => false,

    // 视图输出字符串内容替换


    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'   => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'        => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'         => '访问的页面不存在~',
    // 显示错误信息
    'show_error_msg'        => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'      => '',
    'listNum'               =>10,//订单列表的每页展示数

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'   => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => [],
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace' => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache' => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'  => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
        'expire'     => 0,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'   => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate' => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 10,
    ],

    //e签宝的配置信息

    /*项目初始化请求地址*/
//    'open_api_url' => 'http://121.40.164.61:8080/tgmonitor/rest/app!getAPIInfo2',//模拟环境
    //'open_api_url' => "http://itsm.tsign.cn/tgmonitor/rest/app!getAPIInfo2", //正式环境

    /*接入平台项目ID,必填；*/
    'project_id' => '1111563517',

    /*项目密钥，必填*/
    'project_secret' => '95439b0863c241c63a861b87d1e647b7',

    /**
     * 签名方式 ：支持RSA、 HMACSHA256
     * 使用RSA签名方式，需打开php_openssl扩展。
     */
    'sign_algorithm' => 'HMACSHA256',
    //'sign_algorithm' => 'RSA',

    /**
     * 接入平台rsa私钥包含“-----BEGIN PRIVATE KEY-----”和“-----END PRIVATE KEY-----”。用于对请求数据进行签名。
     * 如果签名方式设置为“RSA”时，请设置该参数；
     * 如果为HMACSHA256，置空
     */
    'rsa_private_key' => '',

    /**
     * e签宝公钥,包含“-----BEGIN PUBLIC KEY-----”和“-----END PUBLIC KEY-----”。用于对响应数据进行验签。
     * 如果签名方式设置为“RSA”时，请设置该参数
     * 如果为HMACSHA256，置空
     */
    'esign_public_key' => '',

    /* http请求代理服务器设置;不使用代理的时候置空 */
    'proxy_ip' => '',
    'proxy_port' => '',

    /* 与服务端通讯方式设置。HTTP或HTTPS */
    'http_type' => 'HTTPS',
    'retry' => 3,

    /* 本地java服务 */
    'java_server' => 'http://localhost:8080'
];
