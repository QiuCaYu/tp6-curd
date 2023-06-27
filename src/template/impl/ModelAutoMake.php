<?php
/**
 * Created by PhpStorm.
 * Date: 2021/7/8
 * Time: 11:23 PM
 */

namespace cayu\tp6curd\template\impl;

use cayu\tp6curd\extend\Utils;
use cayu\tp6curd\template\IAutoMake;
use think\facade\App;
use think\facade\Db;
use think\console\Output;

class ModelAutoMake implements IAutoMake
{
    public function check($table, $path, $other = '')
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);

        $modelName = Utils::camelize($table);
        $modelFilePath = App::getAppPath().$path.DS.'model'.DS.$modelName.'.php';

        if (!is_dir(App::getAppPath().$path.DS.'model')) {
            mkdir(App::getAppPath().$path.DS.'model', 0755, true);
        }

        if (file_exists($modelFilePath)) {
            $output = new Output();
            $output->error("$modelName.php已经存在,跳过");
        }
    }

    public function make($table, $path, $dbName = '', $other)
    {
        $routingModelTpl = dirname(dirname(__DIR__)).'/tpl/RouteBaseModel.tpl';
        $routingModelContent = file_get_contents($routingModelTpl);
        $routingFile = App::getAppPath().'routing'.DS.'BaseModel.php';
        if (!file_exists($routingFile)) {
            file_put_contents($routingFile, $routingModelContent);
        }
        $model = ucfirst(Utils::camelize($table));
        $filePath = empty($path) ? '' : DS.$path;
        $namespace = empty($path) ? '\\' : '\\'.$path.'\\';
        if ($dbName) {
            $prefix = config('database.connections.'.$dbName.'.prefix');
        } else {
            $prefix = config('database.connections.mysql.prefix');
        }
        $column = Db::connect($dbName)->query('SHOW FULL COLUMNS FROM `'.$prefix.$table.'`');
        $pk = '';
        foreach ($column as $vo) {
            if ($vo['Key'] == 'PRI') {
                $pk = $vo['Field'];
                break;
            }
        }
        $controllerTpl = dirname(dirname(__DIR__)).'/tpl/Model.tpl';
        $tplContent = file_get_contents($controllerTpl);
        $tplContent = str_replace('<namespace>', $namespace, $tplContent);
        $tplContent = str_replace('<model>', $model, $tplContent);
        $tplContent = str_replace('<pk>', $pk, $tplContent);
        if (!empty($path)) {
            $projectPath = App::getAppPath().$path;
            $peojectModelTpl = dirname(dirname(__DIR__)).'/tpl/BaseModel.tpl';
            $peojectModelContent = file_get_contents($peojectModelTpl);
            $projectModelFile = $projectPath.DS.'BaseModel.php';
            if (!file_exists($projectModelFile)) {
                $peojectModelContent = str_replace('<connection>', $dbName, $peojectModelContent);
                $peojectModelContent = str_replace('<path>', $path, $peojectModelContent);
                file_put_contents($projectModelFile, $peojectModelContent);
            }
            $useMStr = 'use app\\'.$path.'\\BaseModel';
            $tplContent = str_replace('<useModel>', $useMStr, $tplContent);
        } else {
            $useMStr = 'use app\\routing\\BaseModel';
            $tplContent = str_replace('<useModel>', $useMStr, $tplContent);
        }
        $genFileName = App::getAppPath().$path.DS.'model'.DS.$model.'.php';
        file_put_contents($genFileName, $tplContent);
        echo '生成模型:'.$genFileName.'成功'.PHP_EOL;
    }
}