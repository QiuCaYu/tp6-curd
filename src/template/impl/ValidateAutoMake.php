<?php

namespace cayu\tp6curd\template\impl;

use cayu\tp6curd\extend\Utils;
use cayu\tp6curd\template\IAutoMake;
use Symfony\Component\VarExporter\VarExporter;
use think\console\Output;
use think\facade\App;
use think\facade\Db;
use think\helper\Str;

class ValidateAutoMake implements IAutoMake
{
    public $validateNames = [
        'Index',
        'Del',
        'Create',
        'Edit',
        'Read',
    ];

    public function check($controller, $path, $table)
    {
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);
        $controllerPathInfo = $controller;
        if (strpos($controller, '/') !== false) {
            $cInfo = pathinfo($controller);
            $controller = ucfirst($cInfo['filename']);
            $namespace = empty($path) ? '\\' : '\\'.$path.'\\';
            $namespace = $namespace.'validate\\'.$cInfo['dirname'];
        } else {
            $controller = ucfirst($controller);
            $namespace = empty($path) ? '\\' : '\\'.$path.'\\';
            $namespace = $namespace.'validate';
        }
        $genDir = App::getAppPath().$path.DS.'validate'.DS.$controllerPathInfo;
        if (!is_dir($genDir)) {
            mkdir($genDir, 0755, true);
        }
        foreach ($this->validateNames as $validateName) {
            $validateFilePath = App::getAppPath().$path.DS.'validate'.DS.$controllerPathInfo.DS.$validateName.'.php';
            if (file_exists($validateFilePath)) {
                $output = new Output();
                $output->error("$validateName.php已经存在,跳过生成");
            }
        }
    }

    public function make($controller, $path, $table, $dbName)
    {
        $controllerPathInfo = $controller;
        if (strpos($controller, '/') !== false) {
            $cInfo = pathinfo($controller);
            $controller = ucfirst($cInfo['filename']);
            $namespace = empty($path) ? '\\' : '\\'.$path.'\\';
            $namespace = $namespace.'validate\\'.$cInfo['dirname'];
        } else {
            $controller = ucfirst($controller);
            $namespace = empty($path) ? '\\' : '\\'.$path.'\\';
            $namespace = $namespace.'validate';
        }


        if ($dbName) {
            $prefix = config('database.connections.'.$dbName.'.prefix');
        } else {
            $prefix = config('database.connections.mysql.prefix');
        }
        $column = Db::connect($dbName)->query('SHOW FULL COLUMNS FROM `'.$prefix.$table.'`');
        $rule = [];
        $attributes = [];
        $idRule = [];
        foreach ($column as $vo) {
            $rule[$vo['Field']] = 'require';
            if (strpos($vo['Type'], 'varchar') !== false) {
                preg_match('/\d+/', $vo['Type'], $matches);
                $rule[$vo['Field']] = $rule[$vo['Field']].'|max:'.$matches[0];
            } else if (strpos($vo['Type'], 'text') !== false) {
                $rule[$vo['Field']] = $rule[$vo['Field']].'|max:10000000';
            } else if (strpos($vo['Type'], 'int') !== false) {
                preg_match('/\d+/', $vo['Type'], $matches);
                $rule[$vo['Field']] = $rule[$vo['Field']].'|integer|max:'.$matches[0];
            } else if (strpos($vo['Type'], 'decimal') !== false) {
                preg_match('/\d+,\d+/', $vo['Type'], $matches);
                $matches = explode(',', $matches[0]);
                $rule[$vo['Field']] = $rule[$vo['Field']].'|float|max:'.$matches[0];
            } else if (strpos($vo['Type'], 'date') !== false) {
                $rule[$vo['Field']] = $rule[$vo['Field']].'|date|dateFormat:Y-m-d';
            } else if (strpos($vo['Type'], 'datetime') !== false) {
                $rule[$vo['Field']] = $rule[$vo['Field']].'|date|dateFormat:Y-m-d H:i:s';
            }
            if ($vo['Field'] != 'id') {
                $rule[$vo['Field']] = str_replace('require|', '', $rule[$vo['Field']]);
            }
            $rule[$vo['Field']] = $rule[$vo['Field']];
            if ($vo['Field'] == 'id') {
                $idRule[$vo['Field']] = $rule[$vo['Field']];
            }
            $attributes[$vo['Field']] = $vo['Comment'];
        }
        $filePath = empty($path) ? '' : $path;
        foreach ($this->validateNames as $validateName) {
            $validateTpl = dirname(dirname(__DIR__)).'/tpl/'.$validateName.'Validate.tpl';
            $tplContent = file_get_contents($validateTpl);
            $tplContent = str_replace('<namespace>', $namespace.'\\'.$controller, $tplContent);
            $tplContent = str_replace('<validateName>', $validateName, $tplContent);
            if ($validateName == 'Index') {
                $ruleArr = [
                    'id'       => $idRule['id'],
                    'page'     => "integer|egt:0|max:9999",
                    'pagesize' => "integer|gt:0|max:100",
                ];
                $attributesArr = [
                    'id'   => $attributes['id'] ?? "id",
                    'page' => "页码",
                    'id'   => "页数",
                ];
                $ruleArr = VarExporter::export($ruleArr);
                $attributesArr = VarExporter::export($attributesArr);
                $tplContent = str_replace('<rule>', $ruleArr, $tplContent);
                $tplContent = str_replace('<attributes>', $attributesArr, $tplContent);
            } elseif (in_array($validateName, ['Read', 'Del'])) {
                $ruleArr = [
                    'id' => $idRule['id']
                ];
                $attributesArr = [
                    'id' => $attributes['id'] ?? "id",
                ];
                $ruleArr = VarExporter::export($ruleArr);
                $attributesArr = VarExporter::export($attributesArr);
                $tplContent = str_replace('<rule>', $ruleArr, $tplContent);
                $tplContent = str_replace('<attributes>', $attributesArr, $tplContent);
            } else {
                $ruleArr = $rule;
                $attributesArr = $attributes;
                unset($ruleArr['id']);
                unset($attributesArr['id']);
                $ruleArr = camelArray($ruleArr);
                $attributesArr = camelArray($attributesArr);
                $ruleArr = VarExporter::export($ruleArr);
                $attributesArr = VarExporter::export($attributesArr);
                $tplContent = str_replace('<rule>', $ruleArr, $tplContent);
                $tplContent = str_replace('<attributes>', $attributesArr, $tplContent);
            }
            $genFileName = App::getAppPath().$filePath.DS.'validate'.DS.$controllerPathInfo.DS.$validateName.'.php';
            if (file_exists($genFileName)) {
                // echo '验证器:'.$genFileName.'已存在'.PHP_EOL;
            } else {
                file_put_contents($genFileName, $tplContent);
                echo '生成验证器:'.$genFileName.'成功'.PHP_EOL;
            }
        }
    }

}