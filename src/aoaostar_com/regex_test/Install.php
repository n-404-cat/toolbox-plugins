<?php

namespace plugin\aoaostar_com\regex_test;


use app\model\Plugin;

class Install implements \plugin\Install
{

    public function Install(Plugin $model)
    {
        $model->title = '正则表达式测试';
        $model->class = plugin_current_class_get(__NAMESPACE__);
        $model->alias = base_space_name(__NAMESPACE__);
        $model->desc = '正则表达式在线测试、验证工具';
        $model->version = 'v1.0';
    }

    public function UnInstall(Plugin $model)
    {

    }
}