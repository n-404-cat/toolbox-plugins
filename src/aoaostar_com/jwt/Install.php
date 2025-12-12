<?php

namespace plugin\aoaostar_com\jwt;


use app\model\Plugin;

class Install implements \plugin\Install
{
    # 安装时运行方法
    public function Install(Plugin $model)
    {
        # 标题
        $model->title = "JWT解析工具";
        # 类名、无需修改
        $model->class = plugin_current_class_get(__NAMESPACE__);
        # 路由、即 jwt
        $model->alias = base_space_name(__NAMESPACE__);
        # 描述
        $model->desc = 'JWT解析工具，支持解析JWT令牌，展示格式化数据，支持鼠标移上展示格式化时间。';
        # 版本号
        $model->version = 'v1.0';
    }
    # 卸载时运行方法
    public function UnInstall(Plugin $model)
    {

    }
}