<?php

return [
    // +----------------------------------------------------------------------
    // | 后台模板设置
    // +----------------------------------------------------------------------

    'template' => [
        // 模板路径
        'view_path' => '../themes/admin/'
    ],
    'view_replace_str'      => [
        '__UPLOAD__' => '/uploads',
        '__STATIC__' => '/static',
        '__IMAGES__' => '/static/images',
        '__JS__'     => '/static/js',
        '__CSS__'    => '/static/css',
        '__LOGINJS__'     => '/static/admin/js',
        '__LOGINCSS__'    => '/static/admin/css',
        '__NEWCSS__'    => '/static/admin/new',
    ],
];