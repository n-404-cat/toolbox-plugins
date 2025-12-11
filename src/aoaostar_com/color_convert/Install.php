<?php

namespace plugin\aoaostar_com\color_convert;


use app\model\Plugin;

class Install implements \plugin\Install
{

    public function Install(Plugin $model)
    {
        $model->title = '颜色转换';
        $model->class = plugin_current_class_get(__NAMESPACE__);
        $model->alias = base_space_name(__NAMESPACE__);
        $model->desc = '颜色格式转换工具，支持HEX、RGB、HSL等格式互转';
        $model->version = 'v1.0';
    }

    public function UnInstall(Plugin $model)
    {

    }
}