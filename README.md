# webman-tool
基于webman的工具



### 定义或者修改文件配置使用演示：

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

//\Wekyun\WebmanLib\WekConfig::setPluginConfigValue($your_config_data, 'demo', 'aliDianBoConfig');

//第一次开发使用，时候注意服务窗口的错误输出提示
\Wekyun\WebmanLib\WekConfig::cs();

```

