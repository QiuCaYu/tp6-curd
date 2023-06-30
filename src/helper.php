<?php
use think\Console;



/**
 * 模型内统一数据返回
 * @param $code
 * @param string $msg
 * @param array $data
 * @return array
 */
if (!function_exists('dataReturn')) {
    
    function dataReturn($code, $msg = 'success', $data = []) {

        return ['code' => $code, 'data' => $data, 'msg' => $msg];
    }
}

/**
 * 统一返回json数据
 * @param $code
 * @param string $msg
 * @param array $data
 * @return \think\response\Json
 */
if (!function_exists('jsonReturn')) {
    
    function jsonReturn($code, $msg = 'success', $data = []) {

        return json(['code' => $code, 'data' => $data, 'msg' => $msg]);
    }
}

/**
 * 统一分页返回
 * @param $list
 * @return array
 */
if (!function_exists('pageReturn')) {

    function pageReturn($list) {
        if (0 == $list['code']) {
            return ['code' => 0, 'msg' => 'ok', 'count' => $list['data']->total(), 'data' => $list['data']->all()];
        }

        return ['code' => 0, 'msg' => 'ok', 'count' => 0, 'data' => []];
    }
}

/**
 * 多级转换下划线数组转换成小驼峰
 * @param $data
 * @return array|mixed
 * @author qjy 2021/12/28
 * @update qjy 2021/12/28
 */
function camelArray($data){
    if(!$data){
        return [];
    }
    foreach ($data as $key => $value) {
        $camelKey = \think\helper\Str::camel($key);
        if (is_array($value)) {
            $data[$camelKey] = camelArray($value);
        }
        else {
            $data[$camelKey] = $value;
        }
        // 去重，删除数据组内下划线
        if ($camelKey != $key) {
            unset($data[$key]);
        }
    }
    return $data;
}