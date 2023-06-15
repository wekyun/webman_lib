<?php
//配置文件名要改成 check.php 放在webman项目的根目录的config的根目录下
return [
    //自定义错误的处理方法 $msg：错误提示   $err_code：错误码
//    'err_func' => function ($msg, $err_code) {

//    },
    'err_code' => 203,//默认错误的错误码
    //此配置为必须,配置需要使用的验证场景类，需要按照目录创建文件写法参考TP的验证器文档
    'mapping' => [
        'com' => \app\com\validate\Common::class,
    ],
];