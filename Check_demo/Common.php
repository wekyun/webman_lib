<?php

namespace app\com\validate;

use think\validate;

//验证起规则文件demo,至少要定义要给才能用插件
class Common extends Validate
{

    //书写你验证的字段，或者重新定义验证类文件，并在check配置中的 mapping 定义验证文件路径

    protected $rule = [
        'id_card' => 'idCard',//验证某个字段的值是否为有效的身份证格式
        'mobile' => 'mobile',//验证某个字段的值是否为有效的手机
        'name' => ['max' => 25, 'regex' => '/^[\w|\d]\w+/'],
        'email' => 'email',
        'age' => 'number|between:1,120',//验证某个字段的值是否在某个区间
        'info' => 'array',//验证某个字段的值是否为数组
        'accept' => 'accepted',//验证某个字段是否为为 yes, on, 或是 1。这在确认"服务条款"是否同意时很有用
        'repassword' => 'confirm:password',//验证某个字段是否和另外一个字段的值一致
        'password' => 'confirm',
        //支持正则验证
        //'zip'=>'\d{6}',

    ];

    protected $message = [
    ];

}