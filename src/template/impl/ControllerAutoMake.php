<?php
/**
 * Created by PhpStorm.
 * Date: 2021/7/8
 * Time: 10:49 PM
 */

namespace cayu\tp6curd\template\impl;

use cayu\tp6curd\extend\Utils;
use cayu\tp6curd\template\IAutoMake;
use think\facade\App;
use think\facade\Db;
use think\console\Output;

class ControllerAutoMake implements IAutoMake
{
    public function check($controller, $path, $other = '')
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);
        $controllerPathInfo = $controller;
        if (strpos($controller, '/') !== false) {
            $cInfo = pathinfo($controller);
            $controller = ucfirst($cInfo['filename']);
            $controllerPathInfo = $cInfo['dirname'];
        } else {
            $controller = ucfirst($controller);
            $controllerPathInfo = '';
        }
        $genDir = App::getAppPath().$path.DS.'controller'.DS.$controllerPathInfo;
        if (!is_dir($genDir)) {
            mkdir($genDir, 0755, true);
        }
        $controllerFilePath = App::getAppPath().$path.DS.'controller'.DS.$controllerPathInfo.DS.$controller.'.php';
        if (file_exists($controllerFilePath)) {
            $output = new Output();
            $output->error($controllerFilePath.'已经存在');
        }
    }

    public function make($controller, $path, $table, $dbName)
    {
        $routingCTpl = dirname(dirname(__DIR__)).'/tpl/RouteBaseController.tpl';
        $routingCContent = file_get_contents($routingCTpl);
        $routingFile = App::getAppPath().'routing'.DS.'BaseController.php';
        if (!file_exists($routingFile)) {
            file_put_contents($routingFile, $routingCContent);
        }
        $controllerPathInfo = $controller;
        if (strpos($controller, '/') !== false) {
            $cInfo = pathinfo($controller);
            $controller = ucfirst($cInfo['filename']);
            $namespace = empty($path) ? '\\' : '\\'.$path.'\\';
            $namespace = $namespace.'controller\\'.$cInfo['dirname'];
        } else {
            $controller = ucfirst($controller);
            $namespace = empty($path) ? '\\' : '\\'.$path.'\\';
            $namespace = $namespace.'controller';
        }
        $model = ucfirst(Utils::camelize($table));
        $filePath = empty($path) ? '' : DS.$path;


        if ($dbName) {
            $prefix = config('database.connections.'.$dbName.'.prefix');
        } else {
            $prefix = config('database.connections.mysql.prefix');
        }
        $column = Db::connect($dbName)->query('SHOW FULL COLUMNS FROM `'.$prefix.$table.'`');
        $pk = '';
        $columnFields = [];
        foreach ($column as $vo) {
            if ($vo['Key'] == 'PRI') {
                $pk = $vo['Field'];
            }
            $columnFields[] = $vo['Field'];
        }
        $columnFields = "'".implode("','", $columnFields).PHP_EOL."'";
        // file_put_contents(App::getAppPath() . $filePath . DS . 'controller' . DS . $controller . '.php', $tplContent);

        // 检测baseController是否存在
        if (!empty($path)) {
            $projectPath = App::getAppPath().$path;
            $peojectCTpl = dirname(dirname(__DIR__)).'/tpl/BaseController.tpl';
            $peojectCContent = file_get_contents($peojectCTpl);
            $projectCFile = $projectPath.DS.'BaseController.php';
            if (!file_exists($projectCFile)) {
                file_put_contents($projectCFile, $peojectCContent);
            }
            $useCStr = 'use app\\'.$path.'\\BaseController';
            $useMStr = 'use app\\'.$path.'\\model\\'.$model;
            $modelStr = $model;
        } else {
            $useCStr = 'use app\\routing\\BaseController';
            $useMStr = 'use app\\model\\'.$model;
        }
        // 生成控制器
        $controllerTpl = dirname(dirname(__DIR__)).'/tpl/Controller.tpl';
        $tplContent = file_get_contents($controllerTpl);
        $tplContent = str_replace('<namespace>', $namespace, $tplContent);
        $tplContent = str_replace('<controller>', $controller, $tplContent);
        $tplContent = str_replace('<model>', $model, $tplContent);
        $tplContent = str_replace('<pk>', $pk, $tplContent);
        $tplContent = str_replace('<useController>', $useCStr, $tplContent);
        $tplContent = str_replace('<useModel>', $useMStr, $tplContent);
        $tplContent = str_replace('<model>', $model, $tplContent);
        $tplContent = str_replace('<columnFields>', $columnFields, $tplContent);
        $genFileName = App::getAppPath().$filePath.DS.'controller'.DS.$controllerPathInfo.'.php';
        if (!file_exists($genFileName)) {
            file_put_contents($genFileName, $tplContent);
            echo '生成控制器:'.$genFileName.'成功'.PHP_EOL;
        }
    }
}