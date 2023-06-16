# 插件安装

基于webman的工具：安装

使用阿里云的composer镜像无法安装到插件，目前华为测试可以，所以需要先更改到华为的镜像源。

### 华为镜像如下：

```
 composer config -g repo.packagist composer https://mirrors.huaweicloud.com/repository/php/
```

### composer安装组件命令如下：请在项目根目录下执行

~~~php
composer require wekyun/webman_lib
~~~

### github代码地址：希望大家给个星星鼓励一下！

[https://github.com/wekyun/webman_lib](https://github.com/wekyun/webman_lib)



## ## 1:配置插件

定义或者修改文件配置使用演示

工具是基于xiunobbs的文件配置修改，修改前备份，修改成功删除备份，修改失败回复备份。

因为webman是常驻内存的框架，运行了修改配置的代码，需要重启服务，否则修改配置不会生效。

```php
include './vendor/autoload.php';

$your_config_data = [
    'title' => 'my_plugin_config',
    'name' => '胡刚',
];
//第一个参数：$your_config_data 需要修改或者新增定义的配置数据
//第二个参数：你的插件名字，必须是你的插件名字
//第三个参数：你插件的配置文件名，不需要.php结尾
\Wekyun\WebmanLib\WekConfig::setPluginConfigValue($your_config_data, 'demo', 'aliDianBoConfig');

```

## 2:验证插件

基于\think\Validate的验证插件

**使用此插件需要安装TP的验证器**：`composer require topthink/think-validate`

除了要跟TP一样定义规则文件外，还需要在配置目录下创建check.php配置文件，具体参考下面的使用前配置。

TP验证器文档：https://www.kancloud.cn/manual/thinkphp5_1/354102



项目开发中，除了数据库操作以外，最常写的代码便是参数接收和数据校验了。

很多程序员可能就简单的接受了参数，并不会去验证数据，或者有些程序员只是简单的if去判断一下，但是有时候有些字段必须要验证，字段多了验证数据就写了很多的代码，即便是使用\think\Validate的验证器，也需要去麻烦一下。



作者跟你们一样懒，但是又想让项目的数据验证规范起来，从最初的验证层，到现在一个插件就搞定，这期间有一个代码进化的过程。

现在把功能封装成了插件贡献大家使用，也希望各位在数据校验这方面使用起来又简单又方便。



### 参数接收

```
//需要先引入插件
use Wekyun\WebmanLib\Check;

//基础使用:$param就是接收的参数
$param = Check::checkAll('com', ['name', 'age', 'sex']);
var_dump($param);

```

**这里来解释一下两个参数，第一个参数是com,第二个参数是一个数组**

#### 第一个参数com

这个参数是配置文件check.php中定义的，对应mapping的com别名。此文件需要在使用之前配置，**如何配置看下面的使用前的配置**，com是告诉插件，要使用哪个TP验证规则文件的规则，你可以定义其他的规则文件，并在配置mapping中注册别名。

~~~
'mapping' => [
        'com' => \app\common\validate\Common::class,
    ],
~~~

#### 第二个参数数组

这第二个参数就是你要接收的参数字段了，如代码这样定义，就可以接受参数，但是如果客户端发送了没有接收的参数，也会被接收，如果想要接收自己指定的参数可以这么写：

~~~
$param = Check::checkOnlyAll('com', ['name', 'age', 'sex']);
var_dump($param);
~~~

checkOnlyAll方法的意思就是验证接收指定的字段，没有指定的字段是不会接收的。

### 参数验证与默认值

##### 字段必传（.）

必传验证很简单，`name.` 写法就是验证name传递参数是否有值，0，null，false,都算是值，只有空字符串不算传递了参数。

~~~
Check::checkAll('com', ['name.', 'age', 'sex']);
~~~

##### 默认值（:）

`age:18` 就是给了参数默认值，如果接受的参数这个字段没有值或者没传，就会给指派的默认值。

**需要注意的是，默认值和必传不要同时写**

~~~
Check::checkAll('com', ['name.', 'age:18', 'sex']);
~~~

##### 错误提示的字段别名（|）

`name|用户名` 就是给字段name自定义了错误提示时的别名，如果字段name没有传递，或者不符合验证规则，就会提示`用户名`怎么样，如果不设置别名，就会提示`name`怎么样。

使用时需要注意，|必须跟在字段name面，在必传.的前面，否则无法正常使用。

~~~
Check::checkAll('com', ['name|用户名.', 'age:18', 'sex']);
~~~

##### 自定义完整必传错误提示(>)

必传字段name没有传递，就会提示 `请输入用户名`，这在用户端提交表单的时候，验证必传字段十分有效。

需要注意的是，自定义的提示信息优先级不会大于TP的验证错误提示，也就是说，如果在验证规则文件中，设置了字段的其他错误验证规则和验证错误提示，那么验证规则的验证提示是优先级最高的。

~~~
Check::checkAll('com', ['name.>请输入用户名', 'age:18', 'sex']);
~~~



### 使用规则总结

`.`	必传

`>`	自定义必传提示，常用户前端字段友好提示

`|`	自定义字段错误提示的别名

`:`	设置默认值，此标识符和必传字段的 `.`不可以同时使用，有冲突

在字段特别多的时候，验证后写字段的必传时，这个插件非常香。



### 使用前的配置

需要在项目config目录下创建一个check.php配置文件

~~~
├── app                           应用目录
├── config                        配置目录
│   ├── check.php                 check配置文件
├── public                        静态资源目录
├── process                       自定义进程目录
├── runtime                       应用的运行时目录，需要可写权限
├── start.php                     服务启动文件
├── vendor                        composer安装的第三方类库目录
└── support                       类库适配(包括第三方类库)
~~~

在配置文件check.php中复制如下配置代码：

~~~
<?php
//配置文件名要改成 check.php 放在webman项目的根目录的config的根目录下
return [
    //自定义错误的处理方法 $msg：错误提示   $err_code：错误码
//    'err_func' => function ($msg, $err_code) {

//    },
    'err_code' => 203,//默认错误的错误码
    //此配置为必须,配置需要使用的验证场景类，需要按照目录创建文件写法参考TP的验证器文档
    'mapping' => [
        'com' => \app\common\validate\Common::class,
    ],
];
~~~



然后需要创建tp验证器的验证规则文件，在 `app\common\validate` 目录下创建 `Common.php` 文件，代码如下：

需要注意的是：tp验证规则文件，必须在上一步的 `check.php` 配置文件的 `mapping` 中配置文件地址才能使用，`\app\common\validate\Common::class`的意思就是TP配置文件的命名空间地址，可以打印看下是什么就明白了。

~~~
<?php
//验证起规则文件
namespace app\com\validate;

use think\validate;

//验证起规则文件demo,至少要定义，才能用插件
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
~~~

至此就可以使用了！





