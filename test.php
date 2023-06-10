<?php

$your_config_data = [
    'title' => 'my_plugin_config',
    'name' => '胡刚',
];
//第一个参数：$your_config_data 需要修改或者新增定义的配置数据
//第二个参数：你的插件名字，必须是你的插件名字
//第三个参数：你插件的配置文件名，不需要.php结尾

\wekConfig\wekConfig::setPluginConfigValue($your_config_data, 'demo', 'aliDianBoConfig');

//第一次开发使用，时候注意服务窗口的错误输出提示

