<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------
//$path= str_ireplace('\\','/',dirname($_SERVER['DOCUMENT_URI']));
$path='/';
return [
    // 模板引擎类型使用Think
    'type'          => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'     => 1,
    // 模板目录名
      'view_dir_name' => 'view',
    // 'view_dir_name' => 'view/tx-widget',
    //   'view_dir_name' => cache('view_view')?cache('view_view'): 'view/tx-widget',
    // 模板后缀
    'view_suffix'   => 'html',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'     => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'       => '}',
    // 标签库标签开始标记
    'taglib_begin'  => '{',
    // 标签库标签结束标记
    'taglib_end'    => '}',
    // 视图输出字符串内容替换 //view_replace_str.__PUBLIC__
    'tpl_replace_string' => [
        '__PUBLIC__' =>   $path.'Public',                  //公共文件目录
        '__TX_HUI__' =>   $path.'Public/tx-hui',                  //公共文件目录
        '__TX_VIDGET__' =>   $path.'Public/tx-widget',                  //公共文件目录
        '__MANAGE__' =>   $path . 'Public' . DIRECTORY_SEPARATOR . 'Manage',   //后台资源目录
        '__UPLOAD_DOMAIN__' => '',  //上传所使用的域名，可跨域，但要使用同一个服务器。
        '__UPLOAD__' =>  $path . 'Public' . DIRECTORY_SEPARATOR . 'Upload',  //上传目录
    ],
];
