<?php
namespace cayu\tp6curd\service;
use cayu\tp6curd\command\Curd;
class ConsoleService extends \think\Service
{
    /**
     * 注册服务
     *
     * @return mixed
     */
    public function register()
    {
    
    }

    /**
     * 执行服务
     *
     * @return mixed
     */
    public function boot()
    {
        $commands = [
            Curd::class
        ];
        $this->commands($commands);
    }
}
