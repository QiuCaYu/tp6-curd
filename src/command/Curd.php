<?php
/**
 * Created by PhpStorm.
 * Date: 2021/7/8
 * Time: 8:23 PM
 */

namespace cayu\tp6curd\command;

use cayu\tp6curd\strategy\AutoMakeStrategy;
use cayu\tp6curd\template\impl\ControllerAutoMake;
use cayu\tp6curd\template\impl\ModelAutoMake;
use cayu\tp6curd\template\impl\ValidateAutoMake;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Curd extends Command
{

    public $dbt = '';

    protected function configure()
    {
        $this->setName('make:curd')
            ->addOption('table', 't', Option::VALUE_OPTIONAL, '表名，若多库请使用 库名.表名方式，例子：dbname.table', null)
            ->addOption('name', 'c', Option::VALUE_OPTIONAL, '控制器名称', null)
            ->addOption('path', 'p', Option::VALUE_OPTIONAL, '模块', null)
            ->setDescription('自动生成控制器，验证器以及模型；php think make:curd -t 库名.表名 -c 目录/控制器 -p 模块');
    }

    protected function execute(Input $input, Output $output)
    {
        $table = $input->getOption('table');
        if (!$table) {
            $output->error("请输入 -t 表名|请输入 -t 库名.表名，php think make:curd -t 库名.表名 -c 目录/控制器 -p 模块");
            exit;
        }
        $this->dbt = explode('.', $table);

        $controller = $input->getOption('name');
        if (!$controller) {
            $output->error("请输入 -c 控制器名，php think make:curd -t 库名.表名 -c 目录/控制器 -p 模块");
            exit;
        }

        $path = $input->getOption('path');
        if (!$path) {
            $path = '';
        }

        $context = new AutoMakeStrategy();

        // 执行生成controller策略

        $context->Context(new ControllerAutoMake());
        $context->executeStrategy($controller, $path, $this->getTable(),$this->getDb());

        // 执行生成model策略
        $context->Context(new ModelAutoMake());
        $context->executeStrategy($this->getTable(), $path, $this->getDb(),'');

        // 执行生成validate策略
        $context->Context(new ValidateAutoMake());
        $context->executeStrategy($controller, $path, $this->getTable(), $this->getDb());

        $output->info("auto make curd success");
    }

    protected function getTable()
    {
        if (isset($this->dbt[1])) {
            return $this->dbt[1];
        } else {
            return $this->dbt[0];
        }
    }

    protected function getDb()
    {
        if (isset($this->dbt[1])) {
            return $this->dbt[0];
        }
        return '';
    }
}