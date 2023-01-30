<?php
//上传文件信息处理
return [
    // 上传地址
    'from' => 'local',

    'checkExt' => [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tif', 'tiff'],
        'audio' => ['mp3', 'wav'],
        'video' => ['avi', 'mpg', 'mpg4', 'mp4', 'mov', 'rm', 'rmpg', 'wmv', '3gp', 'flv'],
        'office' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'rtf', 'wps','csv'],
        'compress' => ['zip', 'rar', '7z'],
    ]
];
