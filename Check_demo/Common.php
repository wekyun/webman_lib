<?php

namespace app\com\validate;

use think\validate;

//验证起规则文件demo,至少要定义要给才能用插件
class Common extends Validate
{
    protected $rule = [
        'name' => 'min:5',
        'email' => 'email',
    ];

    protected $message = [
        'name.min' => '名称最低个5字符',
    ];

}